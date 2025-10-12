# `BuildEnglishGrammar` action

This action will take a `Language` model and add `WordClassGroup`s, `Feature`s/`FeatureValue`s, and `DisplayTable`s roughly matching real-life English grammar.

Used for testing/demo purposes, or as a preset for users to modify.

To run:
```
$language = Language::find(1);
$action = new \PeterMarkley\Tollerus\Domain\Language\Actions\BuildEnglishGrammar;
$action($language)
```

Structure:
* (group)
  * `adjective`
* (group)
  * `adverb`
* (group) - **inflected** by role, tense, aspect, number, person
  * `auxiliary verb`
  * `verb`
* (group)
  * `combining form`
* (group)
  * `contraction`
* (group)
  * `conjunction`
* (group)
  * `determiner`
* (group) - **inflected** by number
  * `noun`
  * `proper noun`
* (group)
  * `preposition`
* (group) - **inflected** by number, case
  * `pronoun`

# English Verb Conjugations

We need a way to represent the conjugations of just the non-auxiliary verbs by themselves.

## Display Tables

|  |  |
|--|--|
| infinitive | GIVE |
| 3rd pers. sing. | GIVES |
| past tense | GAVE |
| pres. participle | GIVING |
| past participle | GIVEN |

In the finite verb inflections, the non-simple aspects are here called "participles" because they are also used that way (_"The gift **given** was thoughtful,"_ _"The man **giving** gifts arrived"_).

* _I give_
* _You give_
* _He gives_  <-- (only inflection on person or number)
* _They give_

## Dictionary Data

This is how the actual morphologies will look in the `forms` table.

* give [infinitive]

### finite verb
* gives [finite, present, simple, 3rd person, singular]
* gave [finite, past, simple]

### participle
* giving [participle, progressive]
* given [participle, perfect]

# English Pronoun Conjugations

In English, personal pronouns are inflected by not just number, but also case: subjective vs. objective. (They also inflect by person and gender, but those inflections don't affect syntax.)

|  | subjective | objective |
|--|--|--|
| singular | I, it, this, that | me, it, this, that |
| plural | we, they, these, those | us, them, these, those |
