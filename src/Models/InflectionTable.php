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
    public function rows(): HasMany
    {
        return $this->hasMany(InflectionTableRow::class, 'inflect_table_id');
    }
    public function filters(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'inflect_table_filters', 'inflect_table_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionTableFilter::class);
    }
}
