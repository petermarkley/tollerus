---
title: SVG Font
nav_title: SVG Font
order: 0
---
# SVG Font

Use [Inkscape](https://inkscape.org/) (or similar software) to create an SVG font (for example using [this guide](https://inkscape-manuals.readthedocs.io/en/latest/creating-custom-fonts.html)). You can then convert this to other formats using [FontForge](https://fontforge.org/).

> [!Tip]
> Consider mapping your conlang glyphs to a [Unicode Private Use Area](https://en.wikipedia.org/wiki/Private_Use_Areas), perhaps a region not yet claimed in the [Under-ConScript Unicode Registry](https://www.kreativekorp.com/ucsur/).

Currently Tollerus expects two formats: SVG and TTF.
- SVG is needed because it's an XML-based format that Tollerus can easily decode and use for automatically populating other data.
- TTF is needed because SVG is not supported by modern browsers, so a web-friendly font is needed for display.

> [!Tip]
> If there seems to be a demand for more font formats, more can be added with minimal development in the code. See [`contributing.md`](https://github.com/petermarkley/tollerus/blob/main/docs/dev/contributing.md) on GitHub.

Once you have a font, from the Tollerus Admin page click "Neographies" and create a new Neography. Fill out the "Info" tab, then drag-and-drop your font file(s) into the "Font" tab.

<img src="/docs/img/screenshot-001-font_drag.jpg" alt="Screenshot of dragging SVG font into Tollerus" width="640" height="403"/>

---

Next: [Glyphs & Keyboards](/docs/user/initial-setup/glyphs-and-keyboards.md)
