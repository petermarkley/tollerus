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
* (group) - **inflected** by role, tense, mood, gender, number, person, voice, case
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
* пиша — gerund, present
* писав / писавши — gerund, past
* пишущий — participle, active, present, masculine, singular, nominative
* пишущего — participle, active, present, masculine, singular, genitive
* пишущему — participle, active, present, masculine, singular, dative
* пишущим — participle, active, present, masculine, singular, instrumental
* пишущем — participle, active, present, masculine, singular, prepositional
* пишущая — participle, active, present, feminine, singular, nominative
* пишущей — participle, active, present, feminine, singular, genitive
* пишущей — participle, active, present, feminine, singular, dative
* пишущую — participle, active, present, feminine, singular, accusative
* пишущей — participle, active, present, feminine, singular, instrumental
* пишущей — participle, active, present, feminine, singular, prepositional
* пишущее — participle, active, present, neuter, singular, nominative
* пишущего — participle, active, present, neuter, singular, genitive
* пишущему — participle, active, present, neuter, singular, dative
* пишущим — participle, active, present, neuter, singular, instrumental
* пишущем — participle, active, present, neuter, singular, prepositional
* пишущие — participle, active, present, plural, nominative
* пишущих — participle, active, present, plural, genitive
* пишущим — participle, active, present, plural, dative
* пишущими — participle, active, present, plural, instrumental
* пишущих — participle, active, present, plural, prepositional
* писавший — participle, active, past, masculine, singular, nominative
* писавшего — participle, active, past, masculine, singular, genitive
* писавшему — participle, active, past, masculine, singular, dative
* писавшим — participle, active, past, masculine, singular, instrumental
* писавшем — participle, active, past, masculine, singular, prepositional
* писавшая — participle, active, past, feminine, singular, nominative
* писавшей — participle, active, past, feminine, singular, genitive
* писавшей — participle, active, past, feminine, singular, dative
* писавшую — participle, active, past, feminine, singular, accusative
* писавшей — participle, active, past, feminine, singular, instrumental
* писавшей — participle, active, past, feminine, singular, prepositional
* писавшее — participle, active, past, neuter, singular, nominative
* писавшего — participle, active, past, neuter, singular, genitive
* писавшему — participle, active, past, neuter, singular, dative
* писавшим — participle, active, past, neuter, singular, instrumental
* писавшем — participle, active, past, neuter, singular, prepositional
* писавшие — participle, active, past, plural, nominative
* писавших — participle, active, past, plural, genitive
* писавшим — participle, active, past, plural, dative
* писавшими — participle, active, past, plural, instrumental
* писавших — participle, active, past, plural, prepositional
* писанный — participle, passive, past, masculine, singular, nominative
* писанного — participle, passive, past, masculine, singular, genitive
* писанному — participle, passive, past, masculine, singular, dative
* писанным — participle, passive, past, masculine, singular, instrumental
* писанном — participle, passive, past, masculine, singular, prepositional
* писанная — participle, passive, past, feminine, singular, nominative
* писанной — participle, passive, past, feminine, singular, genitive
* писанной — participle, passive, past, feminine, singular, dative
* писанную — participle, passive, past, feminine, singular, accusative
* писанной — participle, passive, past, feminine, singular, instrumental
* писанной — participle, passive, past, feminine, singular, prepositional
* писанное — participle, passive, past, neuter, singular, nominative
* писанного — participle, passive, past, neuter, singular, genitive
* писанному — participle, passive, past, neuter, singular, dative
* писанным — participle, passive, past, neuter, singular, instrumental
* писанном — participle, passive, past, neuter, singular, prepositional
* писанные — participle, passive, past, plural, nominative
* писанных — participle, passive, past, plural, genitive
* писанным — participle, passive, past, plural, dative
* писанными — participle, passive, past, plural, instrumental
* писанных — participle, passive, past, plural, prepositional

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
* написав / написавши — gerund, past
* написавший — participle, active, past, masculine, singular, nominative
* написавшего — participle, active, past, masculine, singular, genitive
* написавшему — participle, active, past, masculine, singular, dative
* написавшим — participle, active, past, masculine, singular, instrumental
* написавшем — participle, active, past, masculine, singular, prepositional
* написавшая — participle, active, past, feminine, singular, nominative
* написавшей — participle, active, past, feminine, singular, genitive
* написавшей — participle, active, past, feminine, singular, dative
* написавшую — participle, active, past, feminine, singular, accusative
* написавшей — participle, active, past, feminine, singular, instrumental
* написавшей — participle, active, past, feminine, singular, prepositional
* написавшее — participle, active, past, neuter, singular, nominative
* написавшего — participle, active, past, neuter, singular, genitive
* написавшему — participle, active, past, neuter, singular, dative
* написавшим — participle, active, past, neuter, singular, instrumental
* написавшем — participle, active, past, neuter, singular, prepositional
* написавшие — participle, active, past, plural, nominative
* написавших — participle, active, past, plural, genitive
* написавшим — participle, active, past, plural, dative
* написавшими — participle, active, past, plural, instrumental
* написавших — participle, active, past, plural, prepositional
* написанный — participle, passive, past, masculine, singular, nominative
* написанного — participle, passive, past, masculine, singular, genitive
* написанному — participle, passive, past, masculine, singular, dative
* написанным — participle, passive, past, masculine, singular, instrumental
* написанном — participle, passive, past, masculine, singular, prepositional
* написанная — participle, passive, past, feminine, singular, nominative
* написанной — participle, passive, past, feminine, singular, genitive
* написанной — participle, passive, past, feminine, singular, dative
* написанную — participle, passive, past, feminine, singular, accusative
* написанной — participle, passive, past, feminine, singular, instrumental
* написанной — participle, passive, past, feminine, singular, prepositional
* написанное — participle, passive, past, neuter, singular, nominative
* написанного — participle, passive, past, neuter, singular, genitive
* написанному — participle, passive, past, neuter, singular, dative
* написанным — participle, passive, past, neuter, singular, instrumental
* написанном — participle, passive, past, neuter, singular, prepositional
* написанные — participle, passive, past, plural, nominative
* написанных — participle, passive, past, plural, genitive
* написанным — participle, passive, past, plural, dative
* написанными — participle, passive, past, plural, instrumental
* написанных — participle, passive, past, plural, prepositional

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
