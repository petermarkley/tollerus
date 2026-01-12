<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Database\Factories\FormFactory;

class Form extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    use HasFactory;
    protected $table = 'forms';
    public $timestamps = false;
    protected $guarded = [];

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
    public function affectedInflections(): HasMany
    {
        return $this->hasMany(InflectionTableRow::class, 'src_particle');
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

    /**
     * Convenience method(s) expected by the application
     */
    public function primaryNativeSpelling(): ?NativeSpelling
    {
        $this->loadMissing(['language.primaryNeography']);
        $neographyId = $this->language?->primaryNeography?->id;
        if ($this->relationLoaded('nativeSpellings')) {
            return $this->nativeSpellings->firstWhere('neography_id', $neographyId);
        } else {
            return $this->nativeSpellings()->where('neography_id', $neographyId)->first();
        }
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return FormFactory::new();
    }
}
