<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Enums\FontFormat;
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Domain\Language\Actions\LoadGrammarPreset;
use PeterMarkley\Tollerus\Domain\Neography\Services\FontAssetService;
use PeterMarkley\Tollerus\Domain\Neography\Actions\SvgToKeyboard;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\WordClass;

class TollerusPopulate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tollerus:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random conlang data for dev/testing.';

    /**
     * Execute the console command.
     */
    public function handle(
        LoadGrammarPreset $loadGrammarPreset,
        FontAssetService $fontAssetService,
        SvgToKeyboard $svgToKeyboard,
    ) {
        /**
         * Step 1: Generate language, neography, and grammar
         */
        $language = Language::factory()->withNeography()->create();
        $loadGrammarPreset($language, 'english');

        /**
         * Step 2: Run actions
         */
        $fontAssetService->publish(FontFormat::from('svg'), $language->primaryNeography);
        $svgToKeyboard($language->primaryNeography);

        /**
         * Step 3: Define particles for auto-inflection
         */

        // Initialize some values with minimal queries
        $language->loadMissing([
            'wordClassGroups.inflectionTables.filterValues.feature',
            'wordClassGroups.inflectionTables.rows.filterValues.feature',
            'wordClassGroups.primaryClass',
        ]);

        $combiningClass = WordClass::query()->firstWhere('name', 'combining form');
        $combiningGroup = $language->wordClassGroups
            ->first(fn ($t) => $t->id === $combiningClass->group_id);

        // Find built rows
        $inflectionTables = $language->wordClassGroups
            ->flatMap->inflectionTables;
        $builtRows = $inflectionTables
            ->flatMap->rows
            ->filter(fn ($t) => $t->src_base !== null);

        // Make particles for all inflected rows
        foreach ($builtRows as $row) {
            // First get some context
            $table = $inflectionTables
                ->first(fn ($t) => $t->id === $row->inflect_table_id);
            $class = $language->wordClassGroups
                ->first(fn ($t) => $t->id === $table->word_class_group_id)
                ->primaryClass;

            // Now let's write out a definition for the particle
            $filterValues = collect([
                $table->filterValues,
                $row->filterValues
            ])->filter()->collapse();
            $grammarFeatures = $filterValues
                ->map(fn ($t) => $t->name.' '.$t->feature->name)
                ->implode(', ');
            $classNamePlural = Str::plural($class->name);
            $definition = "<p>Forming the {$grammarFeatures} of {$classNamePlural}.</p>";

            // Now let's create an entry for it
            $entry = Entry::factory()
                ->for($language)
                ->create();
            $lexeme = Lexeme::create([
                'language_id' => $language->id,
                'entry_id' => $entry->id,
                'word_class_id' => $combiningClass->id,
                'position' => 0,
            ]);
            $lexeme->senses()->create([
                'num' => 1,
                'body' => $definition,
            ]);
            $particle = Form::factory()
                ->for($lexeme)
                ->for($language)
                ->withSpelling($language, mt_rand(1,3))
                ->create();
            $entry->primary_form = $particle->id;
            $entry->save();

            // And set the form as this row's particle
            $row->src_particle = $particle->id;
            $row->save();

            // Also drop a few morph rules
            $pattern = "(?<=.).\$";
            $row->morphRules()->create([
                'pattern' => $pattern,
                'target_type' => MorphRuleTargetType::BaseInput,
                'pattern_type' => MorphRulePatternType::Transliterated,
                'order' => 1,
            ]);
            $row->morphRules()->create([
                'pattern' => $pattern,
                'target_type' => MorphRuleTargetType::BaseInput,
                'pattern_type' => MorphRulePatternType::Phonemic,
                'order' => 1,
            ]);
            $row->morphRules()->create([
                'pattern' => $pattern,
                'neography_id' => $language->primary_neography,
                'target_type' => MorphRuleTargetType::BaseInput,
                'pattern_type' => MorphRulePatternType::Native,
                'order' => 1,
            ]);
        }

        /**
         * Step 3: Make a large batch of words to fill the dictionary
         */
        Entry::factory()
            ->for($language)
            ->withLexemes($language)
            ->count(150)
            ->create();
    }
}
