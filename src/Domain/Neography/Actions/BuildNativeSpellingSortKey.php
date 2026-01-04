<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use PeterMarkley\Tollerus\Models\NativeSpelling;

final class BuildNativeSpellingSortKey
{
    private static array $rankLookup = [];

    /**
     * This will populate the `native_spellings.sort_key` column.
     */
    public function __invoke(NativeSpelling $nativeSpelling): void
    {
        $nativeSpelling->loadMissing(['neography.glyphs']);
        $neography = $nativeSpelling->neography;

        // Establish native definition of 'alphabetical'
        if (!isset(self::$rankLookup[$neography->id])) {
            $canonicalGlyphs = $neography->glyphs
                ->where('render_base', false) // Skip marks
                ->whereNotNull('canonical_rank')
                ->sortBy('canonical_rank');
            self::$rankLookup[$neography->id] = $canonicalGlyphs->pluck('canonical_rank', 'glyph'); // Results in: [glyph => rank]
        }

        // Prepare some data
        $padLen = BuildGlyphCanonicalRanks::SEGMENT_WIDTH * 3;
        $spellingChars = mb_str_split($nativeSpelling->spelling, 1, 'UTF-8');

        // Build sort key
        $rankSequence = collect($spellingChars)
            ->map(fn ($char) => self::$rankLookup[$neography->id]->get($char))
            ->filter(fn ($rank) => $rank !== null)
            ->map(fn ($rank) => str_pad((string) $rank, $padLen, '0', STR_PAD_LEFT))
            ->implode('');
        $tiebreak = bin2hex($nativeSpelling->spelling);
        NativeSpelling::whereKey($nativeSpelling->id)->update(['sort_key' => $rankSequence . '|' . $tiebreak]);
    }

    /**
     * Utility for clearing static cache, for use by batch processes
     */
    public static function clearRankLookup(?int $neographyId = null): void
    {
        if ($neographyId === null) {
            self::$rankLookup = [];
            return;
        }
        unset(self::$rankLookup[$neographyId]);
    }
}
