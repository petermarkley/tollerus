# Russian Grammar Preset

## Word class structure

* (group) - **inflected** by number, case
  * `noun`
  * `proper noun`
* (group) - **inflected** by gender, number, case, degree
  * `adjective`
  * `demonstrative pronoun`
  * `possessive pronoun`
* (group)
  * `adverb`
* (group) - **inflected** by role, tense, mood, gender, number, person, voice
  * `auxiliary verb`
  * `verb`
* (group)
  * `conjunction`
* (group)
  * `interjection`
* (group)
  * `particle`
* (group)
  * `preposition`
* (group) - **inflected** by case
  * `personal pronoun`
* (group) - **inflected** by case
  * `reflexive pronoun`
* (group)
  * `numeral`
* (group)
  * `combining form`

## Inflection features (collected list)

* gender
  * masculine
  * feminine
  * neuter
* number
  * singular
  * plural
* case
  * nominative
  * genitive
  * dative
  * accusative
  * instrumental
  * prepositional
* degree
  * positive
  * comparative
  * superlative
* tense
  * past
  * present
  * future
* mood
  * indicative
  * imperative
* verb role
  * infinitive
  * finite
  * participle
  * gerund
* voice
  * passive
  * active
* person
  * first
  * second
  * third

# Russian Noun Inflections

Russian nouns inflect for number and case, and also have inherent gender.

## Inflection Tables

Example noun: *дом* “house” (masculine)

|              | singular | plural |
|--------------|----------|--------|
| nominative   | дом      | дома   |
| genitive     | дома     | домов  |
| dative       | дому     | домам  |
| accusative   | дом      | дома   |
| instrumental | домом    | домами |
| prepositional| доме     | домах  |

# Russian Adjective Inflections

Adjectives agree with nouns in gender, number, and case, and (separately) inflect for degree of comparison. Short-form adjectives are not modeled separately here; they are treated as lexical or stylistic variants if needed.

## Inflection Tables

Example adjective: *новый* “new”

|              | sing. masculine | sing. feminine | sing. neuter | plural  |
|--------------|-----------------|----------------|--------------|---------|
| nominative   | новый           | новая          | новое        | новые   |
| genitive     | нового          | новой          | нового       | новых   |
| dative       | новому          | новой          | новому       | новым   |
| accusative   | новый           | новую          | новое        | новые   |
| instrumental | новым           | новой          | новым        | новыми  |
| prepositional| новом           | новой          | новом        | новых   |

|               |            |
|---------------|------------|
| comparative   | новее      |
| superlative   | самый новый|

# Russian Verb Inflections

Russian verbs inflect for role, tense, mood, person, number, and (in past tense) gender. Aspect is encoded lexically as separate words (without a clean predictable pair for each root verb), but usage of non-past tenses is **conditioned on aspect.**

## Listed Forms

Example verb: *писать* “to write” (imperfective)

* писать — infinitive
* пишу — finite, present, indicative, singular, first-person
* пишешь — finite, present, indicative, singular, second-person
* пишет — finite, present, indicative, singular, third-person
* пишем — finite, present, indicative, plural, first-person
* пишете — finite, present, indicative, plural, second-person
* пишут — finite, present, indicative, plural, third-person
* писал — finite, past, indicative, masculine, singular
* писала — finite, past, indicative, feminine, singular
* писало — finite, past, indicative, neuter, singular
* писали — finite, past, indicative, plural
* пиши — imperative, singular
* пишите — imperative, plural
* пишущий — participle, active, present
* писавший — participle, active, past
* писанный — participle, passive, past
* пиша — adverbial participle (gerund), present
* писав / писавши — adverbial participle (gerund), past

Example verb: *написать* “to write” (perfective)

* написать — infinitive
* напишу — finite, future, indicative, singular, first-person
* напишешь — finite, future, indicative, singular, second-person
* напишет — finite, future, indicative, singular, third-person
* напишем — finite, future, indicative, plural, first-person
* напишете — finite, future, indicative, plural, second-person
* напишут — finite, future, indicative, plural, third-person
* написал — finite, past, indicative, masculine, singular
* написала — finite, past, indicative, feminine, singular
* написало — finite, past, indicative, neuter, singular
* написали — finite, past, indicative, plural
* напиши — imperative, singular
* напишите — imperative, plural
* написавший — participle, active, past
* написанный — participle, passive, past
* написав / написавши — adverbial participle (gerund), past

## Inflection Tables

We have to run through this twice, once for each aspect, to show all the table behaviors.

### Example verb: *писать* “to write” (imperfective)

|            | infinitive |
|------------|------------|
| infinitive | писать     |

|               | present | future |
|---------------|---------|--------|
| s. 1st-pers.  | пишу    | --     |
| s. 2nd-pers.  | пишешь  | --     |
| s. 3rd-pers.  | пишет   | --     |
| pl. 1st-pers. | пишем   | --     |
| pl. 2nd-pers. | пишете  | --     |
| pl. 3rd-pers. | пишут   | --     |

|          | past   |
|----------|--------|
| s. masc. | писал  |
| s. fem.  | писала |
| s. neu.  | писало |
| plural   | писали |

|               | indicative |
|---------------|------------|
| sing. imper.  | пиши       |
|   pl. imper.  | пишите     |

|            | participle |
|------------|------------|
| pres. act. | пишущий    |
| past act.  | писавший   |
| past pass. | писанный   |

|         | gerund          |
|---------|-----------------|
| present | пиша            |
| past    | писав / писавши |

### Example verb: *написать* “to write” (perfective)

|            | infinitive |
|------------|------------|
| infinitive | написать   |

|               | present | future   |
|---------------|---------|----------|
| s. 1st-pers.  | --      | напишу   |
| s. 2nd-pers.  | --      | напишешь |
| s. 3rd-pers.  | --      | напишет  |
| pl. 1st-pers. | --      | напишем  |
| pl. 2nd-pers. | --      | напишете |
| pl. 3rd-pers. | --      | напишут  |

|          | past     |
|----------|----------|
| s. masc. | написал  |
| s. fem.  | написала |
| s. neu.  | написало |
| plural   | написали |

|          | imperative |
|----------|------------|
| singular | напиши     |
| plural   | напишите   |

|            | participle |
|------------|------------|
| pres. act. | --         |
| past act.  | написавший |
| past pass. | написанный |

|         | gerund              |
|---------|---------------------|
| present | --                  |
| past    | написав / написавши |

# Russian Personal Pronoun Inflections

Personal pronouns encode person, number, and gender, but only decline for case. (Gender is only relevant for third-person singular forms.)

## Inflection Tables

Example pronoun: *он* “he”

|              |      |
|--------------|------|
| nominative   | он   |
| genitive     | его  |
| dative       | ему  |
| accusative   | его  |
| instrumental | им   |
| prepositional| нём  |

# Russian Reflexive Pronoun Inflections

Reflexive pronouns encode only case, and they cannot be nominative.

## Inflection Tables

Example pronoun: *себя* “myself”

|               |       |
| ------------- | ----- |
| genitive      | себя  |
| dative        | себе  |
| accusative    | себя  |
| instrumental  | собой |
| prepositional | себе  |
