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

            /**
             * ============================
             *         ADJECTIVES
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.adjective.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.adjective.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *           ADVERBS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.adverb.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.adverb.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *           VERBS
             * ============================
             */

            $wordClassGroup = $language->wordClassGroups()->create();
            $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.auxiliary_verb.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.auxiliary_verb.name_brief'),
                'language_id' => $language->id
            ]);
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.verb.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.verb.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * Add verb inflection features
             * ----------------------------
             */
            // Role -----------------------
            $verbRole = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.verb_role._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.verb_role._name_brief'),
            ]);
            $verbInfinitive = $verbRole->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.verb_role.infinitive.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.verb_role.infinitive.name_brief'),
            ]);
            $verbFinite = $verbRole->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.verb_role.finite.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.verb_role.finite.name_brief'),
            ]);
            $verbParticiple = $verbRole->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.verb_role.participle.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.verb_role.participle.name_brief'),
            ]);
            // Tense -----------------------
            $verbTense = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.tense._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.tense._name_brief'),
            ]);
            $verbPast = $verbTense->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.tense.past.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.tense.past.name_brief'),
            ]);
            $verbPresent = $verbTense->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.tense.present.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.tense.present.name_brief'),
            ]);
            // Aspect -----------------------
            $verbAspect = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.aspect._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.aspect._name_brief'),
            ]);
            $verbPerfect = $verbAspect->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.aspect.perfect.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.aspect.perfect.name_brief'),
            ]);
            $verbSimple = $verbAspect->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.aspect.simple.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.aspect.simple.name_brief'),
            ]);
            $verbProgressive = $verbAspect->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.aspect.progressive.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.aspect.progressive.name_brief'),
            ]);
            // Number -----------------------
            $verbNumber = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.number._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number._name_brief'),
            ]);
            $verbSingular = $verbNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.singular.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.singular.name_brief'),
            ]);
            $verbPlural = $verbNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.plural.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.plural.name_brief'),
            ]);
            // Person -----------------------
            $verbPerson = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.person._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.person._name_brief'),
            ]);
            $verbFirst = $verbPerson->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.person.first.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.person.first.name_brief'),
            ]);
            $verbSecond = $verbPerson->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.person.second.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.person.second.name_brief'),
            ]);
            $verbThird = $verbPerson->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.person.third.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.person.third.name_brief'),
            ]);

            /**
             * Add verb inflection tables
             * --------------------------
             */
            // Infinitive (hidden from UI)
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.infinitive.label'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.infinitive.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.infinitive.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.infinitive.label_long'),
                'position' => 0,
            ]);
            // Finite verb -----------------------
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.finite_verb._label'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.finite_verb.third_person_singular.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.finite_verb.third_person_singular.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.finite_verb.third_person_singular.label_long'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.finite_verb.past_tense.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.finite_verb.past_tense.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.finite_verb.past_tense.label_long'),
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbTense->id,
                'value_id' => $verbPast->id,
            ]))->save();
            // Participle -----------------------
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.participle._label'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.participle.present.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.participle.present.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.participle.present.label_long'),
                'position' => 0,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbProgressive->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.participle.past.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.participle.past.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.participle.past.label_long'),
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $verbAspect->id,
                'value_id' => $verbPerfect->id,
            ]))->save();

            /**
             * ============================
             *       COMBINING FORMS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.combining_form.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.combining_form.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *         CONTRACTIONS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.contraction.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.contraction.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *         CONJUNCTIONS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.conjunction.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.conjunction.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *         DETERMINERS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.determiner.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.determiner.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *           NOUNS
             * ============================
             */

            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.noun.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.noun.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();
            $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.proper_noun.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.proper_noun.name_brief'),
                'language_id' => $language->id
            ]);

            // Add noun inflection features
            $nounNumber = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.number._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number._name_brief'),
            ]);
            $nounSingular = $nounNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.singular.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.singular.name_brief'),
            ]);
            $nounPlural = $nounNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.plural.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.plural.name_brief'),
            ]);

            // Add noun inflection tables
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.noun._label'),
                'position' => 0,
                'show_label' => false,
                'stack' => true,
                'align_on_stack' => false,
                'table_fold' => false,
                'rows_fold' => false
            ]);
            $baseRow = $table->rows()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.singular.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_long'),
                'position' => 0,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $baseRow->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.plural.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_long'),
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $nounNumber->id,
                'value_id' => $nounPlural->id,
            ]))->save();

            /**
             * ============================
             *        PREPOSITIONS
             * ============================
             */
            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.preposition.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.preposition.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            /**
             * ============================
             *          PRONOUNS
             * ============================
             *
             * In English, personal pronouns are inflected by not just number, but also
             * case: subjective vs. objective.
             *
             * They also inflect by person and gender, but those inflections don't affect
             * syntax. So "I" and "you" can be separate entries in the dictionary, whereas
             * "I" and "me" benefit from sharing an inflection table on one entry.
             */

            $wordClassGroup = $language->wordClassGroups()->create();
            $primaryClass = $wordClassGroup->wordClasses()->create([
                'name' => __('tollerus::grammar_presets/english.word_classes.pronoun.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.word_classes.pronoun.name_brief'),
                'language_id' => $language->id
            ]);
            $wordClassGroup->primary_class = $primaryClass->id;
            $wordClassGroup->save();

            // Add pronoun inflection features
            $pronounNumber = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.number._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number._name_brief'),
            ]);
            $pronounSingular = $pronounNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.singular.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.singular.name_brief'),
            ]);
            $pronounPlural   = $pronounNumber->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.number.plural.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.number.plural.name_brief'),
            ]);
            $pronounCase = $wordClassGroup->features()->create([
                'name' => __('tollerus::grammar_presets/english.case._name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.case._name_brief'),
            ]);
            $pronounSubjective = $pronounCase->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.case.subjective.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.case.subjective.name_brief'),
            ]);
            $pronounObjective  = $pronounCase->featureValues()->create([
                'name' => __('tollerus::grammar_presets/english.case.objective.name'),
                'name_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.case.objective.name_brief'),
            ]);

            // Add pronoun inflection tables
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.subjective._label'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.singular.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_long'),
                'position' => 0,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $baseRow->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.plural.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_long'),
                'position' => 1,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounPlural->id,
            ]))->save();
            $table = $wordClassGroup->inflectionTables()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.objective._label'),
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
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.singular.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.singular.label_long'),
                'position' => 0,
                'src_base' => $baseRow->id,
            ]);
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $row->id,
                'feature_id' => $pronounNumber->id,
                'value_id' => $pronounSingular->id,
            ]))->save();
            $row = $table->rows()->create([
                'label' => __('tollerus::grammar_presets/english.inflection_tables.number.plural.label'),
                'label_brief' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_brief'),
                'label_long' => tollerus_tr_optional('tollerus::grammar_presets/english.inflection_tables.number.plural.label_long'),
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
