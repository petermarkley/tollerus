<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\WordClassGroupFactory;

class WordClassGroup extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'word_class_groups';
    public $timestamps = false;

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
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
    public function inflectionTables(): HasMany
    {
        return $this->hasMany(InflectionTable::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return WordClassGroupFactory::new();
    }
}
