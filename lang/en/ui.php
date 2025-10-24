<?php

return [
  'languages' => 'Languages',
  /**
   * A "neography" is a fictional writing system. ("Neo-"
   * because someone thought of it as a "new" idea.) The
   * more familiar English term is "script," but script
   * can have many more meanings depending on context
   * (like a movie script, or a computer script) and the
   * phrase "writing system" is too clumsy.
   */
  'neographies' => 'Neographies',
  'primary_neography' => 'Primary neography: :name',
  'none' => '(None)',
  /**
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
