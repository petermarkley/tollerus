---
title: Auto-Inflection
nav_title: Auto-Inflection
order: 40
---
# Auto-Inflection

If your conlang is inflected, you very likely have some type of particles or combining forms (like prefixes or suffixes) that are used (at least _sometimes_) to produce inflected word forms by a predictable pattern. Tollerus has a feature that can help you automate this process, called **auto-inflection.**

This feature is completely optional, so if you don't think you will benefit then you can certainly skip to [writing word entries](/docs/user/initial-setup/word-entries.md). You can also come back and set it up any time later. There is _no accumulating consequence_ to going without it.

## Set a base row

On each inflected grammar group, if you click "Edit inflection tables" then at the top of the page you will see a drop menu called "Base row."

This decides which word form is the root, or starting point, for deriving other word forms. Make sure it is set correctly.

<img src="/docs/img/screenshot-014-base_row.jpg" alt="Screenshot of base row option" width="300" height="188" />

## Add combining-form entries

Next, add an entry for each combining form that you will use in auto-inflection. For example, if your "conlang" was English, you might add an entry for *-ing* that will produce the present participle of verbs.

* From the "Edit language" page, in the "Entries" tab, click "Add entry".
* Inside the new entry, click "Add word class" and pick the appropriate type of combining form.
* Fill out the transliterated, phonemic, and native spellings.
* Add a sense under "Definition" and describe the morphological purpose of your combining form. For example, "forming the present participle of verbs."

You should now have something like this:

| *"-ing"* in admin config | *-ing* as displayed for readers |
|---|---|
| <img src="/docs/img/screenshot-015-combining_form_ing.jpg" alt="Screenshot of &quot;-ing&quot; lexeme in configuration" width="640" height="485" /> | <img src="/docs/img/screenshot-016-combining_form_ing.png" alt="Screenshot of &quot;-ing&quot; entry display" width="640" height="314" /> |

Repeat this process for each combining form, suffix, prefix, etc. that you will need.

## Configure auto-inflection

Go to your Language > Grammar tab > Inflection tables, and edit a specific table. It should be one that contains an inflection row that's not your base row (because auto-inflection can't be configured on the base row itself). Find one of these non-base rows and click "Configure auto-inflection."

This brings you to the **auto-inflection editor.**

| The auto-inflection editor |
|---|
| <img src="/docs/img/screenshot-017-auto_inflection_editor.jpg" alt="Screenshot of the auto-inflection editor page" width="640" height="436" /> |

The base row is shown in the top left.

### Select particle

Use the word picker under "Particle" (in the top right) to select the combining form that you created above. Make sure to select the one that matches the inflection row whose auto-inflection you are editing.

<img src="/docs/img/screenshot-018-auto_inflection_particle.jpg" alt="Screenshot of picking the particle form for auto-inflection" width="640" height="423" />

> [!Tip]
> If you are using Tollerus in a non-English locale, or if your conlang has an unusual word class name for this particle, the word picker may be a little less intelligent about helping you find it.
> 
> You can fix this by adjusting the `particle_word_classes` key in your `config/tollerus.php` file to include the proper word class name.

### Morph template

The base row and the particle will be run through the morph rules you define (see below), and then combined according to the morph template on this page. It uses substitution tokens:
* `{B}` = base
* `{P}` = particle

You will want to change this for example if you are using a prefix instead of a suffix, to swap the order of base vs. particle.

### Auto-inflection preview

Here, you can set an example word to help you see the result of auto-inflection while you make edits.

<img src="/docs/img/screenshot-019-auto_inflection_preview.jpg" alt="Screenshot of auto-inflection preview" width="640" height="400" />

### Morph rules

At the bottom of the page, you'll find a "Rules" section with a series of tabs.

Normally, each word form contains at least 3 representations:
* transliterated,
* phonemic, and
* one native spelling for each neography that's enabled on the conlang

There is a space here to define morph rules on each individual representation of _both the base row and the particle._ The morph rules use [Regular Expressions](https://en.wikipedia.org/wiki/Regular_expression), which are highly technical but offer a lot of power and flexibility for programming your conlang's morphology.

Teaching Regular Expressions is out of scope for this documentation, but here are a few extremely basic ones that you might find useful:

| RegEx pattern | "Replace with" string | Purpose | Example use case |
|---|---|---|---|
| `^.` | *none* | Removes one character from the **beginning** of the source string | Hyphen at beginning of suffix |
| `.$` | *none* | Removes one character from the **end** of the source string | Hyphen at end of prefix |
| `(?<=.).$` | *none* | Removes one character from the end **only if** the source string is longer than 1 character | Avoids zero-length output |

You can also directly insert phonemic or native characters into the RegEx pattern if you want the match to depend on specific characters. This system should be theoretically capable of representing a large majority of conceivable morphology rules in a conlang. (Some notable exceptions are [infixes](https://en.wikipedia.org/wiki/Infix) and [circumfixes](https://en.wikipedia.org/wiki/Circumfix).)

### Repeat for each inflection row

Once you have configured auto-inflection for an inflection row, go to the next and repeat the process for any remaining non-base rows.

---

Next: [Word Entries](/docs/user/initial-setup/word-entries.md)
