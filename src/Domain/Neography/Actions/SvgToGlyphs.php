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
        $glyphCount = $neography->glyphs()->count();

        // Load SVG
        if (empty($neography->font_svg)) {
            throw new \RuntimeException(__('tollerus::error.font_missing'));
        }
        $svg = simplexml_load_string($neography->font_svg);
        if ($svg === false) {
            throw new \RuntimeException(__('tollerus::error.svg_parse_error'));
        }
        // Sort and separate into contiguous chunks (a gap in the Unicode sequence means a new chunk)
        $glyphChunks = collect($svg->xpath('defs/font/glyph'))
            ->sort(fn ($a, $b) => mb_ord($a['unicode']) <=> mb_ord($b['unicode']))->values()
            ->chunkWhile(fn ($g, $key, $chunk) => mb_ord($g['unicode']) !== mb_ord($chunk->last()['unicode']))
            ->map->values();

        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($neography, $glyphsCollection) {
            foreach ($glyphsCollection as $glyph) {}
            return 1;
        });
    }
}
