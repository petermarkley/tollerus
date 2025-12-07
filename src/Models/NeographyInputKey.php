<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class NeographyInputKey extends Model
{
    use HasTablePrefix;
    protected $table = 'neography_input_keys';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function keyboard(): BelongsTo
    {
        return $this->belongsTo(NeographyInputKeyboard::class, 'keyboard_id');
    }
}
