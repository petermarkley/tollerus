---
title: Getting Started
nav_title: Getting Started
order: 10
---
# Getting Started

If you haven't seen it, consider watching the [10-minute demo video](https://youtu.be/DiMnB7XbTs8).

If by some slim chance you have data in the 2023-era Tollerus XML format, you can import this using the `tollerus:import` Artisan command (see `php artisan help tollerus:import`). Otherwise, see the instructions below.

## Best Setup Sequence

There's a certain sequence that's recommended for initial setup with Tollerus:

1. SVG Font (with external software)
2. Glyphs & Keyboards
3. Grammar
4. Inflection Tables
5. Combining Forms & Auto-Inflection (if applicable)
6. More Word Entries

### Why this setup sequence?

This sequence saves work and avoids some pain points or incomplete data. For example:
- If you create word entries before setting up a neography, then you can't capture native spellings for those words at creation time. So after you add a neography you'll have to revise all prior entries.
- Even if you create a neography and add a font, if you don't create a keyboard before adding word entries, then you won't benefit from the virtual keyboard when adding the native spellings.
- If you try to create a neography keyboard before adding a font, then you won't benefit from the _"extract from SVG"_ feature that automatically populates the keyboard for you.
- If you do almost anything with your neography before adding a font, then you won't benefit from proper glyph rendering onscreen. (At best, you'll have to identify the glyphs by hexidecimal or some other indirect means; at worst, your browser will show a blank square for each glyph and you'll be editing blind.)
- If you try to add word entries before setting up the grammar, you won't be able to create any lexemes.
- If you try to add word entries before setting up inflection tables, Tollerus can't guide and support adding the proper word forms. (And auto-inflection obviously can't work either.)

### Detailed Process

#### 1. SVG Font

Use [Inkscape](https://inkscape.org/) (or similar software) to create an SVG font (for example using [this guide](https://inkscape-manuals.readthedocs.io/en/latest/creating-custom-fonts.html)). You can then convert this to other formats using [FontForge](https://fontforge.org/).

Consider mapping your conlang glyphs to a [Unicode Private Use Area](https://en.wikipedia.org/wiki/Private_Use_Areas), perhaps a region not yet claimed in the [Under-ConScript Unicode Registry](https://www.kreativekorp.com/ucsur/).

Currently Tollerus expects two formats: SVG and TTF.
- SVG is needed because it's an XML-based format that Tollerus can easily decode and use for automatically populating other data.
- TTF is needed because SVG is not supported by modern browsers, so a web-friendly font is needed for display.

Once you have a font, from the Tollerus Admin page click "Neographies" and create a new Neography. Fill out the "Info" tab, then drag-and-drop your font file(s) into the "Font" tab.

<img src="/docs/img/screenshot-001-font_drag.jpg" alt="Screenshot of dragging SVG font into Tollerus" width="640" height="403"/>

#### 2. Glyphs and Keyboards

The Glyphs and Keyboards tabs have distinct purposes.

- The Glyphs tab defines your neography's **canonical glyph order** (or \"alphabetic order\"), and its **public-facing primer material.** The goal is to document the neography and help someone learn it.

- The Keyboards tab defines an input utility that lets you **type using your own neography.** This is not currently presented to readers.

This means that you probably want the keyboard info to be more exhaustive than the glyph info. For example, if your font has typographical variants that aren't meaningful to a reader, or marks that aren't counted alphabetically, they can often be omitted from the Glyphs tab. Conversely, in most cases you'll want one keyboard button for each glyph in your font. (Because what's the point of a glyph that you can't type?)

You can add the data manually, but Tollerus offers some functions to automatically populate it:
- If you have an SVG font, there's an "Extract from SVG" button on each tab.
- If you've populated the glyph data, there's an "Import from glyphs" button on the Keyboards tab.

<img src="/docs/img/illus-001-neography_auto_flow.png" alt="Illustration of Neography automation options" width="600" height="450" />

Obviously you would not want (or need) to perform both functions to populate Keyboards. SVG extraction is preferred if available. Importing from Glyphs is provided mainly for cases where (for some reason) you can't add an SVG version of your font.

> [!Important]
> After any automatic populate function, you should check the results to see if they need cleanup or editing. It's meant to be convenient, not definitive.

| Post-extraction glyph cleanup |
|---|
| <img src="screenshot-003-glyph_cleanup-callout.png" alt="Screenshot showing post-extraction glyph cleanup" width="640" height="287" /> |

#### 3. Grammar

#### 4. Inflection Tables

#### 5. Combining Forms & Auto-Inflections

#### 6. More Word Entries
