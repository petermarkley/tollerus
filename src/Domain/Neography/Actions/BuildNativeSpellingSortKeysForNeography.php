<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class BuildNativeSpellingSortKeysForNeography
{
    /**
     * This will populate the `native_spellings.sort_key` columns for a whole neography.
     *
     * WARNING! For large, already-populated conlangs, this could take a minute.
     * Consider queueing a job so the web page doesn't freeze for the user.
     * (See: src/Domain/Neography/Jobs/BuildNativeSpellingSortKeysForNeographyJob.php)
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
