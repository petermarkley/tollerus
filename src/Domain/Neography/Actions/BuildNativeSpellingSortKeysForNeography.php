<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Neography;

final class BuildNativeSpellingSortKeysForNeography
{
    /**
     * This will populate the `native_spellings.sort_key` columns for a whole neography.
     */
    public function __invoke(Neography $neography): int
    {
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () {
            $count = 0;
            // FIXME
            return $count;
        });
    }
}
