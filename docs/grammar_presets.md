Tollerus is a Laravel package that lets a user build, track, and browse a dictionary for their own conlang, or fictional language. As part of the Tollerus software, we offer grammar presets based on a selection of real-life languages.

The existing grammar presets can be found in `resources/data/grammar_presets/`, and their localization files in `lang/[lang_code]/grammar_presets/`. These serve as examples to imitate when creating new presets.

The grammar presets are to demonstrate for Tollerus users different ways that it's possible for a language's grammar to be represented in the software, and maybe if one happens to be similar to the user's intended conlang then it can even be a starting point that they load and modify, to save work. (It's doubtful that many users will want to use a grammar preset unmodified, but they certainly could if for some reason it perfectly matched their conlang.) So although the preset files discuss the grammar of real languages, these grammar concepts/rules are co-opted and transplanted into a conlang context for illustrative and scaffolding purposes. We try to keep them faithful to the grammar they represent, but they are not meant to support any teaching or translating of a real-life language.

Furthermore, the Tollerus system is concerned strictly with lexical and morphological data; inter-word syntax rules (like subject-verb agreement, or Spanish "usted" encoding a different person value lexically vs. grammatically) are far outside that scope. If appropriate, lexical info that doesn't belong in the Tollerus grammar config can still be written in a word entry's definition. For example, in the English grammar preset the "pronoun" word class does not have "gender" configured as an official inflection feature, but a user would be free to write "feminine personal pronoun" in an entry corresponding to "she." Tollerus has no need to understand or constrain the text in a definition--only to provide a means of recording, organizing, and comprehensibly displaying the word's morphology.

Grammar presets are built in two stages:
1. The Markdown file
2. The JSON file + its localization file

The JSON file (with localization) is what's actually used by Tollerus. The Markdown file is basically an intermediate draft to help create and document it.

Some brief, key details about the Tollerus data schema:
- Inflection tables can have row and column labels, but no label at a "table level" that's higher than a column. More on this in the section about the JSON.
- Word classes are treated as mere labels underneath word class groups, or grammar groups. This is because some word classes share identical grammatical behavior but deserve different part-of-speech headings in a dictionary, like "noun" vs. "proper noun".
- Inflection features are attached to word class groups, not to word classes.
- There is no need for every word form to have a value assigned in every inflection axis on its grammar group, nor for any value to be identifying. For example in the English grammar preset, verbs officially inflect on number and person, but only third-person singular is used and only for the present simple form. This means:
  * Past simple, infinitive, and participles all leave the "number" and "person" axes empty.
  * The 3rd-person and singular grammar values are not needed to uniquely identify that word form.

  Both of these facts, while sometimes reflecting author preference for how to interpret or represent the grammar, are nonetheless completely fine and normal for Tollerus. Sometimes they are desirable or even required, if they are faithfully representing a real asymmetry in the language.

# The Markdown

The Markdown file should follow this format:

- At the very top under a heading `## Word class structure`, a 2-tier outline of word classes (or parts of speech) inside word class groups (prefer lowercase, not capitalized). Every 1st-tier item in this outline must be the word `(group)` in parentheses, and under it there should be one or more word classes. This list is meant to represent all the part-of-speech headings that you might see in a dictionary, so if there are further divisions in analytical or academic literature those aren't important.

- Each group in this outline, if it's inflected must enumerate inflection features in a comma-separated list starting with ` - **inflected** by `

- Next below that outline, under a heading `## Inflection features (collected list)`, a 2-tier outline that compiles / repeats the inflection features in the first outline (with dupes merged) and lists the values under each inflection feature.

- Below the top-level heading that begins the document, have one top-level heading for each inflected word class group. No other top-level headings.

- Under each of these, there must be either no other headings whatsoever, or one 2nd-level heading (`## ...`) just called "Inflection Tables" and at most one other explanatory section if the inflections are very complex (see for example "Dictionary Data" in `english.md` or "Listed forms" in `spanish.md`, both under verbs).

- The explanatory section (if present) must be obviously distinguished from the "Inflection Tables" section. Don't use a table structure to convey information if the table structure is similar to the inflection tables. This makes the inflection tables harder to find at a glance when skimming the document.

- The explanatory section (if present) should more likely than not be reserved for the specific task of enumerating synthetic word forms and their inflection features, adhering to the feature scheme described in "Word class structure"/"Inflection features (collected list)" at the top. Other types of explanatory info are permitted, but this option should be considered because it basically serves as an intermediate step in creating the inflection tables (see below).

- In the "Inflection Tables" section, or under the group's top-level heading if no 2nd-level headings exist there, you must provide a complete draft/approximation of what the Tollerus inflection tables for that word class group can look like, using an example word in the actual language. For example we used 'hablar' for Spanish verbs and 'give' for English verbs. This section should include nothing else except maybe some very brief explanation. This section is required for every inflected word class group and must be prominent. And the inflection tables must cover every possible synthetic form of a word in that word class (hence the intermediate step of giving a full list of synthetic forms, if there's a large number of them).

- Most explanatory content should focus on rationales for why inflection features are defined as they are, why certain permutations of them are or are not included in the list of synthetic forms or the inflection tables, why certain terminology and labels are used, etc. Think of this document as a drafting process that moves in roughly a straight line, from nothing, to something just shy of a final product, with the final product being the inflection tables. Nothing out of proximity to that line should be included or discussed much (if at all).

Some of these decisions require careful consideration: to balance simplification against accuracy, or to find the most efficient and expressive analysis of the grammar, or to optimize dictionary ergonomics, or all of these at once.

# The JSON + localization

Things to be aware of:

- When dereferencing the label for an inflection table, underneath the given `i18n_key` the software looks for a key called `_label` (with an underscore) unless overridden with `i18n_label` in the data file.

- When dereferencing the label for an inflection row, underneath the given `i18n_key` the software looks for three keys: `label`, `label_brief`, and `label_long` (no leading underscore).

- Tollerus does not actually allow tables with multiple columns. The JSON must implement multiple columns as separate tables that have identical rows and `stack=true`/`rows_fold=true` flags set. This facilitates responsive web design where on small displays they break into separate tables that wrap vertically with repeating rows.

- Other than the `_label`/`label`/etc. keys mentioned above, layout of the preset lang file's `inflection_tables` object is up to developer discretion. Layouts so far have been chosen to minimize repetition and encourage reuse of translation keys when appropriate.