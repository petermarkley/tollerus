<?php

namespace PeterMarkley\Tollerus\Observers;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyGlyphObserver
{
    private static array $alreadyRan = [];

    public function created(NeographyGlyph $glyph): void
    {
        $this->buildRank($glyph);
    }

    public function updated(NeographyGlyph $glyph): void
    {
        if ($glyph->wasChanged(['position', 'group_id'])) {
            $this->buildRank($glyph);
        }
    }

    private function buildRank(NeographyGlyph $glyph): void
    {
        $glyph->loadMissing(['neography']);
        $neography = $glyph->neography;
        if (isset(self::$alreadyRan[$neography->id])) {
            // Only call action once per request
            return;
        }
        self::$alreadyRan[$neography->id] = true;
        app(BuildGlyphCanonicalRanks::class)($neography);
    }
}
