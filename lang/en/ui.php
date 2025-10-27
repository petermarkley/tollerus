<?php

return [
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
  'neographies' => 'Neographies',
  'primary_neography' => 'Primary neography: :name',
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
