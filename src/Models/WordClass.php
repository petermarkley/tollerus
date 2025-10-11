<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\WordClassFactory;

class WordClass extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'word_classes';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(WordClassGroup::class, 'group_id');
    }
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function wordClasses(): HasMany
    {
        return $this->hasMany(WordClass::class);
    }
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            if ($model->group && ($model->language_id !== $model->group->language_id)) {
                throw new \LogicException('WordClass.language_id must match its group.language_id');
            }
        });
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return WordClassFactory::new();
    }
}
