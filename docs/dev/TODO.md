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

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?

## PublicWordLookup page
- [ ] Offer native keyboard(s)??? (Do not base on primary neography of currently displayed entry!)
