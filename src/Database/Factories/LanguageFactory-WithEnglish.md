# English Verb Conjugations
**Used as default grammar in `tollerus:populate` factory**

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

