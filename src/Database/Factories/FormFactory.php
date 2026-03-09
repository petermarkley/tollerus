<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\NativeSpelling;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function withSpelling(Language $language, int $len = -1): static
    {
        // Eager load the neography glyphs
        $language->loadMissing(['neographies.glyphs']);

        return $this->afterCreating(function (Form $form) use ($language, $len) {
            $neographies = $language->neographies;
            // If not specified, pick a random word length
            if ($len > 0) {
                $glyphNum = $len;
            } else {
                // Pick a random number of senses, with a nonlinear weight
                $min = 1; $max = 10;
                $randFloat = (float)($this->faker->randomFloat(6, 0, 1));
                $glyphNum = (int)round( pow((acos(1-2*$randFloat)/pi()),1.7) *($max-$min)+$min);
            }
            // This language may have multiple neographies. Add a spelling for each one.
            foreach ($neographies as $neography) {
                $allGlyphs = $neography->glyphs;
                $allGlyphsNum = $allGlyphs->count();
                /**
                 * Randomly pick $glyphNum number of glyphs.
                 *
                 * We can't use array_rand() here because we want the possibility
                 * of repeating glyphs. array_rand() is for non-repeating selections.
                 */
                $selectedGlyphs = collect(range(1,$glyphNum))
                    ->map(function ($item) use ($allGlyphs, $allGlyphsNum) {
                        return $allGlyphs->get(mt_rand(0, $allGlyphsNum-1));
                    });
                // Save this as a native spelling on the form
                $native = $selectedGlyphs->pluck('glyph')->implode('');
                NativeSpelling::factory()
                    ->for($form)
                    ->for($neography)
                    ->create(['spelling'=>$native]);
                // If this neography is primary, use these glyphs for the form's transliterated/phonemic
                if ($neography->id === $language->primary_neography) {
                    $form->transliterated = $selectedGlyphs->pluck('transliterated')->implode('');
                    $form->phonemic = $selectedGlyphs->pluck('phonemic')->implode('');
                    $form->save();
                }
            }
        });
    }

    public function definition(): array
    {
        return [];
    }
}