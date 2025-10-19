<?php

/**
 * For grammar presets, abbreviated names may be an empty string ''.
 * This is intentional and means that the word is short enough that
 * it doesn't need an abbreviation, for example the word "past" for
 * past tense verbs.
 */
return [

    /**
     * ===========================================================
     *                 TECHNICAL/ADMIN LABELS
     * ===========================================================
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
        'combining_form' => [
            'name' => 'combining form',
            'name_brief' => 'comb. f.',
        ],
        'contraction' => [
            'name' => 'contraction',
            'name_brief' => 'contrac.',
        ],
        'conjunction' => [
            'name' => 'conjunction',
            'name_brief' => 'conjunc.',
        ],
        'determiner' => [
            'name' => 'determiner',
            'name_brief' => 'det.',
        ],
        'noun' => [
            'name' => 'noun',
            'name_brief' => 'n.',
        ],
        'proper_noun' => [
            'name' => 'proper noun',
            'name_brief' => 'prop. n.',
        ],
        'preposition' => [
            'name' => 'preposition',
            'name_brief' => 'prep.',
        ],
        'pronoun' => [
            'name' => 'pronoun',
            'name_brief' => 'pron.',
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
    ],
    'aspect' => [
        '_name' => 'aspect',
        '_name_brief' => '', // empty because 'aspect' is already short enough, so no abbreviation is needed
        'perfect' => [
            'name' => 'perfect',
            'name_brief' => 'perf.',
        ],
        'simple' => [
            'name' => 'simple',
            'name_brief' => 'sim.',
        ],
        'progressive' => [
            'name' => 'progressive',
            'name_brief' => 'prog.',
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
    'case' => [
        '_name' => 'case',
        '_name_brief' => '', // empty because 'case' is already short enough, so no abbreviation is needed
        'subjective' => [
            'name' => 'subjective',
            'name_brief' => 'sub.',
        ],
        'objective' => [
            'name' => 'objective',
            'name_brief' => 'obj.',
        ],
    ],

    /**
     * ============================================================
     *                 CASUAL / END-USER LABELS
     * ============================================================
     *
     * These labels are shown when view/browsing entries in the
     * dictionary. Some will be the same as strings above, but the
     * viewing context is less technical so there's a need to be
     * layman-friendly. For example, "finite" becomes "finite verb"
     * and "progressive aspect" becomes "present participle".
     *
     * It's an English-centric idea to conflate participle with
     * aspect, and then co-opt the terminology of tense for it
     * instead of its proper aspect terminology. But that's what a
     * lay person would understand (reflecting what is generally
     * taught in grade school).
     *
     * When translating these, consider what terminology is commonly
     * used in your locale to describe English grammar.
     *
     * As before, an empty string for a '_brief' or '_long' field is
     * purposeful and signifies that no value is needed.
     */
    'inflection_tables' => [
        'infinitive' => [
            /**
             * This row label doubles as the label for the table itself,
             * hence no `_label` key.
             */
            'label' => 'infinitive',
            'label_brief' => 'inf.',
            'label_long' => '', // none needed, because the default is fully clear
        ],
        'finite_verb' => [
            /**
             * The reason in English that we have "finite verb" here
             * instead of just "finite" is because the word "finite"
             * is not obviously a grammatical term. In casual usage
             * it more commonly means "limited in scope or extent;
             * not infinite," like in "Man's knowledge is finite."
             *
             * So we are putting it in a phrase "finite verb" to
             * help show that it's a grammatical term referring to
             * verb syntax.
             */
            '_label' => 'finite verb',
            'third_person_singular' => [
                /**
                 * Cramming a lot of info into this label, so we
                 * need to abbreviate the default and then use the
                 * 'label_long' field.
                 */
                'label' => "3\u{02B3}\u{1D48} pers. pres. sing.", // default is already partly abbreviated
                'label_brief' => "3\u{02B3}\u{1D48} pers. sing.", // '_brief' is even more abbreivated
                'label_long' => 'third person present singular', // '_long' is needed for non-abbreviation
            ],
            'past_tense' => [
                'label' => 'past tense',
                'label_brief' => 'past',
                'label_long' => '', // none needed because the default is fully clear
            ],
        ],
        'participle' => [
            '_label' => 'participle',
            'present' => [
                'label' => 'present',
                'label_brief' => 'pres.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'past' => [
                'label' => 'past',
                'label_brief' => '', // none needed because 'past' is short enough
                'label_long' => '', // none needed because the default is fully clear
            ],
        ],
        'number' => [
            'singular' => [
                'label' => 'singular',
                'label_brief' => 'sing.',
                'label_long' => '', // none needed because the default is fully clear
            ],
            'plural' => [
                'label' => 'plural',
                'label_brief' => 'pl.',
                'label_long' => '', // none needed because the default is fully clear
            ],
        ],
        /**
         * These three tables all re-use the row labels under 'number',
         * so all we need for them is a label for the table itself.
         */
        'noun' => ['_label' => 'noun'],
        'subjective' => ['_label' => 'subjective'],
        'objective' => ['_label' => 'objective'],
    ],

];
