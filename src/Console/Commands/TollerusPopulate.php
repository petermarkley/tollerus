<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Models\DisplayTable;
use PeterMarkley\Tollerus\Models\DisplayTableRow;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableRowFilter;

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
    public function handle()
    {
        /**
         * The language factory itself calls the neography factory,
         * which does a lot of fancy work to make a glyph set.
         */

        $language = Language::factory()
            ->withNeography() // <-- calls the neography factory right here
            ->create();

        /**
         * Define the language's grammar
         */

        // Adjectives
        // ----------
        WordClassGroup::factory()
            ->for($language)
            ->has(WordClass::factory()
                ->for($language)
                ->state(['name'=>'adjective'])
            )->create();

        // Adverbs
        // -------
        WordClassGroup::factory()
            ->for($language)
            ->has(WordClass::factory()
                ->for($language)
                ->state(['name'=>'adverb'])
            )->create();

        // Verbs
        // -----
        $group = WordClassGroup::factory()
            ->for($language)
            ->has(WordClass::factory()
                ->for($language)
                ->state(['name'=>'auxiliary verb'])
            )->has(WordClass::factory()
                ->for($language)
                ->state(['name'=>'verb'])
            )->create(['inflected'=>true]);
        // Add verb inflection features
        $verbRole = Feature::factory()->for($group,'group')>create(['name' => 'role']);
        $verbInfinitive = FeatureValue::factory()->for($verbRole)->create(['name'=>'infinitive']);
        $verbFinite     = FeatureValue::factory()->for($verbRole)->create(['name'=>'finite']);
        $verbParticiple = FeatureValue::factory()->for($verbRole)->create(['name'=>'participle']);
        $verbTense = Feature::factory()->for($group,'group')>create(['name' => 'tense']);
        $verbPast    = FeatureValue::factory()->for($verbTense)->create(['name'=>'past']);
        $verbPresent = FeatureValue::factory()->for($verbTense)->create(['name'=>'present', 'name_brief'=>'pres.']);
        $verbFuture  = FeatureValue::factory()->for($verbTense)->create(['name'=>'future', 'name_brief'=>'fut.']);
        $verbAspect = Feature::factory()->for($group,'group')>create(['name' => 'aspect']);
        $verbPerfect     = FeatureValue::factory()->for($verbAspect)->create(['name'=>'perfect', 'name_brief'=>'perf.']);
        $verbProgressive = FeatureValue::factory()->for($verbAspect)->create(['name'=>'progressive', 'name_brief'=>'prog.']);
        $verbProspective = FeatureValue::factory()->for($verbAspect)->create(['name'=>'prospective', 'name_brief'=>'pros.']);
        // Add verb inflection tables
        $dispTable = DisplayTable::factory()
            ->for($group)
            ->create([
                'label' => 'finite verb',
                'position' => 0,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
        $pivot = new DisplayTableFilter([
            'disp_table_id' => $dispTable->id,
            'feature_id' => $verbRole->id,
            'value_id' => $verbFinite->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'past tense',
                'label_brief' => 'past',
                'position' => 0,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbTense->id,
            'value_id' => $verbPast->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'present tense',
                'label_brief' => 'pres.',
                'position' => 1,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbTense->id,
            'value_id' => $verbPresent->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'future tense',
                'label_brief' => 'fut.',
                'position' => 2,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbTense->id,
            'value_id' => $verbFuture->id,
        ]);
        $pivot->save();
        $dispTable = DisplayTable::factory()
            ->for($group)
            ->create([
                'label' => 'participle',
                'position' => 1,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
        $pivot = new DisplayTableFilter([
            'disp_table_id' => $dispTable->id,
            'feature_id' => $verbRole->id,
            'value_id' => $verbParticiple->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'perfect aspect',
                'label_brief' => 'perf.',
                'position' => 0,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbAspect->id,
            'value_id' => $verbPerfect->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'progressive aspect',
                'label_brief' => 'prog.',
                'position' => 1,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbAspect->id,
            'value_id' => $verbProgressive->id,
        ]);
        $pivot->save();
        $dispTableRow = DisplayTableRow::factory()
            ->for($dispTable)
            ->create([
                'label' => 'prospective aspect',
                'label_brief' => 'pros.',
                'position' => 2,
            ]);
        $pivot = new DisplayTableRowFilter([
            'disp_table_row_id' => $dispTableRow->id,
            'feature_id' => $verbAspect->id,
            'value_id' => $verbProspective->id,
        ]);
        $pivot->save();
    }
}
