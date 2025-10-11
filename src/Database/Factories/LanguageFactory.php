<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * This will allow easily passing the Language name to the Neography
     */
    public function withNeography(): static
    {
        return $this->afterCreating(function (Language $langModel) {
            // Create the Neography, with custom name
            $neoModel = Neography::factory()
                ->withGlyphSet(
                    machineName: $langModel->machine_name,
                    name: $langModel->name,
                    num: mt_rand(15, 30),
                    mix: ! (bool) mt_rand(0,2), // true 1/3rd of the time
                )->create();
            // Add connection between Neography and Language
            $pivot = new LanguageNeography([
                'language_id' => $langModel->id,
                'neography_id' => $neoModel->id,
            ]);
            $pivot->save();
            // Mark Neography as the primary one
            $langModel->primary_neography = $neoModel->id;
            $langModel->save();
        });
    }

    /**
     * This will add WordClassGroups, Features/FeatureValues, and DisplayTables
     * matching real-life English grammar. Used for testing/demo purposes.
     */
    public function withEnglishGrammar(): static
    {
        return $this->afterCreating(function (Language $language) {
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
            $verbAspect = Feature::factory()->for($group,'group')>create(['name' => 'aspect']);
            $verbPerfect     = FeatureValue::factory()->for($verbAspect)->create(['name'=>'perfect', 'name_brief'=>'perf.']);
            $verbSimple      = FeatureValue::factory()->for($verbAspect)->create(['name'=>'simple']);
            $verbProgressive = FeatureValue::factory()->for($verbAspect)->create(['name'=>'progressive', 'name_brief'=>'prog.']);
            $verbNumber = Feature::factory()->for($group,'group')>create(['name' => 'number']);
            $verbSingular = FeatureValue::factory()->for($verbAspect)->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $verbPlural   = FeatureValue::factory()->for($verbAspect)->create(['name'=>'plural', 'name_brief'=>'pl.']);
            $verbPerson = Feature::factory()->for($group,'group')>create(['name' => 'person']);
            $verbFirst  = FeatureValue::factory()->for($verbAspect)->create(['name'=>'first', 'name_brief'=>"1\u{02E2}\u{1D57}"]);
            $verbSecond = FeatureValue::factory()->for($verbAspect)->create(['name'=>'second', 'name_brief'=>"2\u{207F}\u{1D48}"]);
            $verbThird  = FeatureValue::factory()->for($verbAspect)->create(['name'=>'third', 'name_brief'=>"3\u{02B3}\u{1D48}"]);
            // Add verb inflection tables
            $dispTable = DisplayTable::factory()
                ->for($group)
                ->create([
                    'label' => 'finite verb',
                    'position' => 0,
                    'stack' => true,
                    'align_on_stack' => true,
                    'table_fold' => false,
                    'rows_fold' => false
                ]);
            $pivot = new DisplayTableFilter([
                'disp_table_id' => $dispTable->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbFinite->id,
            ]);
            $pivot->save();
            $pivot = new DisplayTableFilter([
                'disp_table_id' => $dispTable->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbSimple->id,
            ]);
            $pivot->save();
            $dispTableRow = DisplayTableRow::factory()
                ->for($dispTable)
                ->create([
                    'label' => '3\u{02B3}\u{1D48} pers. pres. sing.',
                    'label_brief' => '3\u{02B3}\u{1D48} pers. sing.',
                    'label_long' => 'third person, present, singular',
                    'position' => 0,
                ]);
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $dispTableRow->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPresent->id,
            ]);
            $pivot->save();
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $dispTableRow->id,
                'feature_id' => $verbPerson->id,
                'value_id' => $verbThird->id,
            ]);
            $pivot->save();
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $dispTableRow->id,
                'feature_id' => $verbNumber->id,
                'value_id' => $verbSingular->id,
            ]);
            $pivot->save();
            $dispTableRow = DisplayTableRow::factory()
                ->for($dispTable)
                ->create([
                    'label' => 'past tense',
                    'label_brief' => 'past',
                    'position' => 1,
                ]);
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $dispTableRow->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPast->id,
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
                    'label' => 'present',
                    'label_brief' => 'pres.',
                    'position' => 0,
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
                    'label' => 'past',
                    'position' => 1,
                ]);
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $dispTableRow->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbPast->id,
            ]);
            $pivot->save();
        });
    }

    protected static function generateName(): array
    {
        $prefixArray = [
            'con', 'ben', 'rap', 'bud', 'tak',
            'lop', 'kran', 'sop', 'bid', 'wag',
            'gas', 'his', 'drip', 'dis', 'val',
            'yup',
        ];
        $middleArray = [
            'nex', 'soob', 'kran', 'yart', 'rud',
            'beb', 'fidd', 'blatt', 'jiss', 'frass',
            'puck', 'bart', 'goy', 'flep', 'sitt',
            'foy', 'lurt', 'hoy', 'roy', 'lov',
            'vonn', 'vegg', 'varch', 'cratch', 'gloob',
            'darf', 'sov', 'tar', 'yem', 'yuss',
            'vass', 'boos', 'soos', 'biss',
        ];
        $suffixArray = [
            ['truncate' => false, 'suffix' => 'ian'],
            ['truncate' => false, 'suffix' => 'ese'],
            ['truncate' => false, 'suffix' => 'ish'],
            ['truncate' => true, 'suffix' => 'ian'],
            ['truncate' => true, 'suffix' => 'ese'],
            ['truncate' => true, 'suffix' => 'ish'],
            ['truncate' => true, 'suffix' => 'ench'],
            ['truncate' => true, 'suffix' => 'oonch'],
        ];
        $suffix = $suffixArray[array_rand($suffixArray)];
        $middle = $middleArray[array_rand($middleArray)];
        $prefix = $prefixArray[array_rand($prefixArray)];

        /**
         * If the suffix says to truncate, we are going to remove
         * the coda and nucleus of the middle syllable, so it
         * just becomes the beginning of the suffix.
         */
        if ($suffix['truncate']) {
            /**
             * Our middle syllables never have more than 2 consonants at the
             * beginning, and they always have at least 1. So we can just test
             * the 2nd character to see what length we need to cut to.
             */
            $c = $middle[1];
            $isVowel = ($c=='a' || $c=='e' || $c=='i' || $c=='o' || $c=='u');
            $len = ($isVowel? 1 : 2);
            $middle = substr($middle,0,$len);
        }

        $name = $prefix . $middle . $suffix['suffix'];

        return [
            'machine' => strtolower($name),
            'human' => ucfirst($name)
        ];
    }

    public function definition(): array
    {
        // Generate language name
        $name = self::generateName();

        // Generate language intro text
        $intro = collect($this->faker->paragraphs(3))
            ->map(function ($item) {
                return "\t<p>" . $item . "</p>\n";
            })
            ->implode("");
        $intro = "<div>\n" . $intro . "</div>\n";

        return [
            'machine_name' => $name['machine'],
            'name' => $name['human'],
            'dict_title' => $name['human'] . " Dictionary",
            'dict_title_full' => "English Dictionary of the " . $name['human'] . " Language",
            'intro' => $intro,
        ];
    }
}