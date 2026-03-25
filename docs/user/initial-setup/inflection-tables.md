---
title: Inflection Tables
nav_title: Inflection Tables
order: 30
---
# Inflection Tables

If you already have a clear idea of what your inflection tables should look like, you can skip straight to building them in Tollerus. However if it seems daunting, then follow the steps below.

## Step 1: Write a list of word forms

On a piece of paper or in a scratch document somewhere, choose an example word and write an **exhaustive list of every synthetic form.** If you can assign contrastive grammatical feature values to each one, even better.

> [!Tip]
> "Synthetic form" here means a _unique single-word form._ For example compared to "speak", synthetic forms include "speaks," "spoke," and "speaking;" but "will speak" is a [periphrastic](https://en.wikipedia.org/wiki/Periphrasis) form, not synthetic, because the difference is in a separate word "will."

The example below shows synthetic forms of the Spanish verb *hablar.*

| Example: list of Spanish verb forms |
|---|
| <img src="/docs/img/screenshot-008-synthetic_form_list.png" alt="List of synthetic verb forms in Spanish" width="640" height="527" /> |

## Step 2: Merge duplicates

Try to spot any homonyms and merge them. Wherever the meanings differ, you can omit them or combine them with a slash.

For example in Russian, the genitive participle *пишущего* can be either masculine or neuter. Gender could be omitted from the scheme, except that the feminine *пишущей* is distinct, and the masculine *пишущий* and neuter *пишущее* differ in the nominative case. Therefore the gender of *пишущего* can be written as "masc./neu."

## Step 3: Compact into inflection tables

The next step is to draft a set of inflection tables with maximum compactness/efficiency while retaining linguistic accuracy. Sometimes this takes a few tries before you find the optimal structure.

| Draft of Spanish verb inflection tables |
|---|
| <img src="/docs/img/screenshot-009-inflection_table_plan.png" alt="Draft of inflection tables for Spanish verb" width="640" height="527" /> |

This is how the above draft tables look inside Tollerus:

| Admin config | Display for readers |
|---|---|
| <img src="/docs/img/screenshot-010-inflection_table_config.png" alt="Screenshot of Spanish verb inflection tables inside Tollerus admin config" width="640" height="807" /> | <img src="/docs/img/screenshot-011-inflection_table_display.png" alt="Screenshot of Spanish verb inflection tables in Tollerus reader interface" width="640" height="334" /> |

By default, if there are more than 30 inflection rows on a lexeme, Tollerus will put them all inside a collapsible section so the reader must click to see them. This helps avoid overwhelming the reader.

> [!Tip]
> To adjust this threshold, see the `public_inflections_max_rows` key in your `config/tollerus.php` file.

<img src="/docs/img/screenshot-012-inflection_table_collapsed.png" alt="Screenshot of collapsed inflection tables" width="640" height="272" />

You can also hide tables/columns/rows entirely if you prefer:

![Screenshot of 'visible' toggle](/docs/img/screenshot-013-visible_toggle.png)

However, you're encouraged to at least make the internal configuration exhaustive for synthetic forms. This is for two reasons:
1. It makes it easier for you to capture complete data when adding word entries, and
2. Hidden word forms are still searchable to readers.

So in the above example, even if *habléis* was hidden and inaccessible to a reader, they could still find *hablar* by searching `habléis` as shown.

For more guidance see [Grammar Modeling Princples](/docs/user/grammar-modeling-principles.md).

Next: [Combining Forms & Auto-Inflection](/docs/user/initial-setup/auto-inflection.md)
