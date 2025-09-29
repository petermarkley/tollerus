<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Sense extends Model
{
    use HasTablePrefix;
    protected $table = 'senses';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function lexeme(): BelongsTo
    {
        return $this->belongsTo(Lexeme::class);
    }
    public function subsenses(): HasMany
    {
        return $this->hasMany(Subsense::class);
    }
}
