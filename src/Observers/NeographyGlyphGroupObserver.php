<?php

namespace PeterMarkley\Tollerus\Observers;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;

class NeographyGlyphGroupObserver
{
    private static array $alreadyRan = [];

    public function created(NeographyGlyphGroup $group): void
    {
        $this->buildRank($group);
    }

    public function updated(NeographyGlyphGroup $group): void
    {
        if ($group->wasChanged(['position', 'section_id'])) {
            $this->buildRank($group);
        }
    }

    private function buildRank(NeographyGlyphGroup $group): void
    {
        $group->loadMissing(['section.neography']);
        $neography = $group->section->neography;
        if (isset(self::$alreadyRan[$neography->id])) {
            // Only call action once per request
            return;
        }
        self::$alreadyRan[$neography->id] = true;
        app(BuildGlyphCanonicalRanks::class)($neography);
    }
}
