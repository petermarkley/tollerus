<?php

namespace PeterMarkley\Tollerus\Observers;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographySection;

class NeographySectionObserver
{
    private static array $alreadyRan = [];

    public function created(NeographySection $sect): void
    {
        $this->buildRank($sect);
    }

    public function updated(NeographySection $sect): void
    {
        if ($sect->wasChanged('position')) {
            $this->buildRank($sect);
        }
    }

    private function buildRank(NeographySection $sect): void
    {
        $sect->loadMissing(['neography']);
        $neography = $sect->neography;
        if (isset(self::$alreadyRan[$neography->id])) {
            // Only call action once per request
            return;
        }
        self::$alreadyRan[$neography->id] = true;
        app(BuildGlyphCanonicalRanks::class)($neography);
    }
}
