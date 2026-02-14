<?php

/**
 * TRANSLATOR NOTE:
 *
 * For grammar presets, abbreviated names may be an empty string ''.
 * This is intentional and means that the word is short enough that
 * it doesn't need an abbreviation, for example the word "past" for
 * past tense verbs.
 */
return [
    'preset_name' => 'Spanish Grammar',

    /**
     * ===========================================================
     *                 TECHNICAL/ADMIN LABELS
     * ===========================================================
     *
     * TRANSLATOR NOTE:
     *
     * This first portion is mostly a list of technical terms. It
     * will display to admins who are configuring the conlang
     * grammar, not to general users who are viewing/browsing the
     * dictionary.
     *
     * Later on, there are other labels for display to lay people.
     */
    'word_classes' => [
        'adjective' => [
            'name' => 'adjective',
            'name_brief' => 'adj.',
        ],
        'numeral_adjective' => [
            'name' => 'numeral adjective',
            'name_brief' => 'num. adj.',
        ],
        'adverb' => [
            'name' => 'adverb',
            'name_brief' => 'adv.',
        ],
        'auxiliary_verb' => [
            'name' => 'auxiliary verb',
            'name_brief' => 'aux. v.',
        ],
        'verb' => [
            'name' => 'verb',
            'name_brief' => 'v.',
        ],
        'article' => [
            'name' => 'article',
            'name_brief' => 'art.',
        ],
        'determiner' => [
            'name' => 'determiner',
            'name_brief' => 'det.',
        ],
        'combining_form' => [
            'name' => 'combining form',
            'name_brief' => 'comb. f.',
        ],
        'conjunction' => [
            'name' => 'conjunction',
            'name_brief' => 'conjunc.',
        ],
        'contraction' => [
            'name' => 'contraction',
            'name_brief' => 'contrac.',
        ],
        'interjection' => [
            'name' => 'interjection',
            'name_brief' => 'interj.',
        ],
        'noun' => [
            'name' => 'noun',
            'name_brief' => 'n.',
        ],
        'proper_noun' => [
            'name' => 'proper noun',
            'name_brief' => 'prop. n.',
        ],
        'numeral_noun' => [
            'name' => 'numeral noun',
            'name_brief' => 'num. n.',
        ],
        'preposition' => [
            'name' => 'preposition',
            'name_brief' => 'prep.',
        ],
        'pronoun' => [
            'name' => 'pronoun',
            'name_brief' => 'pron.',
        ],
        'numeral_pronoun' => [
            'name' => 'numeral pronoun',
            'name_brief' => 'num. pron.',
        ],
    ],
    'gender' => [
        '_name' => 'gender',
        '_name_brief' => 'gen.',
        'masculine' => [
            'name' => 'masculine',
            'name_brief' => 'masc.',
        ],
        'feminine' => [
            'name' => 'feminine',
            'name_brief' => 'fem.',
        ],
        'neuter' => [
            'name' => 'neuter',
            'name_brief' => 'neu.',
        ],
    ],
    'number' => [
        '_name' => 'number',
        '_name_brief' => 'num.',
        'singular' => [
            'name' => 'singular',
            'name_brief' => 'sing.',
        ],
        'plural' => [
            'name' => 'plural',
            'name_brief' => 'pl.',
        ],
    ],
    'verb_role' => [
        '_name' => 'role',
        '_name_brief' => '', // empty because 'role' is already short enough, so no abbreviation is needed
        'infinitive' => [
            'name' => 'infinitive',
            'name_brief' => 'inf.',
        ],
        'finite' => [
            'name' => 'finite',
            'name_brief' => 'fin.',
        ],
        'gerund' => [
            'name' => 'gerund',
            'name_brief' => 'ger.',
        ],
        'participle' => [
            'name' => 'participle',
            'name_brief' => 'prcpl.',
        ],
    ],
    'tense' => [
        '_name' => 'tense',
        '_name_brief' => '', // empty because 'tense' is already short enough, so no abbreviation is needed
        'past' => [
            'name' => 'past',
            'name_brief' => '', // empty because 'past' is already short enough, so no abbreviation is needed
        ],
        'present' => [
            'name' => 'present',
            'name_brief' => 'pres.',
        ],
        'future' => [
            'name' => 'future',
            'name_brief' => 'fut.',
        ],
        'conditional' => [
            'name' => 'conditional',
            'name_brief' => 'cond.',
        ],
    ],
    'aspect' => [
        '_name' => 'aspect',
        '_name_brief' => '', // empty because 'aspect' is already short enough, so no abbreviation is needed
        'simple' => [
            'name' => 'simple',
            'name_brief' => 'sim.',
        ],
        'imperfective' => [
            'name' => 'imperfective',
            'name_brief' => 'imp.',
        ],
        'perfective' => [
            'name' => 'perfective',
            'name_brief' => 'perf.',
        ],
    ],
    'mood' => [
        '_name' => 'mood',
        '_name_brief' => '', // empty because 'mood' is already short enough, so no abbreviation is needed
        'indicative' => [
            'name' => 'indicative',
            'name_brief' => 'ind.',
        ],
        'subjunctive' => [
            'name' => 'subjunctive',
            'name_brief' => 'subj.',
        ],
        'imperative' => [
            'name' => 'imperative',
            'name_brief' => 'imper.',
        ],
    ],
    'person' => [
        '_name' => 'person',
        '_name_brief' => 'pers.',
        'first' => [
            'name' => 'first',
            'name_brief' => "1\u{02E2}\u{1D57}", // "1st" but uses Unicode superscripts for 'st'
        ],
        'second' => [
            'name' => 'second',
            'name_brief' => "2\u{207F}\u{1D48}", // "2nd" but uses Unicode superscripts for 'nd'
        ],
        'third' => [
            'name' => 'third',
            'name_brief' => "3\u{02B3}\u{1D48}", // "3rd" but uses Unicode superscripts for 'rd'
        ],
    ],
    'formality' => [
        '_name' => 'formality',
        '_name_brief' => 'form.',
        'familiar' => [
            'name' => 'familiar',
            'name_brief' => 'fam.',
        ],
        'formal' => [
            'name' => 'formal',
            'name_brief' => 'form.',
        ],
    ],
    'case' => [
        '_name' => 'case',
        '_name_brief' => '', // empty because 'case' is already short enough, so no abbreviation is needed
        'nominative' => [
            'name' => 'nominative',
            'name_brief' => 'nom.',
        ],
        'accusative' => [
            'name' => 'accusative',
            'name_brief' => 'accus.',
        ],
        'dative' => [
            'name' => 'dative',
            'name_brief' => 'dat.',
        ],
        'reflexive' => [
            'name' => 'reflexive',
            'name_brief' => 'refl.',
        ],
        'prepositional' => [
            'name' => 'prepositional',
            'name_brief' => 'prep.',
        ],
    ],

    /**
     * ============================================================
     *                 CASUAL / END-USER LABELS
     * ============================================================
     *
     * TRANSLATOR NOTE:
     *
     * These labels are shown when view/browsing entries in the
     * dictionary. Some will be the same as strings above, but the
     * viewing context is less technical so there's a need to be
     * layman-friendly. For example, "finite" becomes "finite verb"
     * and "past tense / imperfective aspect" becomes simply
     * "imperfect".
     *
     * When translating these, consider what terminology is commonly
     * used in your locale to describe English grammar.
     *
     * As before, an empty string for a '_brief' or '_long' field is
     * purposeful and signifies that no value is needed.
     */
    'inflection_tables' => [
        /**
         * These are labels that are used in more than one table...
         */
        'gender' => [
            /**
             * These are only used as row labels.
             */
            'masculine' => [
                'label' => 'masculine',
                'label_brief' => 'masc.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'feminine' => [
                'label' => 'feminine',
                'label_brief' => 'fem.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'neuter' => [
                'label' => 'neuter',
                'label_brief' => 'neu.',
                'label_long' => '', // none needed because the default is fully clear
            ],
        ],
        'number' => [
            /**
             * These are only used as column labels.
             */
            'singular' => [
                '_label' => 'singular',
            ],
            'plural' => [
                '_label' => 'plural',
            ],
        ],
        /**
         * Tables with special labels...
         */
        'verbs' => [
            /**
             * Column labels
             */
            'non_finite' => [
                '_label' => 'non-finite',
            ],
            'singular_first_person' => [
                '_label' => "s. 1\u{2E2}\u{1D57}-pers.",
            ],
            'singular_second_person' => [
                '_label' => "s. 2\u{207F}\u{1D48}-pers.",
            ],
            'singular_third_person' => [
                '_label' => "s. 3\u{02B3}\u{1D48}-pers.",
            ],
            'plural_first_person' => [
                '_label' => "pl. 1\u{2E2}\u{1D57}-pers.",
            ],
            'plural_second_person' => [
                '_label' => "pl. 2\u{207F}\u{1D48}-pers.",
            ],
            'plural_third_person' => [
                '_label' => "pl. 3\u{02B3}\u{1D48}-pers.",
            ],
            /**
             * Row labels
             */
            'infinitive' => [
                'label' => 'infinitive',
                'label_brief' => 'inf.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'gerund' => [
                'label' => 'gerund',
                'label_brief' => 'ger.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'present_indicative' => [
                'label' => 'present ind.',
                'label_brief' => 'pres. ind.',
                'label_long' => 'present indicative',
            ],
            'preterite_indicative' => [
                'label' => 'preterite ind.',
                'label_brief' => 'pret. ind.',
                'label_long' => 'preterite indicative',
            ],
            'imperfect_indicative' => [
                'label' => 'imperfect ind.',
                'label_brief' => 'imperf. ind.',
                'label_long' => 'imperfect indicative',
            ],
            'future_indicative' => [
                'label' => 'future ind.',
                'label_brief' => 'fut. ind.',
                'label_long' => 'future indicative',
            ],
            'conditional' => [
                'label' => 'conditional',
                'label_brief' => 'cond.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'present_subjunctive' => [
                'label' => 'present subj.',
                'label_brief' => 'pres. subj.',
                'label_long' => 'present subjunctive',
            ],
            'imperfect_subjunctive' => [
                'label' => 'imperfect subj.',
                'label_brief' => 'imperf. subj.',
                'label_long' => 'imperfect subjunctive',
            ],
            'future_subjunctive' => [
                'label' => 'future subj.',
                'label_brief' => 'fut. subj.',
                'label_long' => 'future subjunctive',
            ],
            'familiar_imperative' => [
                'label' => 'familiar impera.',
                'label_brief' => 'fam. impera.',
                'label_long' => 'familiar imperative',
            ],
            'formal_imperative' => [
                'label' => 'formal impera.',
                'label_brief' => 'form. impera.',
                'label_long' => 'formal imperative',
            ],
            'masculine_participle' => [
                'label' => 'masc. participle',
                'label_brief' => 'masc. partic.',
                'label_long' => 'masculine participle (adjectival)',
            ],
            'feminine_participle' => [
                'label' => 'fem. participle',
                'label_brief' => 'fem. partic.',
                'label_long' => 'feminine participle (adjectival)',
            ],
        ],
        'pronouns' => [
            /**
             * Column labels
             */
            'first_person' => [
                '_label' => 'first-person',
            ],
            'first_person_masc' => [
                '_label' => 'first-person m.',
            ],
            'first_person_fem' => [
                '_label' => 'first-person f.',
            ],
            'second_person' => [
                '_label' => 'second-person',
            ],
            'second_person_masc' => [
                '_label' => 'second-person m.',
            ],
            'second_person_fem' => [
                '_label' => 'second-person f.',
            ],
            'third_person_masc' => [
                '_label' => 'third-person m.',
            ],
            'third_person_fem' => [
                '_label' => 'third-person f.',
            ],
            /**
             * Row labels
             */
            'singular_nominative' => [
                'label' => 'sing. nominative',
                'label_brief' => 's. nom.',
                'label_long' => 'singular nominative (subject)',
            ],
            'singular_accusative' => [
                'label' => 'sing. accusative',
                'label_brief' => 's. accus.',
                'label_long' => 'singular accusative (direct object)',
            ],
            'singular_dative' => [
                'label' => 'sing. dative',
                'label_brief' => 's. dat.',
                'label_long' => 'singular dative (indirect object)',
            ],
            'singular_reflexive' => [
                'label' => 'sing. reflexive',
                'label_brief' => 's. refl.',
                'label_long' => 'singular reflexive (self-object)',
            ],
            'singular_prepositional' => [
                'label' => 'sing. prepositional',
                'label_brief' => 's. prep.',
                'label_long' => 'singular prepositional',
            ],
            'plural_nominative' => [
                'label' => 'pl. nominative',
                'label_brief' => 'pl. nom.',
                'label_long' => 'plural nominative (subject)',
            ],
            'plural_accusative' => [
                'label' => 'pl. accusative',
                'label_brief' => 'pl. accus.',
                'label_long' => 'plural accusative (direct object)',
            ],
            'plural_dative' => [
                'label' => 'pl. dative',
                'label_brief' => 'pl. dat.',
                'label_long' => 'plural dative (indirect object)',
            ],
            'plural_reflexive' => [
                'label' => 'pl. reflexive',
                'label_brief' => 'pl. refl.',
                'label_long' => 'plural reflexive (self-object)',
            ],
        ],
    ],

];
