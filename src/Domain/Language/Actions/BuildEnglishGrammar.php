<?php

namespace PeterMarkley\Tollerus\Domain\Language\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Language;
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
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'adjective', 'language_id'=>$language->id]);

            // Adverbs
            // -------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'adverb', 'language_id'=>$language->id]);

            // Verbs
            // -----
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'auxiliary verb', 'language_id'=>$language->id]);
            $wordClassGroup->wordClasses()->create(['name'=>'verb', 'language_id'=>$language->id]);
            // Add verb inflection features
            $verbRole = $wordClassGroup->features()->create(['name' => 'role']);
            $verbInfinitive = $verbRole->featureValues()->create(['name'=>'infinitive']);
            $verbFinite     = $verbRole->featureValues()->create(['name'=>'finite']);
            $verbParticiple = $verbRole->featureValues()->create(['name'=>'participle']);
            $verbTense = $wordClassGroup->features()->create(['name' => 'tense']);
            $verbPast    = $verbTense->featureValues()->create(['name'=>'past']);
            $verbPresent = $verbTense->featureValues()->create(['name'=>'present', 'name_brief'=>'pres.']);
            $verbAspect = $wordClassGroup->features()->create(['name' => 'aspect']);
            $verbPerfect     = $verbAspect->featureValues()->create(['name'=>'perfect', 'name_brief'=>'perf.']);
            $verbSimple      = $verbAspect->featureValues()->create(['name'=>'simple', 'name_brief'=>'sim.']);
            $verbProgressive = $verbAspect->featureValues()->create(['name'=>'progressive', 'name_brief'=>'prog.']);
            $verbNumber = $wordClassGroup->features()->create(['name' => 'number']);
            $verbSingular = $verbNumber->featureValues()->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $verbPlural   = $verbNumber->featureValues()->create(['name'=>'plural', 'name_brief'=>'pl.']);
            $verbPerson = $wordClassGroup->features()->create(['name' => 'person']);
            $verbFirst  = $verbPerson->featureValues()->create(['name'=>'first', 'name_brief'=>"1\u{02E2}\u{1D57}"]);
            $verbSecond = $verbPerson->featureValues()->create(['name'=>'second', 'name_brief'=>"2\u{207F}\u{1D48}"]);
            $verbThird  = $verbPerson->featureValues()->create(['name'=>'third', 'name_brief'=>"3\u{02B3}\u{1D48}"]);
            // Add verb inflection tables
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'infinitive',
                'position' => 0,
                'visible' => false,
                'stack' => false,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbInfinitive->id,
            ]))->save();
            $baseRow = $table->rows()->create([
                'label' => "infinitive",
                'label_brief' => "inf.",
                'position' => 0,
            ]);
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'finite verb',
                'position' => 1,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbFinite->id,
            ]))->save();
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbSimple->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => "3\u{02B3}\u{1D48} pers. pres. sing.",
                'label_brief' => "3\u{02B3}\u{1D48} pers. sing.",
                'label_long' => 'third person present singular',
                'position' => 0,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPresent->id,
            ]))->save();
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbPerson->id,
                'value_id' => $verbThird->id,
            ]))->save();
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbNumber->id,
                'value_id' => $verbSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => 'past tense',
                'label_brief' => 'past',
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPast->id,
            ]))->save();
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'participle',
                'position' => 2,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $verbRole->id,
                'value_id' => $verbParticiple->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => 'present',
                'label_brief' => 'pres.',
                'position' => 0,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbProgressive->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => 'past',
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbPerfect->id,
            ]))->save();

            // Combining Forms
            // ---------------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'combining form', 'language_id'=>$language->id]);

            // Contractions
            // ------------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'contraction', 'language_id'=>$language->id]);

            // Conjunctions
            // ------------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'conjunction', 'language_id'=>$language->id]);

            // Determiners
            // -----------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'determiner', 'language_id'=>$language->id]);

            // Nouns
            // -----
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'noun', 'language_id'=>$language->id]);
            $wordClassGroup->wordClasses()->create(['name'=>'proper noun', 'language_id'=>$language->id]);
            // Add noun inflection features
            $nounNumber = $wordClassGroup->features()->create(['name' => 'number']);
            $nounSingular = $nounNumber->featureValues()->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $nounPlural   = $nounNumber->featureValues()->create(['name'=>'plural', 'name_brief'=>'pl.']);
            // Add noun inflection tables
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'noun',
                'position' => 0,
                'show_label' => false,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            $baseRow = $table->rows()->create([
                'label' => "singular",
                'label_brief' => "sing.",
                'position' => 0,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $baseRow->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => "plural",
                'label_brief' => "pl.",
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounPlural->id,
            ]))->save();

            // Prepositions
            // ------------
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'preposition', 'language_id'=>$language->id]);

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
            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create(['name'=>'pronoun', 'language_id'=>$language->id]);
            // Add pronoun inflection features
            $pronounNumber = $wordClassGroup->features()->create(['name' => 'number']);
            $pronounSingular = $pronounNumber->featureValues()->create(['name'=>'singular', 'name_brief'=>'sing.']);
            $pronounPlural   = $pronounNumber->featureValues()->create(['name'=>'plural', 'name_brief'=>'pl.']);
            $pronounCase = $wordClassGroup->features()->create(['name' => 'case']);
            $pronounSubjective = $pronounCase->featureValues()->create(['name'=>'subjective', 'name_brief'=>'sub.']);
            $pronounObjective  = $pronounCase->featureValues()->create(['name'=>'objective', 'name_brief'=>'obj.']);
            // Add pronoun inflection tables
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'subjective',
                'position' => 0,
                'stack' => true,
                'align_on_stack' => true,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $pronounCase->id,
                'value_id' => $pronounSubjective->id,
            ]))->save();
            $baseRow = $table->rows()->create([
                'label' => "singular",
                'label_brief' => "sing.",
                'position' => 0,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $baseRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => "plural",
                'label_brief' => "pl.",
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounPlural->id,
            ]))->save();
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => 'objective',
                'position' => 1,
                'stack' => true,
                'align_on_stack' => true,
                'table_fold' => false,
                'rows_fold' => true
            ]);
            (new InflectionTableFilter([
                'inflect_table_id' => $table->id,
                'feature_id' => $pronounCase->id,
                'value_id' => $pronounObjective->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => "singular",
                'label_brief' => "sing.",
                'position' => 0,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => "plural",
                'label_brief' => "pl.",
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounPlural->id,
            ]))->save();
            return 1;
        });
    }
}
