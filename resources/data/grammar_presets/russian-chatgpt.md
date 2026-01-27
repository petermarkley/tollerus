> [!Warning]
>
> **This file was created with help from ChatGPT. It has not yet been checked by a person with real knowledge of Russian grammar.**

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

| | role | tense | mood | gender | number | person | voice | case |
|--|--|--|--|--|--|--|--|--|
| писать | infinitive | | | | | | | |
| пишу | finite | present | indicative | | singular | first-person
| пишешь | finite | present | indicative | | singular | second-person
| пишет | finite | present | indicative | | singular | third-person
| пишем | finite | present | indicative | | plural | first-person
| пишете | finite | present | indicative | | plural | second-person
| пишут | finite | present | indicative | | plural | third-person
| писал | finite | past | indicative | masculine | singular
| писала | finite | past | indicative | feminine | singular
| писало | finite | past | indicative | neuter | singular
| писали | finite | past | indicative | | plural
| пиши | finite | | imperative | | singular
| пишите | finite | | imperative | | plural
| пиша | gerund | present
| писав / писавши | gerund | past
| пишущий | participle | present | | masculine | singular | | active | nominative
| пишущего | participle | present | | masculine | singular | | active | genitive
| пишущему | participle | present | | masculine | singular | | active | dative
| пишущим | participle | present | | masculine | singular | | active | instrumental
| пишущем | participle | present | | masculine | singular | | active | prepositional
| пишущая | participle | present | | feminine | singular | | active | nominative
| пишущей | participle | present | | feminine | singular | | active | genitive
| пишущей | participle | present | | feminine | singular | | active | dative
| пишущую | participle | present | | feminine | singular | | active | accusative
| пишущей | participle | present | | feminine | singular | | active | instrumental
| пишущей | participle | present | | feminine | singular | | active | prepositional
| пишущее | participle | present | | neuter | singular | | active | nominative
| пишущего | participle | present | | neuter | singular | | active | genitive
| пишущему | participle | present | | neuter | singular | | active | dative
| пишущее | participle | present | | neuter | singular | | active | accusative
| пишущим | participle | present | | neuter | singular | | active | instrumental
| пишущем | participle | present | | neuter | singular | | active | prepositional
| пишущие | participle | present | | | plural | | active | nominative
| пишущих | participle | present | | | plural | | active | genitive
| пишущим | participle | present | | | plural | | active | dative
| пишущими | participle | present | | | plural | | active | instrumental
| пишущих | participle | present | | | plural | | active | prepositional
| писавший | participle | past | | masculine | singular | | active | nominative
| писавшего | participle | past | | masculine | singular | | active | genitive
| писавшему | participle | past | | masculine | singular | | active | dative
| писавшим | participle | past | | masculine | singular | | active | instrumental
| писавшем | participle | past | | masculine | singular | | active | prepositional
| писавшая | participle | past | | feminine | singular | | active | nominative
| писавшей | participle | past | | feminine | singular | | active | genitive
| писавшей | participle | past | | feminine | singular | | active | dative
| писавшую | participle | past | | feminine | singular | | active | accusative
| писавшей | participle | past | | feminine | singular | | active | instrumental
| писавшей | participle | past | | feminine | singular | | active | prepositional
| писавшее | participle | past | | neuter | singular | | active | nominative
| писавшего | participle | past | | neuter | singular | | active | genitive
| писавшему | participle | past | | neuter | singular | | active | dative
| писавшее | participle | past | | neuter | singular | | active | accusative
| писавшим | participle | past | | neuter | singular | | active | instrumental
| писавшем | participle | past | | neuter | singular | | active | prepositional
| писавшие | participle | past | | | plural | | active | nominative
| писавших | participle | past | | | plural | | active | genitive
| писавшим | participle | past | | | plural | | active | dative
| писавшими | participle | past | | | plural | | active | instrumental
| писавших | participle | past | | | plural | | active | prepositional
| писанный | participle | past | | masculine | singular | | passive | nominative
| писанного | participle | past | | masculine | singular | | passive | genitive
| писанному | participle | past | | masculine | singular | | passive | dative
| писанным | participle | past | | masculine | singular | | passive | instrumental
| писанном | participle | past | | masculine | singular | | passive | prepositional
| писанная | participle | past | | feminine | singular | | passive | nominative
| писанной | participle | past | | feminine | singular | | passive | genitive
| писанной | participle | past | | feminine | singular | | passive | dative
| писанную | participle | past | | feminine | singular | | passive | accusative
| писанной | participle | past | | feminine | singular | | passive | instrumental
| писанной | participle | past | | feminine | singular | | passive | prepositional
| писанное | participle | past | | neuter | singular | | passive | nominative
| писанного | participle | past | | neuter | singular | | passive | genitive
| писанному | participle | past | | neuter | singular | | passive | dative
| писанное | participle | past | | neuter | singular | | passive | accusative
| писанным | participle | past | | neuter | singular | | passive | instrumental
| писанном | participle | past | | neuter | singular | | passive | prepositional
| писанные | participle | past | | | plural | | passive | nominative
| писанных | participle | past | | | plural | | passive | genitive
| писанным | participle | past | | | plural | | passive | dative
| писанными | participle | past | | | plural | | passive | instrumental
| писанных | participle | past | | | plural | | passive | prepositional

Example verb: *написать* “to write” (perfective)

| | role | tense | mood | gender | number | person | voice | case |
|--|--|--|--|--|--|--|--|--|
| написать | infinitive
| напишу | finite | future | indicative | | singular | first-person
| напишешь | finite | future | indicative | | singular | second-person
| напишет | finite | future | indicative | | singular | third-person
| напишем | finite | future | indicative | | plural | first-person
| напишете | finite | future | indicative | | plural | second-person
| напишут | finite | future | indicative | | plural | third-person
| написал | finite | past | indicative | masculine | singular
| написала | finite | past | indicative | feminine | singular
| написало | finite | past | indicative | neuter | singular
| написали | finite | past | indicative | | plural
| напиши | finite | | imperative | | singular
| напишите | finite | | imperative | | plural
| написав / написавши | gerund | past
| написавший | participle | past | | masculine | singular | | active | nominative
| написавшего | participle | past | | masculine | singular | | active | genitive
| написавшему | participle | past | | masculine | singular | | active | dative
| написавшим | participle | past | | masculine | singular | | active | instrumental
| написавшем | participle | past | | masculine | singular | | active | prepositional
| написавшая | participle | past | | feminine | singular | | active | nominative
| написавшей | participle | past | | feminine | singular | | active | genitive
| написавшей | participle | past | | feminine | singular | | active | dative
| написавшую | participle | past | | feminine | singular | | active | accusative
| написавшей | participle | past | | feminine | singular | | active | instrumental
| написавшей | participle | past | | feminine | singular | | active | prepositional
| написавшее | participle | past | | neuter | singular | | active | nominative
| написавшего | participle | past | | neuter | singular | | active | genitive
| написавшему | participle | past | | neuter | singular | | active | dative
| написавшим | participle | past | | neuter | singular | | active | instrumental
| написавшем | participle | past | | neuter | singular | | active | prepositional
| написавшие | participle | past | | | plural | | active | nominative
| написавших | participle | past | | | plural | | active | genitive
| написавшим | participle | past | | | plural | | active | dative
| написавшими | participle | past | | | plural | | active | instrumental
| написавших | participle | past | | | plural | | active | prepositional
| написанный | participle | past | | masculine | singular | | passive | nominative
| написанного | participle | past | | masculine | singular | | passive | genitive
| написанному | participle | past | | masculine | singular | | passive | dative
| написанным | participle | past | | masculine | singular | | passive | instrumental
| написанном | participle | past | | masculine | singular | | passive | prepositional
| написанная | participle | past | | feminine | singular | | passive | nominative
| написанной | participle | past | | feminine | singular | | passive | genitive
| написанной | participle | past | | feminine | singular | | passive | dative
| написанную | participle | past | | feminine | singular | | passive | accusative
| написанной | participle | past | | feminine | singular | | passive | instrumental
| написанной | participle | past | | feminine | singular | | passive | prepositional
| написанное | participle | past | | neuter | singular | | passive | nominative
| написанного | participle | past | | neuter | singular | | passive | genitive
| написанному | participle | past | | neuter | singular | | passive | dative
| написанным | participle | past | | neuter | singular | | passive | instrumental
| написанном | participle | past | | neuter | singular | | passive | prepositional
| написанные | participle | past | | | plural | | passive | nominative
| написанных | participle | past | | | plural | | passive | genitive
| написанным | participle | past | | | plural | | passive | dative
| написанными | participle | past | | | plural | | passive | instrumental
| написанных | participle | past | | | plural | | passive | prepositional

## Preliminary draft of a participle inflection table

The singular masculine accusatives and plural accusatives reuse either the nominative or genitive form, and switch between these based on *animacy*. This is not modeled as an inflection axis because the inflection is so minimal and does not produce any unique synthetic forms.

These inflections are usually not enumerated in dictionaries or other literature, because it's basically an adjective inflection *within* a verb inflection. Perhaps this *recursive multiplication* is considered simultaneously too heavy and too trivial/pedantic for most readers.

Regardless, we enumerate them here in keeping with the Tollerus principle of _"store everything, show what you need."_

|                         | sing. masc. participle | sing. fem. participle | sing. neu. participle | pl. participle        |
|-------------------------|------------------------|-----------------------|-----------------------|-----------------------|
| pres. act. nominative   | пишущий                | пишущая               | пишущее               | пишущие               |
| pres. act. genitive     | пишущего               | пишущей               | пишущего              | пишущих               |
| pres. act. dative       | пишущему               | пишущей               | пишущему              | пишущим               |
| pres. act. accusative   | пишущий / пишущего †   | пишущую               | пишущее               | пишущие / пишущих †   |
| pres. act. instrumental | пишущим                | пишущей               | пишущим               | пишущими              |
| pres. act. prepositional| пишущем                | пишущей               | пишущем               | пишущих               |
|  past act. nominative   | писавший               | писавшая              | писавшее              | писавшие              |
|  past act. genitive     | писавшего              | писавшей              | писавшего             | писавших              |
|  past act. dative       | писавшему              | писавшей              | писавшему             | писавшим              |
|  past act. accusative   | писавший / писавшего † | писавшую              | писавшее              | писавшие / писавших † |
|  past act. instrumental | писавшим               | писавшей              | писавшим              | писавшими             |
|  past act. prepositional| писавшем               | писавшей              | писавшем              | писавших              |
| past pass. nominative   | писанный               | писанная              | писанное              | писанные              |
| past pass. genitive     | писанного              | писанной              | писанного             | писанных              |
| past pass. dative       | писанному              | писанной              | писанному             | писанным              |
| past pass. accusative   | писанный / писанного † | писанную              | писанное              | писанные / писанных † |
| past pass. instrumental | писанным               | писанной              | писанным              | писанными             |
| past pass. prepositional| писанном               | писанной              | писанном              | писанных              |

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

|         | gerund          |
|---------|-----------------|
| present | пиша            |
| past    | писав / писавши |

|            | participle |
|------------|------------|
| pres. act. | пишущий    |
| past act.  | писавший   |
| past pass. | писанный   |

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

|         | gerund              |
|---------|---------------------|
| present | --                  |
| past    | написав / написавши |

|            | participle |
|------------|------------|
| pres. act. | --         |
| past act.  | написавший |
| past pass. | написанный |

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
