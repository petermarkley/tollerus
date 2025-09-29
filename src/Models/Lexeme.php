<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;

class Lexeme extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'lexemes';
    public $timestamps = false;

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

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            if ($model->entry && ($model->language_id !== $model->entry->language_id)) {
                throw new \LogicException('Lexeme.language_id must match its entry.language_id');
            }
        });
    }
}
