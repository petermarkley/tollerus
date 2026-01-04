<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;

class NeographyGlyphGroupObserver
{
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
    private function buildRank(NeographyGlyphGroup $group): int
    {
        $action = new BuildGlyphCanonicalRanks;
        $group->loadMissing(['section.glyphGroups.glyphs.group.section']);
        $glyphs = $group->section->glyphGroups->flatMap->glyphs;
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
