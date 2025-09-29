<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;

class Form extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'forms';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function lexeme(): BelongsTo
    {
        return $this->belongsTo(Lexeme::class);
    }
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function nativeSpellings(): HasMany
    {
        return $this->hasMany(NativeSpelling::class);
    }
    public function inflectionValues(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'form_feature_values', 'form_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\FormFeatureValue::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            if ($model->lexeme && ($model->language_id !== $model->lexeme->language_id)) {
                throw new \LogicException('Form.language_id must match its lexeme.language_id');
            }
        });
    }
}
