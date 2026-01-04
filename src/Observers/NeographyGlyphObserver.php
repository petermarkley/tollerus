<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyGlyphObserver
{
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
    private function buildRank(NeographyGlyph $glyphModel): int
    {
        $action = new BuildGlyphCanonicalRanks;
        $glyphModel->loadMissing(['group.glyphs.group.section']);
        $glyphs = $glyphModel->group->glyphs;
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
