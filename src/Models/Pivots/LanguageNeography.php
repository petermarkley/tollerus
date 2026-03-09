<?php

namespace PeterMarkley\Tollerus\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class LanguageNeography extends Pivot
{
    use HasTablePrefix;

    protected $table = 'language_neography';
    public $timestamps = false;
    public $incrementing = true;
}
