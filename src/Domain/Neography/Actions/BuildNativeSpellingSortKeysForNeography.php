<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class BuildNativeSpellingSortKeysForNeography
{
    /**
     * This will populate the `native_spellings.sort_key` columns for a whole neography.
     */
    public function __invoke(Neography $neography): void
    {
        $neography->loadMissing([
            'glyphs',
            'nativeSpellings',
        ]);
        // Pre-load this once and pass as a parameter to save work
        $canonicalGlyphs = $neography->glyphs
            ->where('render_base', false) // Skip marks
            ->whereNotNull('canonical_rank')
            ->sortBy('canonical_rank');
        $rankLookup = $canonicalGlyphs->pluck('canonical_rank', 'glyph'); // Results in: [glyph => rank]
        foreach ($neography->nativeSpellings as $nativeSpelling) {
            app(BuildNativeSpellingSortKey::class)($nativeSpelling, $rankLookup);
        }
    }
}
