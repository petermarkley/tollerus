<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\Pivots\FormFeatureValue;

class EntryFactory extends Factory
{
    protected $model = Entry::class;

    public function withLexemes(Language $language): static
    {
        // Eager load what we'll need just once before running factory in batch
        $language->load([
            'wordClassGroups.wordClasses',
            'wordClassGroups.features.featureValues',
            'wordClassGroups.inflectionTables.filterValues',
            'wordClassGroups.inflectionTables.rows.filterValues',
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
            // Make multi-lexeme entries only 50% of the time
            $lexemeNum = (((bool)mt_rand(0,1)) ? mt_rand(2, $maxLexemes) : 1);
            $wordClassIndices = array_rand($wordClasses->toArray(), $lexemeNum);

            // Turn the ones we picked into lexemes
            foreach (collect($wordClassIndices) as $position => $index) {
                // Let's get some context
                $wordClass = $wordClasses->get($index);
                /**
                 * Which word_class_group is this class from? We want to use the cached
                 * result from $wordClassGroups, not the upward relation $wordClass->group,
                 * because we are in a batch process now and should limit DB queries.
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
                    ->create(['position'=>$position]);
                // Find base row(s) first
                foreach ($wordClassGroup->inflectionTables as $table) {
                    foreach ($table->rows as $row) {
                        // Skip any non-base / derived rows
                        if ($row->src_base !== null) {
                            continue;
                        }
                        // Create form
                        $baseForm = Form::factory()
                            ->for($lexeme)
                            ->for($language)
                            ->create([
                                'roman' => '',
                                'phonemic' => '',
                            ]);
                        // Add native spellings
                        foreach ($language->neographies as $neography) {
                            NativeSpelling::factory()
                                ->for($baseForm)
                                ->for($neography)
                                ->create(['spelling'=>'']);
                        }
                        // Add grammatical features
                        $filterValues = collect([
                            $table->filterValues,
                            $row->filterValues
                        ])->filter()->collapse();
                        foreach ($filterValues as $featureValue) {
                            (new FormFeatureValue([
                                'form_id' => $baseForm->id,
                                'feature_id' => $featureValue->feature_id,
                                'value_id' => $featureValue->id,
                            ]))->save();
                        }
                        // Mark first one as the entry's primary form
                        if ($entry->primary_form === null) {
                            $entry->primary_form = $baseForm->id;
                            $entry->save();
                        }
                    }
                }
                // Create inflections
                foreach ($wordClassGroup->inflectionTables as $table) {
                    foreach ($table->rows as $row) {
                        // Skip any base rows
                        if ($row->src_base === null) {
                            continue;
                        }
                        // Create form
                        $form = Form::factory()
                            ->for($lexeme)
                            ->for($language)
                            ->create([
                                'roman' => '',
                                'phonemic' => '',
                            ]);
                        // Add native spellings
                        foreach ($language->neographies as $neography) {
                            NativeSpelling::factory()
                                ->for($form)
                                ->for($neography)
                                ->create(['spelling'=>'']);
                        }
                        // Add grammatical features
                        $filterValues = collect([
                            $table->filterValues,
                            $row->filterValues
                        ])->filter()->collapse();
                        foreach ($filterValues as $featureValue) {
                            (new FormFeatureValue([
                                'form_id' => $form->id,
                                'feature_id' => $featureValue->feature_id,
                                'value_id' => $featureValue->id,
                            ]))->save();
                        }
                    }
                }
            }
            // If no inflected lexemes, we still need a primary form
            if ($entry->primary_form === null) {
                $form = Form::factory()
                    ->for($lexeme)
                    ->for($language)
                    ->create([
                        'roman' => '',
                        'phonemic' => '',
                    ]);
                foreach ($language->neographies as $neography) {
                    NativeSpelling::factory()
                        ->for($form)
                        ->for($neography)
                        ->create(['spelling'=>'']);
                }
                $entry->primary_form = $form->id;
                $entry->save();
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