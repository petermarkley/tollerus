<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildNativeSpellingSortKey;
use PeterMarkley\Tollerus\Models\NativeSpelling;

class NativeSpellingObserver
{
    private static array $scheduled = [];

    public function created(NativeSpelling $spelling): void
    {
        $this->buildSortKey($spelling);
    }

    public function updated(NativeSpelling $spelling): void
    {
        if ($spelling->wasChanged('spelling')) {
            $this->buildSortKey($spelling);
        }
    }

    private function buildSortKey(NativeSpelling $spelling): void
    {
        app(BuildNativeSpellingSortKey::class)($spelling->fresh());
    }
}
