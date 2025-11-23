<?php

return [
  'admin' => 'Admin',
  'language' => 'Language',
  'languages' => 'Languages',
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
   */
  'neography' => 'Neography',
  'neographies' => 'Neographies',
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
  'entries' => 'Entries', // Word entries in a dictionary
  'no_entries' => '(No Entries)',
  'none' => '(none)',
  /**
   * TRANSLATOR NOTE:
   *
   * "Glyph" here is a term borrowed from the field of
   * typography, referring to a shape of type inside a
   * font. For a neography, it means a single letter or
   * symbol in the alphabet (or other writing system).
   */
  'glyphs' => 'Glyphs',
  'no_glyphs' => '(No Glyphs)',
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
  'add_language' => 'Add language',
  'abbreviation' => 'Abbreviation',
  'group_nameless' => '(group)',
  'inflection_table_nameless' => '(inflection table)',
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
  'preset_notice' => 'You don\'t have any grammar groups! Add some manually, or to save work consider starting with a preset.',
  'preview_of_thing' => 'Preview of :thing',
  'activate' => 'Activate',
  'activate_neography_in_language' => 'Activate :neography in :language',
  'associated_delete' => 'Saving this change will delete associated data.',
  'label' => 'Label',
  'label_long' => 'Label (long)',
  'show_label' => 'Show label',
  /**
   * TRANSLATOR NOTE:
   *
   * These items will be used together in a popup dialogue,
   * along with "Save" above. "Cancel" means cancel the action
   * of leaving, so the user can continue making changes.
   * "Discard" means get rid of the changes, reverting back
   * to the last saved state.
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
  'delete_word_class_group_confirmation' => 'About to delete grammar group. Are you sure?',
  'delete_word_class_confirmation' => 'About to delete word class. Are you sure?',
  'delete_feature_confirmation' => 'About to delete an inflection feature. Are you sure?',
  'delete_feature_value_confirmation' => 'About to delete an inflection feature value. Are you sure?',
  'delete_inflection_table_confirmation' => 'About to delete an inflection table. Are you sure?',
  'delete_inflection_row_confirmation' => 'About to delete an inflection row. Are you sure?',
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
   *     "The Tollerus software is copyright © 2025 by
   *     Peter Markley. Licensed via LGPL v2.1"
   */
  'copyright_footer' => 'The [Tollerus software](:github_url) is copyright &copy; :year by Peter Markley.<br>Licensed via [LGPL v2.1](:lgpl_url)',
];
