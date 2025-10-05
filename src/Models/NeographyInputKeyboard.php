<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class NeographyInputKeyboard extends Model
{
    use HasTablePrefix;
    protected $table = 'neography_input_keyboards';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }
    public function inputKeys(): HasMany
    {
        return $this->hasMany(NeographyInputKey::class, 'keyboard_id');
    }
}
