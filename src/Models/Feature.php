<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Feature extends Model
{
    use HasTablePrefix;
    protected $table = 'features';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(WordClassGroup::class, 'word_class_group_id');
    }
    public function featureValues(): HasMany
    {
        return $this->hasMany(FeatureValue::class);
    }
}
