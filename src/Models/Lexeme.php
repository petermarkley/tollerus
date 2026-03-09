<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Database\Factories\LexemeFactory;

class Lexeme extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    use HasFactory;
    protected $table = 'lexemes';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function wordClass(): BelongsTo
    {
        return $this->belongsTo(WordClass::class);
    }
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
    public function senses(): HasMany
    {
        return $this->hasMany(Sense::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            if ($model->entry && ($model->language_id !== $model->entry->language_id)) {
                throw new \LogicException('Lexeme.language_id must match its entry.language_id');
            }
        });
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return LexemeFactory::new();
    }
}
