<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyGlyphObserver
{
    private static array $scheduled = [];

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
        if (isset(self::$scheduled[$neography->id])) {
            // Only call action once per request
            return;
        }
        self::$scheduled[$neography->id] = true;
        DB::afterCommit(function () use ($neography) {
            app(BuildGlyphCanonicalRanks::class)($neography->fresh());
        });
    }
}
