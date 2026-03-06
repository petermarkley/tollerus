# TODO

## Things to Fix
- [ ] Usage notes!
  - [ ] Add `usage` column to `sense`/`subsense` tables
  - [ ] Populate from attr in `FileImportSeeder`
  - [ ] Add public Blade component that can be injected inside `<p>` in `BodyTextRenderer` (can return val from Laravel `view()` be stored/manipulated as HTML string?)--or else (maybe even better) ditch the root `<p>` in the `body` DB column and wrap it in Blade with conditional "usage" `<span>`
- [ ] Normalize WYGIWYG save value's empty `<p>` tags as `<div>` boundaries
- [ ] Add WYSIWYG "inline" mode that disallows multiple `<p>`s and disallows any other block level Tiptap nodes. On save, normalize to expected DB field format for sense/subsense bodies (see above).

## Tollerus Features
- [ ] WYSIWYG & sanitizer for 'intro' box on Language, NeographySection, etc. (look for 3rd party package?)
- [ ] Better form ID selector on auto-inflection page
- [ ] Allow selecting an input word for auto-inflection preview
- [ ] (Language emblems/artwork?)
- [ ] Console command to generate grammar preset from current config?
- [ ] Console command for creating/restoring DB backups? (There may already be tools for this!)
- [ ] Web documentation (driven by `.md` files in Tollerus repo, inside `docs/user`)?
- [ ] Make example host app layouts publishable (like views for Laravel pagination links)?
- [ ] Dockerfile for easier hosting? (Maybe also publishable--or would that be circular...?)

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?

## PublicWordLookup page
- [ ] Offer native keyboard(s)??? (Do not base on primary neography of currently displayed entry!)
