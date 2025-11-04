<?php

return [
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
  'reset' => 'Reset', // i.e. reset the form; revert/discard changes
  'load' => 'Load',
  'loading' => 'Loading ...',
  'info' => 'Info',
  'edit' => 'Edit',
  'edit_thing' => 'Edit :thing',
  'select' => 'Select',
  'delete' => 'Delete',
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
  'features' => 'Features', // i.e. grammatical features or dimensions
  'preset' => 'Preset', // i.e. a predefined configuration for user convenience
  'preset_notice' => 'You don\'t have any grammar groups! Add some manually, or to save work consider starting with a preset.',
  'preview_of_thing' => 'Preview of :thing',
  'activate' => 'Activate',
  'activate_neography_in_language' => 'Activate :neography in :language',
  'associated_delete' => 'Saving this change will delete associated data.',
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
  'delete_word_class_group_confirmation' => 'About to delete grammar group. Are you sure?',
  'delete_word_class_confirmation' => 'About to delete word class. Are you sure?',
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
