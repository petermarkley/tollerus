# Contributing

I don't have formal contribution guidelines (yet?) but here are some notes on known needs/opportunities.

## These types of contribution are strongly invited!
- New language localizations
- New [grammar presets](/docs/grammar_presets.md)

> [!Tip]
> You can create a grammar preset by building it in Tollerus and exporting with `php artisan tollerus:export-grammar-preset <myconlang>`.
> 
> Then copy the output files as instructed, commit to a fork, and open a PR!

## These are welcome if there's a demand for them
- Support for more web font formats in [`src/Enums/FontFormat.php`](/src/Enums/FontFormat.php) (will also require DB migration to add columns)

# Getting in touch

If you'd like to discuss a possible code change or contribution, feel free to:
* [Open an issue](https://github.com/petermarkley/tollerus/issues)
* [Fork & pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request-from-a-fork)
* ... or [contact me directly](https://petermarkley.com/contact/)
