---
title: Grammar Modeling Principles
nav_title: Grammar Modeling Principles
order: 100
---
# Grammar Modeling Principles

When configuring your conlang's grammar in Tollerus, there are choices to make based on judgment. Sometimes different but equally valid setups could exist for the same language, with pros and cons to each. This page offers some guiding principles to help you understand and make those choices.

For basic setup instructions, see [Initial Setup > Grammar](/docs/user/initial-setup/grammar.md).

## Background Info

Here are some brief, key details about the Tollerus system:
- Inflection tables can have row and column labels, but no label at a "table level" that's higher than a column.
- Word classes are treated as mere labels underneath word class groups, or grammar groups. This is because some word classes share identical grammatical behavior but deserve different part-of-speech headings in a dictionary, like "noun" vs. "proper noun".
- A word entry can have any number of lexemes or word classes. However each inflected lexeme will display the inflection tables configured on its word class group. There is only one such configuration per group, which applies uniformly to all the lexemes in that group, with no option to treat some assigned lexemes differently.
- Inflection features are attached to word class groups, not to word classes.
- There is no need for every word form to have a value assigned in every inflection axis on its grammar group, nor for any value to be identifying. For example in the English grammar preset, verbs officially inflect on number and person, but only third-person singular is used and only for the present simple form. This means:
  * Past simple, infinitive, and participles all leave the "number" and "person" axes empty.
  * The 3rd-person and singular grammar values are not needed to uniquely identify that word form.

  Both of these facts, while sometimes reflecting author preference for how to interpret or represent the grammar, are nonetheless completely fine and normal for Tollerus. Sometimes they are desirable or even required, if they are faithfully representing a real asymmetry in the language.
- Tollerus offers [grammar presets](/docs/user/initial-setup/grammar.md) not just to save work, but also as _normative/illustrative examples_ for modeling a language's grammar in the system. Hence the principles below often refer to these presets like a standard to learn from and imitate, because that's part of their purpose.

## Principles

In all the principles below, you will notice a theme that the overall goal is _maximum dictionary ergonomics and expressiveness._

In other words:
- What makes the dictionary easiest to use for a reader? (ergnomics)
- And what makes the dictionary concisely and accurately represent the source language? (expressiveness)

### _1. Multiple lexemes are permitted_

The ability to assign multiple lexemes to a single word entry can be advantageous and allows some less-than-obvious classifications. For example in English, the word *his* is a pronoun, and due to its modifier usage one might be tempted to place it in a sub-class like "possessive pronoun." However the modifier vs. noun usages are grammatically different and justify separate lexemes: one under "pronoun," and one under "determiner." (This principle becomes more important for word classes that are inflected.)

### _2. Word classes are just labels_

To an extent, the word classes underneath a grammar group can be thought of as nothing but labels. There is no obligation that classes under the same group superficially resemble one another, or that similar-sounding classes appear together; only that classes in the same group receive the same inflection configuration. For example in Russian, the group containing the "adjective" word class could potentially be shared with "demonstrative pronoun" since these two word classes inflect for the same sets of genders, cases, and numbers. Other pronoun-related word classes could go elsewhere. (Although demonstrative pronouns don't have comparative/superlative forms like adjectives do. The dictionary author would still have to decide how they want to handle that.) There is no inherent issue with grouping unlikely word classes this way, as long as it serves the overall goal of **simple, expressive dictionary ergonomics that faithfully represent the language.**

### _3. Morphology over grammar_

In the English and Spanish presets, the participle forms of a verb (*spoken*, *hablado*) are included as inflections in the verb word class structure even though syntactically they behave like adjectives. These are produced predictably for nearly all verbs in the language and would be much more cumbersome to both author and reader if they were given in a separate "adjective" or "verbal adjective" word class. The difference between a periphrastic tense like preterite (*have spoken*) vs. a pure verbal (*the spoken word*) is out of scope for the lexical data that Tollerus tries to capture. Knowing this a dictionary author can choose to present them strictly as verb inflections--labelled appropriately as "participle"--and allow the reader to infer their grammatical usage based on a wider knowledge of the language, thereby achieving a **more efficient and expressive lexical representation.**

### _4. Internal config vs. reader-facing labels_

In Spanish, for correctness we keep tense and aspect as separate features in the grammar configuration and to *hablé* we assign both `tense=past` and `aspect=perfective`. However, in the inflection table this row is labelled with simply "preterite," which follows traditional grammar literature by using a convenient composite term for both features (tense and aspect). The grammar configuration is internal to the dictionary author and can afford a bit more academic rigor, whereas the labels on the inflection tables are reader-facing with strong pressure to simplify and abbreviate. Tollerus embraces and facilitates this separation.

### _5. Pliable concepts (within reason)_

In the preset, the Spanish conditional mood is conceptualized as a tense simply because this distortion of concepts is very mild with a big payoff for the internal configuration aesthetics. An author could just as easily leave 'tense' blank for these forms and use a separate mood value (combining "conditional / indicative" with a slash, or just "conditional"), and Tollerus would be just as happy--as long as the filters applied to the relevant table cell correctly and uniquely matched the desired form's feature bundle. It also does not affect what the author chooses for the row and column labels that display to the reader. So it's up to author preference and whatever helps them formalize the grammar most sensibly.

### _6. When to inflect vs. duplicate entries_

In any case where a grammatical payload doesn't quite make sense as inflection, the author can consider having multiple word entries and writing it in the definition instead. For example, in the English grammar preset pronouns do not have "gender" configured as an inflection feature, but the author could create separate entries for *he* vs. *she* and write "masculine" or "feminine" in the definitions. This basically conceptualizes pronoun gender as "lexical" instead of "inflected." This makes sense for *he* and *she* because these are different enough to be considered separate words, and because for impersonal pronouns like *it*/*this*/*that* a "gender" feature is inapplicable. Tollerus has no need to understand or constrain the text in a definition--only to provide a means of recording, organizing, and comprehensibly displaying the word's morphology.

This should not be overused, however; an English reader would be confused to find separate dictionary entries for "walk" and "walked." Some differing forms are strongly considered by speakers of the language as inflections on a single word, and some might result in absurd or unwieldy entry sets. Again, the prevailing principle should be to **maximize efficiency, expressiveness, and dictionary ergononics.** In other words: what are the costs vs. benefits in using the dictionary if I choose a certain structural alternative?

### _7. When to inflect vs. duplicate word classes_

The English preset's "pronoun" group may run into trouble with relative or interrogative pronouns that don't inflect (like *which*). In this case, the author may choose to leave inflection cells blank or populate them with duplicates. Tollerus will accommodate this, but it may seem aesthetically sub-optimal.

The author could solve this problem by breaking pronouns into separate word class groups for "personal pronoun" vs. "pronoun", and inflect one but not the other. Or they might use a combined approach: treat the "case" inflection axis as lexical (separate entries for *he* vs. *him*), and put the "personal pronoun" word class in the same group as "noun" which already inflects by number (*him*/*them* in the same entry just like *bird*/*birds*). However, one wouldn't want the number of word classes to explode too much (separate "pronoun" classes for personal, demonstrative, reflexive, relative, interrogative, etc). The author should balance concerns and (again) seek a way to accurately express the grammar that achieves the greatest overall simplicity.

### _8. Store everything, show what you need_

In Tollerus, the ideal goal for lexical data is that every synthetic form is at least *internally* stored and annotated, even if it's not shown to readers. Some languages may have an excessive number of inflections for each word (e.g. 50-100, or even more) and the author may not want to show them all. The best way to handle this is to create a full set of inflection tables as if to show readers everything, then set `visible=false` on the tables you want to hide. This will encourage capturing complete data when the author creates new entries, which in turn allows a reader to find the correct entry even if they search a hidden form of the word, etc. Speculatively, it could also facilitate future grammar/translation tooling for the conlang.

### _9. Condense homography wherever possible_

Sometimes in the inflection table drafting process, after fully listing synthetic forms, the process of de-duplicating homographic forms requires iteration. Because Tollerus focuses so heavily on *morphology over grammar,* ideally reused synthetic forms should not be *avoidably* repeated--but asymmetric homography can make this tricky. For example the Russian verb participle *пишущей* ("writing") is present tense, active voice, singular, and feminine; however its case may be any one of genitive, dative, instrumental, or prepositional. There are different forms for nominative and accusative, and no other genders or numbers re-use a single form across all those cases. So the "case" axis cannot be omitted, but it also offers redundant ways to identify that form.

When configuring the tables for filter matching, if the above situation arises where a given table cell cannot be uniquely identified by any single inflection value that applies to all grammatical uses of the form, then internally one representative value can be used which does **not** match all uses of the form.

For example *пишущей* (mentioned above) can be stored internally as "genitive case," simply ignoring the dative, instrumental, and prepositional uses. This is valid in the Tollerus schema and does not need to affect what is shown to a reader; the row can still be labelled with alternate inflection values not present as a column or row filter (for example "s. fem. gen./dat./instr./prep."). And in extreme cases, even the labels may omit uses pragmatically.
