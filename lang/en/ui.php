<?php

return [
    /**
     * TRANSLATOR NOTE:
     *
     * "Conlang" is short for "constructed language," i.e. an
     * artificial or fictional language. "Tollerus" is the
     * name of this software package.
     */
    'tollerus_welcome' => 'Welcome to Tollerus, the Laravel system for conlang dictionaries!',
    'tollerus_description' => 'The luxurious way to build, track, and browse your conlang\'s lexical data.',
    'tollerus_admin_area' => 'Tollerus Admin Area',
    'how_to_use' => 'How to use',
    'coming_soon' => '(This is coming soon!)',
    'tollerus_on_github' => 'Tollerus on GitHub',
    /**
     * TRANSLATOR NOTE:
     *
     * "Peter Markley" -- that's me, the author of this
     * software. 👋😊
     */
    'peter_markleys_portfolio' => 'Peter Markley\'s Portfolio',
    'start_here' => 'Start here!',
    'admin' => 'Admin',
    'language' => 'Language',
    'languages' => 'Languages',
    'add_language' => 'Add language',
    /**
     * TRANSLATOR NOTE:
     *
     * A "neography" is a fictional writing system. ("Neo-"
     * because someone made it up as a "new" idea.) The
     * more familiar English terms are "script" or
     * "alphabet."
     *
     * But script can have many more meanings depending on
     * context (like a movie script, or a computer script)
     * and "alphabet" excludes a variety of other kinds of
     * writing system. That's why we prefer the term
     * "neography" here.
     *
     * (Another term is "conscript," used here later in the
     * name "ConScript Unicode Registry." It's a synonym
     * for neography.)
     */
    'neography' => 'Neography',
    'neographies' => 'Neographies',
    'add_neography' => 'Add neography',
    'edit_neographies' => 'Edit neographies',
    'language_neographies_context_notice' => 'These options configure the neographies specifically for :language.',
    'edit_all_neographies' => 'Edit all neographies',
    'primary' => 'Primary',
    'primary_neography' => 'Primary neography',
    'primary_neography_name' => 'Primary neography: :name',
    'set_this_as_primary' => 'Set this as primary',
    'set_primary_as_name' => 'Set primary as :name',
    'primary_must_be_active' => '(Primary must be active)',
    'is_primary' => 'Primary?', // Label that controls whether or not a given neography is primary
    'no_neographies' => '(No Neographies)',
    'grammar' => 'Grammar',
    'no_grammar' => '(No Grammar)',
    'entry' => 'Entry', // A word entry in a dictionary
    'entries' => 'Entries',
    'no_entries' => '(No Entries)',
    'entry_nameless' => '(entry)',
    'none' => '(none)',
    'writing_direction' => 'Writing direction',
    'writing_direction_primary' => 'Primary',
    'writing_direction_secondary' => 'Secondary',
    'left_to_right' => 'Left to right',
    'right_to_left' => 'Right to left',
    'top_to_bottom' => 'Top to bottom',
    'bottom_to_top' => 'Bottom to top',
    'direction_primary_description' => 'Direction within a line of text',
    'direction_secondary_description' => 'Feed direction at line breaks (i.e. paragraph fill direction)',
    'incomlete_display_notice' => 'Not all possible settings will display correctly.',
    /**
     * TRANSLATOR NOTE:
     *
     * This prints at the bottom of a page with paginated
     * results, telling what numbers of items are shown.
     */
    'pagination_showing_numbers' => 'Showing :start-:end of :total',
    /**
     * TRANSLATOR NOTE:
     *
     * "Boustrophedon" is a Greek term meaning "as the ox
     * plows," and refers to a writing mode where the
     * direction switches on each line break.
     * See here: https://en.wikipedia.org/wiki/Boustrophedon
     *
     * Don't fuss too much if this single word is difficult
     * to translate. It's just a checkbox label; it will
     * appear next to the `boustrophedon_description` field
     * (near the bottom of this document, in the Markdown
     * section) where you can explain more fully and even
     * link to the Wikipedia article.
     */
    'boustrophedon' => 'Boustrophedon',
    'svg_format' => 'SVG format',
    'ttf_format' => 'TTF format',
    /**
     * TRANSLATOR NOTE:
     *
     * These are the full names for official file standards.
     * See also:
     * - https://en.wikipedia.org/wiki/SVG
     * - https://en.wikipedia.org/wiki/TrueType
     */
    'scalable_vector_graphics' => 'Scalable Vector Graphics',
    'truetype_font' => 'TrueType Font',
    'upload_file' => 'Upload file',
    'delete_file' => 'Delete file',
    'copy_to_clipboard' => 'Copy to clipboard',
    'copied_to_clipboard' => 'Copied to clipboard',
    'asset_url' => 'Asset URL',
    'get_url' => 'Get URL',
    /**
     * TRANSLATOR NOTE:
     *
     * "Glyph" here is a term borrowed from the field of
     * typography, referring to a shape of type inside a
     * font. For a neography, it means a single letter or
     * symbol in the alphabet (or other writing system).
     */
    'glyphs' => 'Glyphs',
    'glyph' => 'Glyph',
    'no_glyphs' => '(No Glyphs)',
    'sections' => 'Sections',
    'glyph_groups' => 'Glyph groups',
    'unicode' => 'Unicode',
    'hexadecimal' => 'Hexadecimal',
    /**
     * TRANSLATOR NOTE:
     *
     * "Type" as in kind or classification. This will be
     * used with neography sections and the glyphs within.
     *
     * Just below this are translations for each enumerated
     * type of those things.
     */
    'type' => 'Type',
    'alphabet' => 'Alphabet',
    'abugida' => 'Abugida',
    'syllabary' => 'Syllabary',
    'logography' => 'Logography',
    'numerals' => 'Numerals',
    'symbol' => 'Symbol', // e.g. a letter or character
    'mark' => 'Mark', // e.g. an apostrophe or accent mark
    'numeral' => 'Numeral',
    /**
     * TRANSLATOR NOTE:
     *
     * "Render on base" as in, render the given glyph over a
     * Unicode "dotted circle" (U+25CC). Used for mark glyphs
     * that need a placeholder base for display (like accents
     * or diacritics, etc).
     */
    'render_on_base' => 'Render on base?',
    /**
     * TRANSLATOR NOTE:
     *
     * "Direct meaning" / "Spoken form"
     *
     * These are headings for settings inside a glyph. The
     * "spoken form" is for when a glyph encodes something
     * beyond mere sound, for example a numeral. (Like how
     * the numeral '1' is pronounced as "one.")
     */
    'direct_meaning' => 'Direct meaning',
    'spoken_form' => 'Spoken form',
    'note' => 'Note',
    'public_id' => 'Public ID',
    'definition' => 'Definition', // i.e. the meaning of a word
    'word_origin' => 'Word origin',
    /**
     * TRANSLATOR NOTE:
     *
     * "Search type" is the label for a setting on a search
     * bar for word entries. It lets the user pick
     * 'transliterated', 'native', or 'definition' as the thing
     * that they're searching.
     *
     * "Search term" is what the user types in the search bar.
     * "Search for entry ..." is a placeholder in the text box.
     */
    'search_for_entry' => 'Search for entry ...',
    'search_type' => 'Search type',
    'search_term' => 'Search term',
    'submit_search' => 'Submit search',
    'no_results' => '(No results)',
    /**
     * TRANSLATOR NOTE:
     *
     * "Transfer" as in move or transplant an object from one
     * place to another. Used for moving glyphs across groups
     * or neography sections.
     *
     * We avoid the term "move" just to help distinguish from
     * the smaller "move up"/"move down" actions in the same
     * UI (see below) that change glyph order within a group.
     *
     * The ellipsis (...) is because, unlike the reorder
     * buttons, this button will open a dialogue prompting
     * the user to select a destination.
     */
    'transfer_to' => 'Transfer to ...',
    'transfer_group_to' => 'Transfer group to ...',
    'visible' => 'Visible',
    'name' => 'Name',
    'human_friendly' => 'Human-friendly',
    'machine_friendly' => 'Machine-friendly',
    'dictionary_info' => 'Dictionary info',
    'title_short' => 'Title (short)',
    'title_full' => 'Title (full)',
    'author' => 'Author',
    'intro' => 'Intro',
    'save' => 'Save',
    'saving' => 'Saving ...',
    'saved' => '(Saved)',
    'reset' => 'Reset', // i.e. reset the interface; revert/discard changes
    'load' => 'Load',
    'loading' => 'Loading ...',
    'info' => 'Info',
    'font' => 'Font',
    'keyboards' => 'Keyboards',
    'edit' => 'Edit',
    'edit_thing' => 'Edit :thing',
    'select' => 'Select',
    'delete' => 'Delete',
    'delete_thing' => 'Delete :thing',
    'untitled' => 'untitled',
    'empty' => 'empty',
    /**
     * TRANSLATOR NOTE:
     *
     * "Add grammar group"
     *
     * In the source code, this is called a "word class
     * group," i.e. a group of word classes (or "parts of
     * speech"). These are grouped according to shared
     * grammatical rules: for example common nouns and
     * proper nouns are technically 2 different parts of
     * speech, but they follow the same rules. So they
     * would belong in the same "word class group" and
     * the rules are defined per group.
     *
     * However "grammar group" is a little more casually
     * comprehensible, so we use that phrase here for the
     * user.
     */
    'add_word_class_group' => 'Add grammar group',
    'delete_word_class_group' => 'Delete grammar group',
    'add_word_class' => 'Add word class',
    'delete_word_class' => 'Delete word class',
    'word_class' => 'Word class',
    'word_classes' => 'Word classes',
    'abbreviation' => 'Abbreviation',
    'group_nameless' => '(group)',
    'features' => 'Features', // i.e. grammatical features or dimensions, for example tense / person / number
    'feature' => 'Feature',
    'add_feature' => 'Add feature',
    'delete_feature' => 'Delete feature',
    'feature_values' => 'Values', // i.e. specific values of grammatical features, for example past-tense / first-person / plural
    'feature_value' => 'Value',
    'add_feature_value' => 'Add value',
    'delete_feature_value' => 'Delete value',
    'preset' => 'Preset', // i.e. a predefined configuration for user convenience
    'inflection_tables' => 'Inflection tables',
    'inflection_table' => 'Inflection table',
    'no_inflection_tables' => 'No inflection tables',
    'add_inflection_table' => 'Add inflection table',
    'move_inflection_table_up' => 'Move table up',
    'move_inflection_table_down' => 'Move table down',
    'filters' => 'Filters',
    'add_filter' => 'Add filter',
    'remove_filter' => 'Remove filter',
    'rows' => 'Rows',
    'add_row' => 'Add row',
    'delete_row' => 'Delete row',
    'move_row_up' => 'Move row up',
    'move_row_down' => 'Move row down',
    'row_name' => 'Row ":name"',
    'add_section' => 'Add section',
    'delete_section' => 'Delete section',
    'move_section_up' => 'Move section up',
    'move_section_down' => 'Move section down',
    'add_glyph_group' => 'Add glyph group',
    'delete_glyph_group' => 'Delete glyph group',
    'add_entry' => 'Add entry',
    'delete_entry' => 'Delete entry',
    'move_word_class_up' => 'Move word class up',
    'move_word_class_down' => 'Move word class down',
    /**
     * TRANSLATOR NOTE:
     *
     * These are the "move up"/"move down" actions
     * mentioned earlier, that contrast with the
     * "transfer to ..." action.
     */
    'move_glyph_group_up' => 'Move glyph group up',
    'move_glyph_group_down' => 'Move glyph group down',
    'add_glyph' => 'Add glyph',
    'delete_glyph' => 'Delete glyph',
    'move_glyph_earlier' => 'Move glyph earlier',
    'move_glyph_later' => 'Move glyph later',
    'keyboard' => 'Keyboard',
    'preview_of_keyboard' => 'Preview of keyboard',
    'add_keyboard' => 'Add keyboard',
    'delete_keyboard' => 'Delete keyboard',
    'move_keyboard_up' => 'Move keyboard up',
    'move_keyboard_down' => 'Move keyboard down',
    'show_virtual_keyboard' => 'Show virtual keyboard',
    'hide_virtual_keyboard' => 'Hide virtual keyboard',
    /**
     * TRANSLATOR NOTE:
     *
     * These next few messages have "key" as in a
     * button on a computer keyboard.
     */
    'keys' => 'Keys',
    'add_key' => 'Add key',
    'delete_key' => 'Delete key',
    'move_key_earlier' => 'Move key earlier',
    'move_key_later' => 'Move key later',
    'svg_to_glyphs_notice' => 'You don\'t have any neography sections! Add some manually, or extract glyphs from your SVG font.',
    'no_keyboard_notice' => 'You don\'t have any keyboards! Add one manually, or use one of the helper functions below.',
    'extract_from_svg' => 'Extract from SVG',
    'extracting' => 'Extracting ...',
    'import_from_glyphs' => 'Import from glyphs',
    'importing' => 'Importing ...',
    /**
     * TRANSLATOR NOTE:
     *
     * This is used as a tab label on a phonemic keyboard.
     * It refers to sounds/phonemes that are automatically
     * detected by their use in phonemic values in the list
     * of neography glyphs.
     */
    'canonical' => 'Canonical',
    /**
     * TRANSLATOR NOTE:
     *
     * "Width" here refers to the number of buttons/keys in
     * each row of a keybaord.
     */
    'width' => 'Width',
    /**
     * TRANSLATOR NOTE:
     *
     * "Base row" denotes the inflection row which is used
     * as a root or starting point when building different
     * forms of the word.
     *
     * For example if you have inflection tables like this:
     *
     *     |          INFINITIVE          |
     *     +----+------------+------------+
     * --> | a) | infinitive | walk       |
     *
     *     |             FINITE VERB              |
     *     +----+-------------------+-------------+
     *     | b) | 3rd pers. present | walks       |
     *     | c) |        past tense | walked      |
     *
     *     |         PARTICIPLE        |
     *     +----+---------+------------+
     *     | b) | present | walking    |
     *     | c) |    past | walked     |
     *
     * ... then your "base row" is (a), marked above with
     * an arrow, because the other word forms are made from
     * it by adding suffixes like '-s', '-ed', and '-ing'.
     *
     * In the UI, we are calling these suffixes "particles"
     * because they might instead be prefixes, for example.
     */
    'base_row' => 'Base row',
    'base_row_description' => 'Which form of a word should serve as the root, or starting point, for deriving other word forms?',
    'used_in_auto_inflection' => '(Used in auto-inflection)',
    'particle_description' => 'What affix or combining form is added to make the :row?',
    'edit_at_group_level' => 'Edit at group level',
    'morph_template' => 'Morph template',
    'morph_template_description' => 'After all rules below are applied to the base and particle, the results will be used in this substitution template to create the final word form.',
    'morph_template_key' => '\'{B}\' = Base; \'{P}\' = Particle',
    'morph_rules' => 'Rules',
    'applied_to_input' => 'Applied to :input',
    'in_type_representation' => 'In :type representation',
    'base' => 'Base',
    'particle' => 'Particle',
    'transliterated' => 'Transliterated',
    'transliteration' => 'Transliteration',
    'phonemic' => 'Phonemic',
    'native' => 'Native',
    'add_rule' => 'Add rule',
    'delete_rule' => 'Delete rule',
    'move_rule_up' => 'Move rule up',
    'move_rule_down' => 'Move rule down',
    'regex_pattern' => 'RegEx pattern',
    'replace_with' => 'Replace with',
    'preset_notice' => 'You don\'t have any grammar groups! Add some manually, or to save work consider starting with a preset.',
    'preview_of_thing' => 'Preview of :thing',
    'activate' => 'Activate',
    'activate_neography_in_language' => 'Activate :neography in :language',
    'associated_delete' => 'Saving this change will delete associated data.',
    'label' => 'Label',
    'label_long' => 'Label (long)',
    'show_label' => 'Show label',
    'auto_inflection' => 'Auto-inflection',
    'configure_auto_inflection' => 'Configure auto-inflection',
    'auto_inflect' => 'Auto-inflect', // i.e. perform the action of auto-inflection, used on a button label
    'no_base_row_notice' => 'This inflection row has no input base row. These settings will have no effect.',
    /**
     * TRANSLATOR NOTE:
     *
     * "Form" i.e. a word form, or a distinct outcome of
     * word inflection/morphology. For example "walk" vs.
     * "walks" vs. "walked" vs. "walking", all different
     * forms of the same word.
     *
     * "Irregular" as in a word form that doesn't follow
     * the typical rules within a language. (For example,
     * "gave" for past tense instead of "gived" with the
     * typical '-ed' suffix.)
     *
     * These labels will appear when editing a word entry
     * in the dictionary.
     */
    'primary_form' => 'Primary form',
    'word_form' => 'Word form',
    'word_forms' => 'Word forms',
    'add_word_form' => 'Add word form',
    'add_missing_word_forms' => 'Add missing word forms',
    'delete_word_form' => 'Delete word form',
    'irregular' => 'Irregular',
    'inflection_values' => 'Inflection values',
    'add_value' => 'Add value', // That is, a grammatical value
    'remove_value' => 'Remove value',
    'match_to_inflection_row' => 'Match to inflection row',
    'matched_inflection_row' => 'Matched inflection row',
    'add_word_sense' => 'Add word sense', // That is, a sense of a word in the dictionary
    'delete_word_sense' => 'Delete word sense',
    'move_sense_up' => 'Move sense up',
    'move_sense_down' => 'Move sense down',
    'subsenses' => 'Subsenses',
    'add_subsense' => 'Add subsense',
    'delete_subsense' => 'Delete subsense',
    'move_subsense_up' => 'Move subsense up',
    'move_subsense_down' => 'Move subsense down',
    'no_row_matches_alert' => 'This word form has no matching inflection row, and will not be shown.',
    'multiple_row_matches_alert' => 'This word form may show in multiple inflection rows.',
    'multiple_form_matches_alert' => 'This word form\'s inflection values are redundant with another word form.',
    'missing_forms_alert' => 'This word class is missing one or more expected word forms.',
    'non_primary_form_alert' => 'This word class does not need any non-primary word forms. This form will not be shown.',
    'missing_primary_form_alert' => 'This entry needs a primary word form, or it will not be shown.',
    /**
     * TRANSLATOR NOTE:
     *
     * These items will be used together in a popup dialogue,
     * along with "Save" above. "Cancel" means cancel the action
     * of leaving the page, so the user can continue making
     * changes there. "Discard" means get rid of the changes,
     * reverting back to the last saved state.
     */
    'unsaved_alert' => 'You have unsaved changes here!',
    'cancel' => 'Cancel',
    'discard' => 'Discard',
    /**
     * TRANSLATOR NOTE:
     *
     * These items will be used together in a popup dialogue.
     * The colon and number symbol ':#' will be replaced with
     * a number.
     */
    'will_delete_native_spellings' => 'You are about to delete :# native spellings! Are you sure?',
    'no_cancel' => 'No, cancel',
    'yes_delete' => 'Yes, delete',
    'delete_language_confirmation' => 'Delete :name, along with :num entries?',
    'delete_neography_confirmation' => 'Delete :name, along with :num native spellings?',
    'delete_word_class_group_confirmation' => 'About to delete grammar group. Are you sure?',
    'delete_word_class_confirmation' => 'About to delete word class. Are you sure?',
    'delete_feature_confirmation' => 'About to delete an inflection feature. Are you sure?',
    'delete_feature_value_confirmation' => 'About to delete an inflection feature value. Are you sure?',
    'delete_inflection_table_confirmation' => 'About to delete an inflection table. Are you sure?',
    'delete_inflection_row_confirmation' => 'About to delete an inflection row. Are you sure?',
    'delete_rule_confirmation' => 'About to delete a morph rule. Are you sure?',
    'delete_font_file_confirmation' => 'About to delete font file. Are you sure?',
    'delete_section_confirmation' => 'About to delete neography section. Are you sure?',
    'delete_glyph_group_confirmation' => 'About to delete glyph group. Are you sure?',
    'delete_glyph_confirmation' => 'About to delete glyph. Are you sure?',
    'delete_keyboard_confirmation' => 'About to delete keyboard. Are you sure?',
    'delete_key_confirmation' => 'About to delete key. Are you sure?',
    'delete_entry_confirmation' => 'About to delete entry. Are you sure?',
    'delete_word_form_confirmation' => 'About to delete word form. Are you sure?',
    'delete_sense_confirmation' => 'About to delete word sense. Are you sure?',
    'delete_subsense_confirmation' => 'About to delete subsense. Are you sure?',
    /**
     * TRANSLATOR NOTE:
     *
     * This is a series of display options with a description
     * explaining each one.
     */
    'stack' => 'Stack on wide displays',
    'stack_description' => 'Allow table to have other tables beside it on wide displays?',
    'align_on_stack' => 'Align left on stack',
    'align_on_stack_description' => 'By default, a table\'s label is centered. When the table stacks horizontally, should it align left instead?',
    'table_fold' => 'Table label is redundant',
    'table_fold_description' => 'When the table is wrapped (not stacked), should its label hide? Use if redundant with the table above it.',
    'rows_fold' => 'Rows labels are redundant',
    'rows_fold_description' => 'When the table stacks horizontally, should the row labels hide? Use if redundant with the table next to it.',
    'pagination_navigation' => 'Pagination Navigation',
    'first' => 'First',
    'previous' => 'Previous',
    'next' => 'Next',
    'last' => 'Last',
    /**
     * TRANSLATOR NOTE:
     *
     * The word 'transliterated' in this phrase is configurable
     * by the user, so that e.g. in English it can say "Roman."
     *
     * See `Config::get('tollerus.local_transliteration_target')`.
     * This config value will be given as the substitution token,
     * or if missing then the value of localization key
     * `transliterated` (here in this file, above).
     */
    'sort_by_transliterated' => 'Sort by :transliterated',
    /**
     * TRANSLATOR NOTE:
     *
     * Similar to the above, this phrase uses
     * `Config::get('tollerus.local_transliteration_word')` which
     * in English defaults to "romanization". If missing, it will
     * use the localization key `transliteration`.
     */
    'word_form_not_transliterated_alert' => 'This word form needs a :transliteration, or it will not be shown.',
    'sort_by_native' => 'Sort by native',
    'native_spelling' => 'Native spelling',
    /**
     * TRANSLATOR NOTE:
     *
     * These messages are for the public-facing UI which is more
     * layman-oriented than the admin UI. Technical terms from
     * linguistics and grammar are avoided a little more strongly.
     */
    'word_lookup' => 'Word lookup',
    /**
     * TRANSLATOR NOTE:
     *
     * In English, this UI message "Language info" is a noun
     * adjunct that doesn't distinguish between singular vs.
     * plural. Other languages may need to re-phrase this with a
     * preposition, where grammatical number is needed. For
     * example in Spanish, two possible translations are:
     *
     *     - "Información sobre el idioma" (singular)
     *     - "Información sobre los idiomas" (plural)
     *
     * To accommodate that, this translation key uses the Laravel
     * pluralization syntax and will be retrieved via
     * `trans_choice()`, even though it's not needed in English.
     *
     * For more info, see the Laravel documentation:
     * https://laravel.com/docs/12.x/localization#pluralization
     */
    'language_info' => '{1} Language info|[2,*] Language info',

    /**
     * TRANSLATOR NOTE:
     *
     * This website footer uses Markdown syntax for a couple of
     * hyperlinks. It works like this:
     *
     *     "You can [click here](https://example.com)
     *     to do such and such ..."
     *
     * with the clickable text in square backets [], followed by
     * the link in parentheses ().
     *
     * The links are passed as variables:
     *   - github_url
     *   - lgpl_url
     *
     * The final English text that the browser will show says:
     *
     *     "The Tollerus software is copyright © 2026 by
     *     Peter Markley. Licensed via LGPL v2.1"
     */
    'copyright_footer' => 'The [Tollerus software](:github_url) is copyright &copy; :year by Peter Markley.<br>Licensed via [LGPL v2.1](:lgpl_url)',

    /**
     * TRANSLATOR NOTE:
     *
     * These are other messages that also use Markdown ...
     *
     * If an English Wikipedia link is used, and an equivalent
     * article exists in the target language, feel free to use
     * that instead of the substitution token.
     */

    // 'regex_url' = https://en.wikipedia.org/wiki/Regular_expression
    'regex_description' => 'Each rule represents a [Regular Expression](:regex_url) search-and-replace on the selected piece of text. Rules are applied top to bottom.',
    // 'wiki_url' = https://en.wikipedia.org/wiki/Boustrophedon
    'boustrophedon_description' => 'If checked, the writing direction will alternate with each new line. [More info](:wiki_url)',
    // 'guide_url'     = https://inkscape-manuals.readthedocs.io/en/latest/creating-custom-fonts.html
    // 'inkscape_url'  = https://inkscape.org/
    // 'fontforge_url' = https://fontforge.org/
    'inkscape_svg_guide' => 'To make your initial SVG font, follow the instructions [here](:guide_url) using the free software [Inkscape](:inkscape_url). (You can then convert this to other formats using [FontForge](:fontforge_url).)',
    /**
     * TRANSLATOR NOTE:
     *
     * "Conlang" is short for "constructed language," i.e. an
     * artificial or fictional language. "Conscript" follows
     * this pattern but for a script (i.e. an alphabet or
     * writing system); it's another term for "neography."
     */
    // 'pua_url'   = https://en.wikipedia.org/wiki/Private_Use_Areas
    // 'ucsur_url' = https://www.kreativekorp.com/ucsur/
    'ucsur_tip' => 'Consider mapping your conlang glyphs to a [Unicode Private Use Area](:pua_url), perhaps a region not yet claimed in the [Under-ConScript Unicode Registry](:ucsur_url).',
    // 'font_url' = (generated inside Laravel)
    'svg_to_glyphs_notice_no_font' => 'You don\'t have any neography sections! Add some manually, or extract glyphs from your SVG font. (For extraction, you\'ll need to add a font [here](:font_url).)',
    // 'font_url' = (generated inside Laravel)
    'no_keyboard_notice_from_svg' => 'This will make keys out of the codepoints in your [SVG font](:font_url) (recommended).',
    // 'glyphs_url' = (generated inside Laravel)
    'no_keyboard_notice_from_glyphs' => 'This will make keys out of the glyphs in your ["Glyphs" tab](:glyphs_url) (sometimes incomplete).',
    /**
     * TRANSLATOR NOTE:
     *
     * Here we use double-newline characters "\n\n" as a
     * paragraph break. (In PHP this escape sequence only
     * works in a string with double-quotes "".)
     *
     * There's also some bold print, marked with
     * **double-asterisks,** used here for emphasis.
     */
    'glyphs_tab_description' => "These settings define your neography's **canonical glyph order** (or \"alphabetic order\"), and its **public-facing primer material**.\n\nFor example, if your font has typographical variants that aren't meaningful to a reader, or marks that aren't counted alphabetically, they can often be omitted here. In this tab, the goal is to document the neography and help someone learn it.",
    'keyboard_tab_description' => "These settings define an input utility that lets you **type using your own neography.**\n\nIn most cases you'll want one keyboard button for each glyph in your font. (Because what's the point of a glyph that you can't type?)",
    // 'donate_url' = https://paypal.me/petermarkley
    'donate_request' => 'I\'m a one-man dev team. If you like this software, please consider [supporting me](:donate_url)!',
];
