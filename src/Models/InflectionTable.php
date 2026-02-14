<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class InflectionTable extends Model
{
    use HasTablePrefix;
    protected $table = 'inflect_tables';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function wordClassGroup(): BelongsTo
    {
        return $this->belongsTo(WordClassGroup::class);
    }
    public function columns(): HasMany
    {
        return $this->hasMany(InflectionColumns::class, 'inflect_column_id');
    }
}
