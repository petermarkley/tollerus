---
title: Grammar
nav_title: Grammar
order: 20
---
# Grammar

## Concepts

Tollerus is mainly a dictionary app. This means that some aspects of grammar are out of scope.

For example, a dictionary might list the different synthetic forms of a verb, but is not typically concerned with teaching subject-verb agreement rules or how to use a certain verb form in a sentence. For those topics, a learner of the conlang should rely on resources outside the dictionary.

Keep this in mind when building your conlang dictionary in Tollerus.

| Abstracted venn diagram of Tollerus scope |
|---|
| <img src="/docs/img/illus-002-domain_venn_diagram.png" alt="Abstracted venn diagram of Tollerus scope" width="600" height="375" /> |

### Word Classes

In Tollerus, **word classes** (or [parts of speech](https://en.wikipedia.org/wiki/Part_of_speech)) are used as **section headings** when displaying a word entry to the reader of your dictionary.

| Word class headings in the reading interface |
|---|
| <img src="/docs/img/screenshot-004-word_class_headings-callout.png" alt="Screenshot showing word class headings in the dictionary interface" width="640" height="477" /> |

This means that, as you choose what word classes to build for your conlang, you should consider what headings you want to appear in word entries to readers.

### Grammar Groups

Sometimes, word class headings may be used for distinctions that don't matter in your Tollerus grammar configuration. For example, a dictionary might show separate word class headings for "noun" vs. "proper noun" (or as in the screenshot above, "verb" vs. "auxiliary verb"). Although nouns and proper nouns are different in some ways, they are identical in terms of grammar and (more importantly for Tollerus) *morphology.* That is, proper nouns pluralize just like common nouns.

Any time two word class headings share identical inflection rules, you can place them in the same **grammar group.** Inflection rules are defined at the group level, not the word class level.
