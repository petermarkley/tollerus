<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\NativeSpelling;

final class BuildNativeSpellingSortKey
{
    /**
     * This will populate the `native_spellings.sort_key` column.
     */
    public function __invoke(NativeSpelling $nativeSpelling): int
    {
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () {
            $count = 0;
            // FIXME
            return $count;
        });
    }
}
