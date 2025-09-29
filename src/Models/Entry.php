<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;

class Entry extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'entries';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function lexemes(): HasMany
    {
        return $this->hasMany(Lexeme::class);
    }
    public function primaryForm(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'primary_form');
    }
    public function senses(): HasMany
    {
        return $this->hasMany(Sense::class);
    }
}
