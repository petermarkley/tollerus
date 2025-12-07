<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class GlyphsToKeyboard
{
    /**
     * This will convert your NeographyGlyphs into NeographyInputKey objects.
     */
    public function __invoke(Neography $neography): int
    {
        // Check for problems
        if ($neography->keyboards()->count() > 0) {
            throw new \RuntimeException(__('tollerus::error.keyboards_already_exists'));
        }
        if ($neography->glyphs()->count() == 0) {
            throw new \RuntimeException(__('tollerus::error.glyphs_missing'));
        }

        // Load glyphs
        $neography->loadMissing(['sections.glyphGroups.glyphs']);
        $glyphGroups = $neography->sections
            ->sortBy('position')
            ->flatMap(fn ($s) => $s->glyphGroups->sortBy('position'));

        // Generate keyboard data
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($neography, $glyphGroups) {
            $count = 0;
            foreach ($glyphGroups as $i => $group) {
                $keyboard = $neography->keyboards()->create([
                    'position' => $i,
                    'width' => 10,
                ]);
                foreach ($group->glyphs as $glyph) {
                    // Choose an appropriate label for this key
                    if (empty($glyph->transliterated)) {
                        if (empty($glyph->pronunciation_transliterated)) {
                            $label = $glyph->note;
                        } else {
                            $label = $glyph->pronunciation_transliterated;
                        }
                    } else {
                        $label = $glyph->transliterated;
                    }
                    // Create key
                    $keyboard->inputKeys()->create([
                        'label' => $label,
                        'glyph' => $glyph->glyph,
                        'position' => $glyph->position,
                        'render_base' => $glyph->render_base,
                    ]);
                    $count++;
                }
            }
            return $count;
        });
    }
}
