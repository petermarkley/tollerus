# Notes

It's too confusing to have results from all languages mixed together in the WordPicker.

There needs to be a "language" drop menu whenever language isn't dictated by page context, so that the "language lock" in the query code is basically engaged in all scenarios and user can decide which one language they want results for.

When would a user ever want results for multiple languages anyway? They're expected to be using the WordPicker with a specific word in mind, not browsing generically. So this makes much more sense from a UX pov

This solution thankfully is also a fairly trivial code change: just add a "langIsStrict" flag from the page context, and if false then display the drop menu. Language can then be passed always, and either sets the drop menu's initial state or (if no drop menu) acts as a true "lock" (e.g. for AutoInflectionEditor)

# TODO

## Tollerus Features
- [ ] WYSIWYG & sanitizer for 'intro' box on Language, NeographySection, etc. (look for 3rd party package?)
- [ ] Better form ID selector on auto-inflection page
- [ ] Allow selecting an input word for auto-inflection preview
- [ ] (Language emblems/artwork?)
- [ ] Console command to generate grammar preset from current config?
- [ ] Web documentation (driven by `.md` files in Tollerus repo, inside `docs/user`)?
- [ ] Make example host app layouts publishable (like views for Laravel pagination links)?
- [ ] Dockerfile for easier hosting? (Maybe also publishable--or would that be circular...?)
- [ ] Prevent 302 -> 404 sequence in PublicWordLookup when id = form/lexeme in a hidden lang, by hitting 404 earlier

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?

## PublicWordLookup page
- [ ] Offer native keyboard(s)??? (Do not base on primary neography of currently displayed entry!)
