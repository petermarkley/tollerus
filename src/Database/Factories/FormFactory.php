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
            $glyphNum = ($len > 0 ? $len : mt_rand(1,10));
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
                // If this neography is primary, use these glyphs for the form's roman/phonemic
                if ($neography->id === $language->primary_neography) {
                    $form->roman = $selectedGlyphs->pluck('roman')->implode('');
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