<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class WordClassGroup extends Model
{
    use HasTablePrefix;
    protected $table = 'word_class_groups';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    public function wordClasses(): HasMany
    {
        return $this->hasMany(WordClass::class, 'group_id');
    }
    public function primaryClass(): BelongsTo
    {
        return $this->belongsTo(WordClass::class, 'primary_class');
    }
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
    public function inflectionTables(): HasMany
    {
        return $this->hasMany(InflectionTable::class);
    }
}
