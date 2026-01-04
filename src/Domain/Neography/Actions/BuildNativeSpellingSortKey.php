<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Models\NativeSpelling;

final class BuildNativeSpellingSortKey
{
    /**
     * This will populate the `native_spellings.sort_key` column.
     */
    public function __invoke(NativeSpelling $nativeSpelling, ?Collection $canonicalGlyphs = null): void
    {
        $nativeSpelling->loadMissing(['neography.glyphs']);
        $neography = $nativeSpelling->neography;

        // Establish native definition of 'alphabetical'
        if ($canonicalGlyphs === null) {
            $canonicalGlyphs = $neography->glyphs
                ->where('render_base', false) // Skip marks
                ->whereNotNull('canonical_rank')
                ->sortBy('canonical_rank');
        }

        // Prepare some data
        $padLen = BuildGlyphCanonicalRanks::SEGMENT_WIDTH * 3;
        $spellingChars = mb_str_split($nativeSpelling->spelling, 1, 'UTF-8');
        $rankLookup = $canonicalGlyphs->pluck('canonical_rank', 'glyph'); // Results in: [glyph => rank]

        // Build sort key
        $rankSequence = collect($spellingChars)
            ->map(fn ($char) => $rankLookup->get($char))
            ->filter(fn ($rank) => $rank !== null)
            ->map(fn ($rank) => str_pad((string) $rank, $padLen, '0', STR_PAD_LEFT))
            ->implode('');
        $tiebreak = bin2hex($nativeSpelling->spelling);
        NativeSpelling::whereKey($nativeSpelling->id)->update(['sort_key' => $rankSequence . '|' . $tiebreak]);
    }
}
