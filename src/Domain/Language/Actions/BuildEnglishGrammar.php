<?php

namespace PeterMarkley\Tollerus\Domain\Language\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\InflectionTableRow;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableRowFilter;

final class BuildEnglishGrammar
{
    /**
     * This will add WordClassGroups, Features/FeatureValues, and InflectionTables
     * matching real-life English grammar. Used for testing/demo purposes, or
     * as a preset for users to modify.
     */
    public function __invoke(Language $language): int
    {
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($language) {
            // Prevent collisions from any other processes
            Language::query()
                ->whereKey($language->getKey())
                ->lockForUpdate()
                ->first();
            // Prevent duplicate grammar data
            $exists = WordClassGroup::query()
                ->whereBelongsTo($language)
                ->exists();
            if ($exists) {
                throw new \DomainException('Grammar already initialized for this language.');
            }

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
            $verbRole = Feature::factory()->for($group,'group')->create(['name' => 'role']);
            $verbInfinitive = FeatureValue::factory()->for($verbRole)->create(['name'=>'infinitive']);
            $verbFinite     = FeatureValue::factory()->for($verbRole)->create(['name'=>'finite']);
            $verbParticiple = FeatureValue::factory()->for($verbRole)->create(['name'=>'participle']);
            $verbTense = Feature::factory()->for($group,'group')->create(['name' => 'tense']);
            $verbPast    = FeatureValue::factory()->for($verbTense)->create(['name'=>'past']);
            $verbPresent = FeatureValue::factory()->for($verbTense)->create(['name'=>'present', 'name_brief'=>'pres.']);
            $verbAspect = Feature::factory()->for($group,'group')->create(['name' => 'aspect']);
            $verbPerfect     = FeatureValue::factory()->for($verbAspect)->create(['name'=>'perfect', 'name_brief'=>'perf.']);
            $verbSimple      = FeatureValue::factory()->for($verbAspect)->create(['name'=>'simple']);
            $verbProgressive = FeatureValue::factory()->for($verbAspect)->create(['name'=>'progressive', 'name_brief'=>'prog.']);
            $verbNumber = Feature::factory()->for($group,'group')->create(['name' => 'number']);
            $verbSingular = FeatureValue::factory()->for($verbNumber)->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $verbPlural   = FeatureValue::factory()->for($verbNumber)->create(['name'=>'plural', 'name_brief'=>'pl.']);
            $verbPerson = Feature::factory()->for($group,'group')->create(['name' => 'person']);
            $verbFirst  = FeatureValue::factory()->for($verbPerson)->create(['name'=>'first', 'name_brief'=>"1\u{02E2}\u{1D57}"]);
            $verbSecond = FeatureValue::factory()->for($verbPerson)->create(['name'=>'second', 'name_brief'=>"2\u{207F}\u{1D48}"]);
            $verbThird  = FeatureValue::factory()->for($verbPerson)->create(['name'=>'third', 'name_brief'=>"3\u{02B3}\u{1D48}"]);
            // Add verb inflection tables
            $inflectionTable = InflectionTable::factory()
                ->for($group)
                ->create([
                    'label' => 'finite verb',
                    'position' => 0,
                    'stack' => true,
                    'align_on_stack' => false,
                    'table_fold' => false,
                    'rows_fold' => false
                ]);
            $pivot = new InflectionTableFilter([
                'inflect_table_id' => $inflectionTable->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbFinite->id,
            ]);
            $pivot->save();
            $pivot = new InflectionTableFilter([
                'inflect_table_id' => $inflectionTable->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbSimple->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "3\u{02B3}\u{1D48} pers. pres. sing.",
                    'label_brief' => "3\u{02B3}\u{1D48} pers. sing.",
                    'label_long' => 'third person present singular',
                    'position' => 0,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPresent->id,
            ]);
            $pivot->save();
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbPerson->id,
                'value_id' => $verbThird->id,
            ]);
            $pivot->save();
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbNumber->id,
                'value_id' => $verbSingular->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => 'past tense',
                    'label_brief' => 'past',
                    'position' => 1,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPast->id,
            ]);
            $pivot->save();
            $inflectionTable = InflectionTable::factory()
                ->for($group)
                ->create([
                    'label' => 'participle',
                    'position' => 1,
                    'stack' => true,
                    'align_on_stack' => false,
                    'table_fold' => false,
                    'rows_fold' => false
                ]);
            $pivot = new InflectionTableFilter([
                'inflect_table_id' => $inflectionTable->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbParticiple->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => 'present',
                    'label_brief' => 'pres.',
                    'position' => 0,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbProgressive->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => 'past',
                    'position' => 1,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbPerfect->id,
            ]);
            $pivot->save();

            // Combining Forms
            // ---------------
            WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'combining form'])
                )->create();

            // Contractions
            // ------------
            WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'contraction'])
                )->create();

            // Conjunctions
            // ------------
            WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'conjunction'])
                )->create();

            // Determiners
            // -----------
            WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'determiner'])
                )->create();

            // Nouns
            // -----
            $group = WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'noun'])
                )->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'proper noun'])
                )->create(['inflected'=>true]);
            // Add noun inflection features
            $nounNumber = Feature::factory()->for($group,'group')->create(['name' => 'number']);
            $nounSingular = FeatureValue::factory()->for($nounNumber)->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $nounPlural   = FeatureValue::factory()->for($nounNumber)->create(['name'=>'plural', 'name_brief'=>'pl.']);
            // Add noun inflection tables
            $inflectionTable = InflectionTable::factory()
                ->for($group)
                ->create([
                    'label' => 'noun',
                    'position' => 0,
                    'show_label' => false,
                    'stack' => true,
                    'align_on_stack' => false,
                    'table_fold' => false,
                    'rows_fold' => false
                ]);
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "singular",
                    'label_brief' => "sing.",
                    'position' => 0,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounSingular->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "plural",
                    'label_brief' => "pl.",
                    'position' => 1,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounPlural->id,
            ]);
            $pivot->save();

            // Prepositions
            // ------------
            WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'preposition'])
                )->create();

            // Pronouns
            // --------
            /**
             * In English, personal pronouns are inflected by not just number, but also
             * case: subjective vs. objective.
             *
             * They also inflect by person and gender, but those inflections don't affect
             * syntax. So "I" and "you" can be separate entries in the dictionary, whereas
             * "I" and "me" benefit from sharing an inflection table on one entry.
             */
            $group = WordClassGroup::factory()
                ->for($language)
                ->has(WordClass::factory()
                    ->for($language)
                    ->state(['name'=>'pronoun'])
                )->create(['inflected'=>true]);
            // Add pronoun inflection features
            $pronounNumber = Feature::factory()->for($group,'group')->create(['name' => 'number']);
            $pronounSingular = FeatureValue::factory()->for($pronounNumber)->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $pronounPlural   = FeatureValue::factory()->for($pronounNumber)->create(['name'=>'plural', 'name_brief'=>'pl.']);
            $pronounCase = Feature::factory()->for($group,'group')->create(['name' => 'case']);
            $pronounSubjective = FeatureValue::factory()->for($pronounCase)->create(['name'=>'subjective', 'name_brief'=>'sub.']);
            $pronounObjective  = FeatureValue::factory()->for($pronounCase)->create(['name'=>'objective', 'name_brief'=>'obj.']);
            // Add pronoun inflection tables
            $inflectionTable = InflectionTable::factory()
                ->for($group)
                ->create([
                    'label' => 'subjective',
                    'position' => 0,
                    'stack' => true,
                    'align_on_stack' => true,
                    'table_fold' => false,
                    'rows_fold' => false
                ]);
            $pivot = new InflectionTableFilter([
                'inflect_table_id' => $inflectionTable->id,
                'feature_id' => $pronounCase->id,
                'value_id' => $pronounSubjective->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "singular",
                    'label_brief' => "sing.",
                    'position' => 0,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "plural",
                    'label_brief' => "pl.",
                    'position' => 1,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounPlural->id,
            ]);
            $pivot->save();
            $inflectionTable = InflectionTable::factory()
                ->for($group)
                ->create([
                    'label' => 'objective',
                    'position' => 1,
                    'stack' => true,
                    'align_on_stack' => true,
                    'table_fold' => false,
                    'rows_fold' => true
                ]);
            $pivot = new InflectionTableFilter([
                'inflect_table_id' => $inflectionTable->id,
                'feature_id' => $pronounCase->id,
                'value_id' => $pronounObjective->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "singular",
                    'label_brief' => "sing.",
                    'position' => 0,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]);
            $pivot->save();
            $inflectionTableRow = InflectionTableRow::factory()
                ->for($inflectionTable)
                ->create([
                    'label' => "plural",
                    'label_brief' => "pl.",
                    'position' => 1,
                ]);
            $pivot = new InflectionTableRowFilter([
                'inflect_table_row_id' => $inflectionTableRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounPlural->id,
            ]);
            $pivot->save();
            return 1;
        });
    }
}
