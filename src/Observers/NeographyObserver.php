<?php

namespace PeterMarkley\Tollerus\Observers;

use PeterMarkley\Tollerus\Domain\Neography\Services\FontCssService;
use PeterMarkley\Tollerus\Models\Neography;

class NeographyObserver
{
    public function saved(Neography $neography): void
    {
        FontCssService::forgetCache();
    }

    public function deleted(Neography $neography): void
    {
        FontCssService::forgetCache();
    }
}
