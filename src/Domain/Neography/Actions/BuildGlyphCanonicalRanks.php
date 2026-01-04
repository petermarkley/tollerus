<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use PeterMarkley\Tollerus\Models\NeographyGlyph;

final class BuildGlyphCanonicalRanks
{
    private const SEGMENT_WIDTH = 4;

    /**
     * This will populate the `neography_glyphs.canonical_rank` column.
     */
    public function __invoke(NeographyGlyph $glyph): void
    {
        // Find relevant models
        $glyph->loadMissing(['group.section']);
        $group = $glyph->group;
        $sect = $group->section;
        // Calculate rank
        $w = 10 ** self::SEGMENT_WIDTH;
        $rank = $glyph->position
            + $group->position * $w
            + $sect->position * ($w ** 2);
        NeographyGlyph::whereKey($glyph->id)->update(['canonical_rank' => $rank]);
    }
}
