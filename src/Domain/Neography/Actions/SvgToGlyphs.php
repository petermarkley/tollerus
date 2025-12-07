<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class SvgToGlyphs
{
    /**
     * This will import glyphs from the stored SVG font into NeographyGlyph objects.
     */
    public function __invoke(Neography $neography): int
    {
        // Check for problems
        if ($neography->glyphs()->count() > 0) {
            throw new \RuntimeException(__('tollerus::error.glyphs_already_exist'));
        }
        if (empty($neography->font_svg)) {
            throw new \RuntimeException(__('tollerus::error.font_missing'));
        }

        // Load SVG
        $svg = simplexml_load_string($neography->font_svg);
        if ($svg === false) {
            throw new \RuntimeException(__('tollerus::error.svg_parse_error'));
        }

        // Sort and separate into contiguous chunks
        /**
         * A gap in the Unicode sequence means a new chunk. For example, if the
         * SVG font contains the following sequence of code points:
         *
         *   [ 0xF2C00, 0xF2C01, 0xF2C02, 0xF2C04, 0xF2C05 ]
         *
         * then $glyphChunks will look like this:
         *
         *   [
         *     [ 0xF2C00, 0xF2C01, 0xF2C02 ],
         *     [ 0xF2C04, 0xF2C05 ]
         *   ]
         */
        $glyphChunks = collect($svg->xpath('defs/font/glyph'))
            ->sort(fn ($a, $b) => mb_ord($a['unicode']) <=> mb_ord($b['unicode']))->values()
            ->chunkWhile(fn ($g, $key, $chunk) => mb_ord($g['unicode']) === mb_ord($chunk->last()['unicode'])+1)
            ->map->values();

        // Get section
        $section = $neography->sections()->first();
        if ($section === null) {
            $section = $neography->sections()->create(['position' => 0]);
        }
        if ($section->glyphGroups()->count() > 0) {
            throw new \RuntimeException(__('tollerus::error.section_has_glyph_groups'));
        }

        // Generate glyphs
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($neography, $section, $glyphChunks) {
            $count = 0;
            foreach ($glyphChunks as $i => $chunk) {
                $group = $section->glyphGroups()->create(['position' => $i]);
                foreach ($chunk as $j => $glyph) {
                    $group->glyphs()->create([
                        'neography_id' => $neography->id,
                        'position' => $j,
                        'render_base' => false,
                        'glyph' => $glyph['unicode'],
                        'note' => $glyph['glyph-name'],
                    ]);
                    $count++;
                }
            }
            return $count;
        });
    }
}
