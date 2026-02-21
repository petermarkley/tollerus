# TODO

## Tollerus Features
- [ ] WYSIWYG & sanitizer for 'intro' box on Language, NeographySection, etc. (look for 3rd party package?)
- [ ] Better form ID selector on auto-inflection page
- [ ] Allow selecting an input word for auto-inflection preview
- [ ] Correctly render `<word>` elements in body text of supporting data objects (lang intro, neography sect intro, entry definitions ...)
- [ ] Implement search by definition on LanguageEditor entries tab, and on PublicWordLookup

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?

## PublicWordLookup page
- [ ] Put inflection tables in collapsible drawer if beyond a threshold defined in `config/tollerus.php`? (Maybe by total `InflectionColumn` count across all the tables on the lexeme...)
- [ ] Offer native keyboard(s)??? (Do not base on primary neography of currently displayed entry!)
