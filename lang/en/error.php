<?php

return [
    'folder_conflict' => 'Unable to create folder because a file name conflicts.',
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
    'font_missing' => 'Neography has no font information.',
    'file_conflict' => 'Unable to create file because it already exists.',
    'file_missing' => 'Unable to delete file because it\'s missing or is not a file.',
    'file_path_missing' => 'No public file path found.',
    'svg_parse_error' => 'Error while parsing SVG font file.',
    'keyboards_already_exists' => 'Keyboards already exist.',
    /**
     * TRANSLATOR NOTE:
     *
     * "Glyph" here is a term borrowed from the field of
     * typography, referring to a shape of type inside a
     * font. For a neography, it means a single letter or
     * symbol in the alphabet (or other writing system).
     */
    'glyphs_already_exist' => 'Glyphs already exist.',
    'glyphs_missing' => 'Neography has no glyphs.',
    'section_has_glyph_groups' => 'Neography section already has glyph groups.',
    /**
    * TRANSLATOR NOTE:
    *
    * "Preset", meaning a predefined configuration for user convenience.
    *
    * A "word class" here means a part of speech like "noun" or "verb."
    * A word class group is a group of these that share grammar rules
    * like "noun" and "proper noun."
    */
    'invalid_preset' => 'Invalid preset',
    'invalid_word_class' => 'Invalid word class ID',
    'invalid_word_class_group' => 'Invalid word class group ID',
    'invalid_feature' => 'Invalid feature ID',
    'invalid_feature_value' => 'Invalid feature value ID',
    'duplicate_of_unique_per_group' => 'This must be unique per group.',
    'duplicate_of_unique_per_section' => 'This must be unique per section.',
    'invalid_inflection_table' => 'Invalid inflection table ID',
    'invalid_inflection_table_row' => 'Invalid inflection row ID',
    'duplicate_of_row' => 'This must be unique per inflection table.',
    'duplicate_of_glyph' => 'This must be unique per glyph group.',
    'invalid_prop_name' => 'Invalid property name',
    'invalid_neography_section' => 'Invalid neography section ID',
    'invalid_glyph_group' => 'Invalid glyph group ID',
    'invalid_glyph' => 'Invalid glyph ID',
    'invalid_keyboard' => 'Invalid keyboard ID',
    'invalid_key' => 'Invalid key ID', // "Key" as in a button on a computer keyboard
    'invalid_lexeme' => 'Invalid lexeme ID',
    'invalid_form' => 'Invalid word form ID', // "Form" as in a word form like "walking" vs. "walked"
    'number_out_of_range' => 'Number is out of range.',
    'duplicate_of_key' => 'This must be unique per keyboard.',
    'dupliacte_of_unique_per_entry' => 'This must be unique per entry.',
    'max_attempts_adding_unique_name' => 'Reached max attempts while trying to create unique object name.',
    /**
     * TRANSLATOR NOTE:
     *
     * "Source particle" here is an abstract term for basically a
     * suffix or prefix, etc., used for making new word forms. For
     * example the "-ing" suffix in "walking."
     *
     * This message is used in the context of configuring the auto-
     * inflection feature.
     */
    'invalid_src_particle' => 'Invalid source particle',
    'invalid_morph_rule' => 'Invalid morph rule',
    'asset_invalid' => 'Something is wrong with the public asset!',
    'invalid_file_mime_type' => 'Unknown file MIME type :mime_type',
    'file_too_big' => 'File size :size exceeds the configured maximum.',
];
