---
title: Getting Started
nav_title: Getting Started
order: 20
---
# Getting Started

If you haven't seen it, consider watching the [10-minute demo video](https://youtu.be/DiMnB7XbTs8).

If by some slim chance you have data in the 2023-era Tollerus XML format, you can import this using the `tollerus:import` Artisan command (see `php artisan help tollerus:import`). Otherwise, see the instructions below.

## Best Setup Sequence

There's a certain sequence that's recommended for initial setup with Tollerus:

1. [SVG Font](/docs/user/initial-setup/svg-font.md)
2. [Glyphs & Keyboards](/docs/user/initial-setup/glyphs-and-keyboards.md)
3. [Grammar](/docs/user/initial-setup/grammar.md)
4. [Inflection Tables](/docs/user/initial-setup/inflection-tables.md)
5. [Auto-Inflection](/docs/user/initial-setup/auto-inflection.md) (if applicable)
6. More Word Entries

### Why this setup sequence?

This sequence saves work and avoids some pain points or incomplete data. For example:
- If you create word entries before setting up a neography, then you can't capture native spellings for those words at creation time. So after you add a neography you'll have to revise all prior entries.
- Even if you create a neography and add a font, if you don't create a keyboard before adding word entries, then you won't benefit from the virtual keyboard when adding the native spellings.
- If you try to create a neography keyboard before adding a font, then you won't benefit from the _"extract from SVG"_ feature that automatically populates the keyboard for you.
- If you do almost anything with your neography before adding a font, then you won't benefit from proper glyph rendering onscreen. (At best, you'll have to identify the glyphs by hexidecimal or some other indirect means; at worst, your browser will show a blank square for each glyph and you'll be editing blind.)
- If you try to add word entries before setting up the grammar, you won't be able to create any lexemes.
- If you try to add word entries before setting up inflection tables, Tollerus can't guide and support adding the proper word forms. (And auto-inflection obviously can't work either.)

## Note on localization

If you are using Tollerus in a non-English locale, there are a few keys you may want to change in your `config/tollerus.php` file.

| Config key | default value | purpose |
|---|---|---|
| `local_transliteration_word` | "romanization" | Word for the **process** of transliteration |
| `local_transliteration_target` | "Roman" | Word for the **product** of transliteration |

Besides translating these, if your locale does not use the Latin alphabet then you may instead want them to say "transliteration"  and "transliterated", or some term specific to your locale's writing system.

| Config key |
|---|
| `particle_word_classes` |

This array of word class names is used for UI hinting when configuring auto-inflection. For more information, see [Auto-Inflection](/docs/user/initial-setup/auto-inflection.md).
