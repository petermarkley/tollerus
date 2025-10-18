<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\SenseFactory;

class Sense extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'senses';
    public $timestamps = false;
    protected $guarded = [];

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

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return SenseFactory::new();
    }
}
