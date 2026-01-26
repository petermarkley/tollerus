> [!Warning]
> 
> **This file was created with help from ChatGPT. It has not yet been checked by a person with real knowledge of Russian grammar.**

# Russian grammar preset

## Word class structure

- (nouns)
  - **inflected** by case, number
- (adjectives)
  - **inflected** by case, number, gender
- (verbs)
  - **inflected** by tense, mood, person, number, gender
- (pronouns)
  - **inflected** by case

## Inflection features (collected list)

- case
  - nominative
  - accusative
  - genitive
  - dative
  - instrumental
  - prepositional
- number
  - singular
  - plural
- gender
  - masculine
  - feminine
  - neuter
- person
  - first
  - second
  - third
- tense
  - present
  - past
  - future
- mood
  - indicative
  - imperative

# Nouns

## Inflection Tables

Example noun: **стол** “table” (masculine, inanimate)

| case | singular | plural |
|--|--|--|
| nominative | стол | столы |
| accusative | стол | столы |
| genitive | стола | столов |
| dative | столу | столам |
| instrumental | столом | столами |
| prepositional | столе | столах |

# Adjectives

## Inflection Tables

Example adjective: **большой** “big”

| case | masc. sg. | fem. sg. | neut. sg. | plural |
|--|--|--|--|--|
| nominative | большой | большая | большое | большие |
| accusative | большой | большую | большое | большие |
| genitive | большого | большой | большого | больших |
| dative | большому | большой | большому | большим |
| instrumental | большим | большой | большим | большими |
| prepositional | большом | большой | большом | больших |

# Verbs

## Listed forms

Russian verbs combine tense, mood, person, number, and (in the past tense) gender.
For this preset:
- **Present tense** encodes person × number.
- **Past tense** encodes gender × number (person is not distinguished).
- **Future tense** is shown as synthetic future (where applicable) for parity with other presets.
- Aspect is treated as **lexical**, not inflectional.

## Inflection Tables

Example verb: **говорить** “to speak” (imperfective)

### Present indicative

| person | singular | plural |
|--|--|--|
| 1st | говорю | говорим |
| 2nd | говоришь | говорите |
| 3rd | говорит | говорят |

### Past indicative

| | singular | plural |
|--|--|--|
| masculine | говорил | говорили |
| feminine | говорила | говорили |
| neuter | говорило | говорили |

### Future indicative

| person | singular | plural |
|--|--|--|
| 1st | буду говорить | будем говорить |
| 2nd | будешь говорить | будете говорить |
| 3rd | будет говорить | будут говорить |

### Imperative

| | singular | plural |
|--|--|--|
| command | говори | говорите |

# Pronouns

### Rationale note

Russian pronouns contrast for person, number, and gender, but **not uniformly**:
- Reflexive pronouns lack nominative and number distinctions.
- Personal pronouns show number contrasts, but these behave lexically (я/мы, он/они).

To maintain a single uniform inflection configuration, this preset treats **person, number, and gender as lexical** for pronouns, and inflects pronouns **only by case**. This mirrors the design choice used in the English preset.

## Inflection Tables

Example pronoun: **я** “I”

| case | form |
|--|--|
| nominative | я |
| accusative | меня |
| genitive | меня |
| dative | мне |
| instrumental | мной |
| prepositional | мне |

Example reflexive pronoun: **себя**

| case | form |
|--|--|
| nominative | — |
| accusative | себя |
| genitive | себя |
| dative | себе |
| instrumental | собой |
| prepositional | себе |

