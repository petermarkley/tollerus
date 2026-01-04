<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class BuildNativeSpellingSortKeysForNeography
{
    /**
     * This will populate the `native_spellings.sort_key` columns for a whole neography.
     */
    public function __invoke(Neography $neography): void
    {
        $neography->loadMissing([
            'glyphs',
            'nativeSpellings',
        ]);
        foreach ($neography->nativeSpellings as $nativeSpelling) {
            app(BuildNativeSpellingSortKey::class)($nativeSpelling);
        }
        BuildNativeSpellingSortKey::clearRankLookup($neography->id);
    }
}
