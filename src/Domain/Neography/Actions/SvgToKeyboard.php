<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class SvgToKeyboard
{
    /**
     * These words, as well as the neography's name, will be ignored when assigning
     * key labels from the font glyph names.
     */
    const IGNORE = ['letter', 'symbol', 'mark', 'character'];

    /**
     * This will import glyphs from the stored SVG font into NeographyInputKey objects.
     *
     * Note: The SVG parsing/chunking code here is repeated once in SvgToGlyphs. If
     * there's any further refactoring or copying than that, consider moving this code
     * into `src/Domain/Neography/Parsing/SvgGlyphExtractor.php` or similar.
     */
    public function __invoke(Neography $neography): int
    {
        // Check for problems
        if ($neography->keyboards()->count() > 0) {
            throw new \RuntimeException(__('tollerus::error.keyboards_already_exists'));
        }
        if (empty($neography->font_svg)) {
            throw new \RuntimeException(__('tollerus::error.font_missing'));
        }

        // Load SVG
        $svg = simplexml_load_string($neography->font_svg);
        if ($svg === false) {
            throw new \RuntimeException(__('tollerus::error.svg_parse_error'));
        }
        $namespaces = $svg->getDocNamespaces();
        $nsPre = '';
        if (isset($namespaces[''])) {
            $nsPre = 'svg';
            $svg->registerXPathNamespace($nsPre, $namespaces['']);
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
        $glyphChunks = collect($svg->xpath(($nsPre?$nsPre.':':'').'defs/'.($nsPre?$nsPre.':':'').'font/'.($nsPre?$nsPre.':':'').'glyph'))
            ->sort(fn ($a, $b) => mb_ord($a['unicode']) <=> mb_ord($b['unicode']))->values()
            ->chunkWhile(fn ($g, $key, $chunk) => mb_ord($g['unicode']) === mb_ord($chunk->last()['unicode'])+1)
            ->map->values();

        // Generate keyboard data
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($neography, $glyphChunks) {
            $count = 0;
            foreach ($glyphChunks as $i => $chunk) {
                $keyboard = $neography->keyboards()->create([
                    'position' => $i,
                    'width' => 10,
                ]);
                foreach ($chunk as $j => $glyph) {
                    $label = collect(explode(' ', $glyph['glyph-name'])) // Break into words
                        ->filter(fn ($str) => strlen($str)>0) // Remove empty word strings (resulting from extra spaces)
                        ->filter(fn ($str) => !in_array(strtolower($str), self::IGNORE)) // Remove words in IGNORE list
                        ->filter(fn ($str) => strtolower($str) != strtolower($neography->machine_name)) // Remove cases of the neography name
                        ->implode(' ');
                    $keyboard->inputKeys()->create([
                        'label' => $label,
                        'glyph' => $glyph['unicode'],
                        'position' => $j,
                        'render_base' => false,
                    ]);
                    $count++;
                }
            }
            return $count;
        });
    }
}
