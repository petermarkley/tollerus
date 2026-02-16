# TODO

## Tollerus Features
- [ ] WYSIWYG & sanitizer for 'intro' box on Language, NeographySection, etc. (look for 3rd party package?)
- [ ] Better form ID selector on auto-inflection page
- [ ] Allow selecting an input word for auto-inflection preview
- [ ] Correctly render `<word>` elements in body text of supporting data objects (lang intro, neography sect intro, entry definitions ...)
- [ ] Research possible CLI tool for SVG->TTF pipeline, use in Console Command without adding as package dep (check for presence on system, fail gracefully?)

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?

## PublicWordLookup page
- [ ] Use responsive `label`/`label_brief`/`label_long` fields on `InflectionRow` in Blade view
- [ ] Put inflection tables in collapsible drawer if beyond a threshold defined in `config/tollerus.php`? (Maybe by total `InflectionColumn` count across all the tables on the lexeme...)
- [ ] Make search work, show clicked result in main view
- [ ] Highlight form on page if there's a document fragment in the URL?
- [ ] Make sure search form changes update URL and push to browser window history
