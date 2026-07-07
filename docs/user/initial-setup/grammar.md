---
title: Grammar
nav_title: Grammar
order: 20
---
# Grammar

You can configure the grammar manually from scratch. However Tollerus offers some **grammar presets** to help jumpstart your process.

<img src="/docs/img/screenshot-005-grammar_preset.jpg" alt="Screenshot of a Tollerus grammar preset" width="640" height="407" />

You will probably want to modify the setup after loading a preset. They are meant to be convenient, not definitive.

> [!Note]
> If you'd like to help me add more grammar presets to Tollerus, this is a **strongly invited** type of contribution. See [`contributing.md`](https://github.com/petermarkley/tollerus/blob/main/docs/dev/contributing.md) on GitHub.

## Topic Scope

Tollerus is mainly a dictionary app. This means that some aspects of grammar are out of scope.

For example, a dictionary might list the different synthetic forms of a verb, but is not typically concerned with teaching subject-verb agreement rules or how to use a certain verb form in a sentence. For those topics, a learner of the conlang should rely on resources outside the dictionary.

Keep this in mind when building your conlang dictionary in Tollerus.

| Abstracted venn diagram of Tollerus scope |
|---|
| <img src="/docs/img/illus-002-domain_venn_diagram.png" alt="Abstracted venn diagram of Tollerus scope" width="600" height="375" /> |

## Word Classes

In Tollerus, **word classes** (or [parts of speech](https://en.wikipedia.org/wiki/Part_of_speech)) are used as **section headings** when displaying a word entry to the reader of your dictionary.

| Word class headings in the reading interface |
|---|
| <img src="/docs/img/screenshot-004-word_class_headings-callout.png" alt="Screenshot showing word class headings in the dictionary interface" width="640" height="477" /> |

This means that, as you choose what word classes to build for your conlang, you should consider what headings you want to appear in word entries to readers.

## Grammar Groups

Sometimes, word class headings may be used for distinctions that don't matter in your Tollerus grammar configuration. For example, a dictionary might show separate word class headings for "noun" vs. "proper noun" (or as in the screenshot above, "verb" vs. "auxiliary verb"). Although nouns and proper nouns are different in some ways, they are identical in terms of grammar and (more importantly for Tollerus) *morphology.* That is, proper nouns pluralize just like common nouns.

Any time two word class headings share identical inflection rules, you can place them in the same **grammar group.** Inflection rules are defined at the group level, not the word class level. They apply uniformly to all word classes in the group.

| "Verb" grammar group with two word classes |
|---|
| <img src="/docs/img/screenshot-006-grammar_group.jpg" alt="Screenshot of a Tollerus grammar group with two word classes inside it" width="640" height="249" /> |

Not all grammar groups are inflected (for example English prepositions). But if your grammar group is inflected, you should add inflection features.

The example below shows English verb inflections. On the left side, each top-level bullet point shows an **inflection feature** with the possible **values** for that feature listed under it.

| outline format | Tollerus interface |
|---|---|
| <ul><li>aspect<ul><li>perfect</li><li>simple</li><li>progressive</li></ul></li><li>number<ul><li>singular</li><li>plural</li></ul></li><li>person<ul><li>first</li><li>second</li><li>third</li></ul></li><li>role<ul><li>infinitive</li><li>finite</li><li>participle</li></ul></li><li>tense<ul><li>past</li><li>present</li></ul></li></ul> | <img src="/docs/img/screenshot-007-verb_features.jpg" alt="Screenshot of English verb features in Tollerus" width="300" height="586" /> |

Not every word form needs to have a value assigned in every axis. For example, in English only finite verbs encode a value in the "tense" axis; this axis is empty for infinitives and participles.

For more guidance, see [Grammar Modeling Principles](/docs/user/grammar-modeling-principles.md).

---

Next: [Inflection Tables](/docs/user/initial-setup/inflection-tables.md)
