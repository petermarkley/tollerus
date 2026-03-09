<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Database\Factories\EntryFactory;

class Entry extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    use HasFactory;
    protected $table = 'entries';
    public $timestamps = false;
    protected $guarded = [];

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

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return EntryFactory::new();
    }
}
