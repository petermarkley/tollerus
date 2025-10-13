<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;

class EntryFactory extends Factory
{
    protected $model = Entry::class;

    public function withLexemes(Language $language): static
    {
        // Eager load what we'll need just once before running factory in batch
        $language->load([
            'wordClassGroups.wordClasses',
            'wordClassGroups.features.featureValues',
            'wordClassGroups.inflectionTables.filters',
            'wordClassGroups.inflectionTables.rows.filters',
            'neographies.glyphs',
        ]);

        // This will be very helpful to have too
        $primaryNeography = $language->neographies
            ->firstWhere('id', (int)$language->primary_neography);
        if (!is_null($primaryNeography)) {
            $language->setRelation('primaryNeography', $primaryNeography);
        }

        // Capture the cached results in factory closure
        return $this->afterCreating(function (Entry $entry) use ($language) {
            $wordClassGroups = $language->wordClassGroups;

            // Pick a random set of word classes
            $wordClasses = $wordClassGroups
                ->map(fn($t)=>$t->wordClasses)
                ->flatten(1)
                ->keyBy('name');
            $maxLexemes = min(4, $wordClasses->count());
            $lexemeNum = mt_rand(1, $maxLexemes);
            $wordClassIndices = array_rand($wordClasses->toArray(), $lexemeNum);

            // Turn the ones we picked into lexemes
            foreach ($wordClassIndices as $position => $index) {
                // Let's get some context
                $wordClass = $wordClasses->get($index);
                /**
                 * Which word_class_group is this class from? We want to used the cached
                 * result from $wordClassGroups, not the upward relation $wordClass->group,
                 * because we are in a batch process now and must not touch the database.
                 */
                $wordClassGroup = $wordClassGroups->filter(function ($group) use ($wordClass) {
                    // Filter by whether the given group contains the given word class
                    return $group->wordClasses->contains(function ($class) use ($wordClass) {
                        return $class === $wordClass;
                    });
                })->first();
                // Create the lexeme
                $lexeme = Lexeme::factory()
                    ->for($entry)
                    ->for($language)
                    ->for($wordClass)
                    ->withForms(
                        language: $language,
                        wordClassGroup: $wordClassGroup
                    )->create(['position'=>$position]);
            }
        });
    }

    public function definition(): array
    {
        if ((bool)mt_rand(0,2)) {
            $etym = null;
        } else {
            $etym = $this->faker->sentence();
        }
        return [
            'etym' => $etym,
        ];
    }
}