<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\FeatureFactory;

class Feature extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'features';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(WordClassGroup::class, 'word_class_group_id');
    }
    public function featureValues(): HasMany
    {
        return $this->hasMany(FeatureValues::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return FeatureFactory::new();
    }
}
