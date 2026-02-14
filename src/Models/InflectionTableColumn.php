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
    public function inflectionTable(): BelongsTo
    {
        return $this->belongsTo(InflectionTable::class, 'inflect_table_id');
    }
    public function rows(): HasMany
    {
        return $this->hasMany(InflectionTableRow::class, 'inflect_table_column_id');
    }
    public function filterValues(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'inflect_table_column_filters', 'inflect_table_column_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionTableColumnFilter::class);
    }
}
