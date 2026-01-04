<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

final class BuildGlyphCanonicalRanks
{
    private const SEGMENT_WIDTH = 4;

    /**
     * This will populate the `neography_glyphs.canonical_rank` column for
     * all the glyphs in the given neography.
     */
    public function __invoke(Neography $neography): int
    {
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($neography) {
            $count = 0;
            $neography->loadMissing(['sections.glyphGroups.glyphs']);
            /**
             * To avoid violating the unique constraint, let's null all
             * of them before recalculating.
             */
            foreach ($neography->sections as $sect) {
                foreach ($sect->glyphGroups as $group) {
                    foreach ($group->glyphs as $glyph) {
                        NeographyGlyph::whereKey($glyph->id)->update(['canonical_rank' => null]);
                    }
                }
            }
            $w = 10 ** self::SEGMENT_WIDTH;
            foreach ($neography->sections as $sect) {
                foreach ($sect->glyphGroups as $group) {
                    foreach ($group->glyphs as $glyph) {
                        $rank = $glyph->position
                            + $group->position * $w
                            + $sect->position * ($w ** 2);
                        NeographyGlyph::whereKey($glyph->id)->update(['canonical_rank' => $rank]);
                        $count++;
                    }
                }
            }
            return $count;
        });
    }
}
