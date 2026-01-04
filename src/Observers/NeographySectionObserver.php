<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographySection;

class NeographySectionObserver
{
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
    private function buildRank(NeographySection $sect): int
    {
        $action = new BuildGlyphCanonicalRanks;
        $sect->loadMissing(['neography.glyphs.group.section']);
        $glyphs = $sect->neography->glyphs;
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($glyphs, $action) {
            $count = 0;
            foreach ($glyphs as $glyph) {
                $action($glyph);
                $count++;
            }
            return $count;
        });
    }
}
