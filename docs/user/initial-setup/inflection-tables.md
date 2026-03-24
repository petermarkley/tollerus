---
title: Inflection Tables
nav_title: Inflection Tables
order: 30
---
# Inflection Tables

If your conlang's word class inflections are at all complex, the recommended workflow starts with choosing an example word and writing an **exhaustive list of every synthetic form.** If you can assign feature values to each one, even better.

This serves as an intermediate stage in the drafting process for inflection tables. Once you have this, you'll be able to more easily see any homographs that should (ideally) be combined, and eventually reshape it into an optimally compacted set of inflection tables for your example word.

| Example: list of Spanish verb forms |
|---|
| <img src="/docs/img/screenshot-008-synthetic_form_list.png" alt="List of synthetic verb forms in Spanish" width="640" height="527" /> |

| Draft of Spanish verb inflection tables |
|---|
| <img src="/docs/img/screenshot-009-inflection_table_plan.png" alt="Draft of inflection tables for Spanish verb" width="640" height="527" /> |

By default, if there are more than 30 inflection rows on a lexeme, Tollerus will put them all inside a collapsible section so the reader must click to see them. This helps avoid overwhelming them. (To adjust this threshold, see the `public_inflections_max_rows` key in your `config/tollerus.php` file.)

You can also hide tables/columns/rows entirely if you prefer, but you're encouraged to at least make the internal configuration exhaustive. This is for two reasons:
1. It makes it easier for you to capture complete data when adding word entries, and
2. Hidden word forms are still searchable to readers. (So in the above example, even if *hablaríamos* was hidden and inaccessible to a reader, they could still find *hablar* by searching `hablaríamos`.)

For more guidance see [Grammar Modeling Princples](/docs/user/grammar-modeling-principles.md).
