<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Services;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;

/**
 * This service class compiles a display-ready dataset for a native
 * keyboard.
 */
final class NativeKeyboard
{
    public function loadSingleNeography(Neography $neography): array
    {
        $neography->loadMissing(['keyboards.inputKeys']);
        return $neography->keyboards
            ->sortBy('position')
            ->map(fn ($k) => [
                'width' => $k->width,
                'keys' => $k->inputKeys->sortBy('position'),
            ])->toArray();
    }

    public function loadForLanguage(Language $language): array
    {
        $language->loadMissing(['neographies.keyboards.inputKeys']);
        return $language->neographies
            ->mapWithKeys(fn ($n) => [$n->id => [
                'name' => $n->name,
                'machineName' => $n->machine_name,
                'keyboards' => $this->loadSingleNeography($n),
            ]])->toArray();
    }
}

