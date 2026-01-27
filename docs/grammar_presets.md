# Tollerus Grammar Presets

Tollerus is a Laravel package that lets a user build, track, and browse a dictionary for their own conlang, or fictional language. As part of the Tollerus software, we offer grammar presets based on a selection of real-life languages.

The existing grammar presets can be found in `resources/data/grammar_presets/`, and their localization files in `lang/[lang_code]/grammar_presets/`. These serve as examples to imitate when creating new presets.

The grammar presets are to demonstrate for Tollerus users different ways that it's possible for a language's grammar to be represented in the software, and maybe if one happens to be similar to the user's intended conlang then it can even be a starting point that they load and modify, to save work. (It's doubtful that many users will want to use a grammar preset unmodified, but they certainly could if for some reason it perfectly matched their conlang.) So although the preset files discuss the grammar of real languages, these grammar concepts/rules are co-opted and transplanted into a conlang context for illustrative and scaffolding purposes. We try to keep them faithful to the grammar they represent, but they are not meant to support any teaching or translating of a real-life language, and nowhere does Tollerus use them to make semantic claims.

Tollerus is concerned purely with lexicography, and the grammar is incidental through morphology. Inter-word syntax rules (like subject-verb agreement, or Spanish "usted" encoding a different person value lexically vs. grammatically) are far outside that scope. If appropriate, lexical info that doesn't belong in the Tollerus grammar config can still be written in a word entry's definition. For example, in the English grammar preset the "pronoun" word class does not have "gender" configured as an official inflection feature, but a user would be free to create separate entries for *he* vs. *she* and write "masculine" or "feminine" in the definitions. Tollerus has no need to understand or constrain the text in a definition--only to provide a means of recording, organizing, and comprehensibly displaying the word's morphology.

Grammar presets are built in two stages:
1. The Markdown file
2. The JSON file + its localization file

The JSON file (with localization) is what's actually used by Tollerus, and offered to the user through a GUI. The Markdown file is basically an intermediate draft to help developers create and document the JSON data.

Some brief, key details about the Tollerus data schema:
- Inflection tables can have row and column labels, but no label at a "table level" that's higher than a column. More on this in the section about the JSON.
- Word classes are treated as mere labels underneath word class groups, or grammar groups. This is because some word classes share identical grammatical behavior but deserve different part-of-speech headings in a dictionary, like "noun" vs. "proper noun".
- A word entry can have any number of lexemes or word classes. However each inflected lexeme will display the inflection tables configured on its word class group. There is only one such configuration per group, which applies uniformly to all the lexemes in that group, with no option to treat some assigned lexemes differently.
- Inflection features are attached to word class groups, not to word classes.
- There is no need for every word form to have a value assigned in every inflection axis on its grammar group, nor for any value to be identifying. For example in the English grammar preset, verbs officially inflect on number and person, but only third-person singular is used and only for the present simple form. This means:
  * Past simple, infinitive, and participles all leave the "number" and "person" axes empty.
  * The 3rd-person and singular grammar values are not needed to uniquely identify that word form.

  Both of these facts, while sometimes reflecting author preference for how to interpret or represent the grammar, are nonetheless completely fine and normal for Tollerus. Sometimes they are desirable or even required, if they are faithfully representing a real asymmetry in the language.

## The Markdown

The Markdown file should follow this format:

- At the very top under a heading `## Word class structure`, a 2-tier outline of word classes (or parts of speech) inside word class groups (prefer lowercase, not capitalized). Every 1st-tier item in this outline must be the word `(group)` in parentheses, and under it there should be one or more word classes. This list is meant to represent all the part-of-speech headings that you might see in a dictionary, so if there are further divisions in analytical or academic literature those aren't important.

- Each group in this outline, if it's inflected must enumerate inflection features in a comma-separated list starting with ` - **inflected** by `

- Next below that outline, under a heading `## Inflection features (collected list)`, a 2-tier outline that compiles / repeats the inflection features in the first outline (with dupes merged) and lists the values under each inflection feature.

- Below the top-level heading that begins the document, have one top-level heading for each inflected word class group. No other top-level headings.

- Under each of these, there must be either no other headings whatsoever, or one 2nd-level heading (`## ...`) just called "Inflection Tables" and at most one other explanatory section if the inflections are very complex (see for example "Dictionary Data" in `english.md` or "Listed forms" in `spanish.md`, both under verbs).

- The explanatory section (if present) must be obviously distinguished from the "Inflection Tables" section. Don't use a table structure to convey information if the table structure is similar to the inflection tables. This makes the inflection tables harder to find at a glance when skimming the document.

- The explanatory section (if present) should more likely than not be reserved for the specific task of enumerating synthetic word forms and their inflection features, adhering to the feature scheme described in "Word class structure"/"Inflection features (collected list)" at the top. Other types of explanatory info are permitted, but this option should be considered because it basically serves as an intermediate step in creating the inflection tables (see below).

- In the "Inflection Tables" section, or under the group's top-level heading if no 2nd-level headings exist there, you must provide a complete draft/approximation of what the Tollerus inflection tables for that word class group can look like, using an example word in the actual language. For example we used *hablar* for Spanish verbs and *give* for English verbs. This section should include nothing else except maybe some very brief explanation. This section is required for every inflected word class group and must be prominent. And the inflection tables must cover every possible synthetic form of a word in that word class (hence the intermediate step of giving a full list of synthetic forms, if there's a large number of them). Any synthetic form that's missing from the inflection tables, besides being hidden from the reader, is also prone to be skipped when an author is entering and storing the lexical data.

- Most explanatory content should focus on rationales for why inflection features are defined as they are, why certain permutations of them are or are not included in the list of synthetic forms or the inflection tables, why certain terminology and labels are used, etc. Think of this document as a drafting process that moves in roughly a straight line, from nothing, to something just shy of a final product, with the final product being the inflection tables. Nothing out of proximity to that line should be included or discussed much (if at all).

### Guidance on structural choices

Each grammar preset reflects a chain of modeling decisions, i.e. decisions by the dictionary author on how to use the Tollerus system as a "language" (so to speak) to both organize their lexical data and communicate/present it to readers. Different but equally valid presets could exist for the same language, with pros and cons to each. Recall that a word class group can have only one inflection config, applied uniformly; this constraint drives several of the considerations below.

Here are some things to keep in mind:

#### _Multiple lexemes are permitted_
The ability to assign multiple lexemes to a single word entry can be advantageous and allows some less-than-obvious classifications. For example in English, the word *his* is a pronoun, and due to its modifier usage one might be tempted to place it in a sub-class like "possessive pronoun." However the modifier vs. noun usages are grammatically different and justify separate lexemes: one under "pronoun," and one under "determiner." (This principle becomes more important for word classes that are inflected.)
#### _Word classes are just labels_
To an extent, the word classes underneath a grammar group can be thought of as nothing but labels. There is no obligation that classes under the same group superficially resemble one another, or that similar-sounding classes appear together; only that classes in the same group receive the same inflection configuration. For example in Russian, the group containing the "adjective" word class can be shared with "demonstrative pronoun" since these two word classes have nearly identical inflection profiles. Other pronoun-related word classes can go elsewhere, and there is no issue with that whatsoever as long as it serves the overall goal of simple, expressive dictionary ergonomics that faithfully represent the language.
#### _Morphology over grammar_
In the English and Spanish presets, the participle forms of a verb (*spoken*, *hablado*) are included as inflections in the verb word class structure even though syntactically they behave like adjectives. These are produced predictably for nearly all verbs in the language and would be much more cumbersome to both author and reader if they were given in a separate "adjective" or "verbal adjective" word class. The difference between a periphrastic tense like preterite (*have spoken*) vs. a pure verbal (*the spoken word*) is out of scope for the lexical data that Tollerus tries to capture. Knowing this a dictionary author can choose to present them strictly as verb inflections--labelled appropriately as "participle"--and allow the reader to infer their grammatical usage based on a wider knowledge of the language, thereby achieving a more efficient and expressive lexical representation.
#### _Internal config vs. reader-facing labels_
In Spanish, for correctness we keep tense and aspect as separate features in the grammar configuration and to *hablé* we assign both `tense=past` and `aspect=perfective`. However, in the inflection table this row is labelled with simply "preterite," which follows traditional grammar literature by using a convenient composite term for both features (tense and aspect). The grammar configuration is internal to the dictionary author and can afford a bit more academic rigor, whereas the labels on the inflection tables are reader-facing with strong pressure to simplify and abbreviate. Tollerus embraces and facilitates this separation.
#### _Pliable concepts (within reason)_
In the preset, the Spanish conditional mood is conceptualized as a tense simply because this distortion of concepts is very mild with a big payoff for the internal configuration aesthetics. An author could just as easily leave 'tense' blank for these forms and use a separate mood value (combining "conditional / indicative" with a slash, or just "conditional"), and Tollerus would be just as happy--as long as the filters applied to the relevant table cell correctly and uniquely matched the desired form's feature bundle. It also does not affect what the author chooses for the row and column labels that display to the reader. So it's up to author preference and whatever helps them formalize the grammar most sensibly.
#### _When to inflect vs. duplicate entries_
Recall the example of gender and person being considered lexical in English pronouns, rather than inflectional. This is because for example *he* and *she* are different enough to be considered separate words, and because for impersonal pronouns like *it*/*this*/*that* these features are inapplicable. In any case where a grammatical payload doesn't quite make sense as inflection, the author can consider having multiple word entries and writing it in the definition instead. This basically conceptualizes that grammar feature as "lexical" instead of "inflected." This should not be overused, however; an English reader would be confused to find separate dictionary entries for "walk" and "walked." Some differing forms are strongly considered by speakers of the language as inflections on a single word, and some might result in absurd or unwieldy entry sets. Again, the prevailing principle should be to maximize efficiency, expressiveness, and dictionary ergononics. In other words: what are the costs vs. benefits in using the dictionary if I choose a certain structural alternative?
#### _When to inflect vs. duplicate word classes_
The English preset's "pronoun" group may run into trouble with relative or interrogative pronouns that don't inflect (like *which*). In this case, the author may choose to leave inflection cells blank or populate them with duplicates. Tollerus will accommodate this, but it may seem aesthetically sub-optimal. The author could solve this problem by breaking pronouns into separate word class groups for "personal pronoun" vs. "pronoun", and inflect one but not the other. Or they might use a combined approach: treat the "case" inflection axis as lexical (separate entries for *I* vs. *me*), and put the "personal pronoun" word class in the same group as "noun" which already inflects by number (*him*/*them* in the same entry just like *bird*/*birds*). However, one wouldn't want the number of word classes to explode too much (separate "pronoun" classes for personal, demonstrative, reflexive, relative, interrogative, etc). The author should balance concerns and (again) seek a way to accurately express the grammar that achieves the greatest overall simplicity.
#### _Store everything, show you need_
In Tollerus, the ideal goal for lexical data is that every synthetic form is at least *internally* stored and annotated, even if it's not shown to readers. Some languages may have an excessive number of inflections for each word (e.g. 50-100, or even more) and the author may not want to show them all. The best way to handle this is to create a full set of inflection tables as if to show readers everything, then set `visible=false` on the tables you want to hide. This will encourage capturing complete data when the author creates new entries, which in turn allows a reader to find the correct entry even if they search a hidden form of the word, etc. Speculatively, it could also facilitate future grammar/translation tooling for the conlang.

## The JSON + localization

Things to be aware of:

- When dereferencing the label for an inflection table, underneath the given `i18n_key` the software looks for a key called `_label` (with an underscore) unless overridden with `i18n_label` in the data file.

- When dereferencing the label for an inflection row, underneath the given `i18n_key` the software looks for three keys: `label`, `label_brief`, and `label_long` (no leading underscore).

- Tollerus does not actually allow tables with multiple columns. The JSON must implement multiple columns as separate tables that have identical rows and `stack=true`/`rows_fold=true` flags set. This facilitates responsive web design where on small displays they break into separate tables that wrap vertically with repeating rows.

- Other than the `_label`/`label`/etc. keys mentioned above, layout of the preset lang file's `inflection_tables` object is up to developer discretion. Layouts so far have been chosen to minimize repetition and encourage reuse of translation keys when appropriate.