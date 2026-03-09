# TODO

## Tollerus Features
- [ ] (Language emblems/artwork?)
- [ ] Console command to generate grammar preset from current config?
- [ ] Console command for creating/restoring DB backups? (There may already be tools for this!)
- [ ] Web documentation (driven by `.md` files in Tollerus repo, inside `docs/user`)?
- [ ] Dockerfile for easier hosting? (Maybe also publishable--or would that be circular...?)

## Code health
- [ ] Audit for uses of `->sortBy()->map()->toArray()` that need to be `->sortBy()->map()->values()->toArray()`?
- [ ] Why does filename conflict cause server error when uploading TTF?
- [ ] On EntryEditor page, if you edit multiple WYSIWYGs at once and save one, you lose changes on the other(s). Warn user with modal? Or put WYSIWYG behind "edit" button that locks others until "save"?

## PublicWordLookup page
- [ ] Offer native keyboard(s)??? (Do not base on primary neography of currently displayed entry!)
