<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class DisplayTableRow extends Model
{
    use HasTablePrefix;
    protected $table = 'disp_table_rows';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function displayTable(): BelongsTo
    {
        return $this->belongsTo(DisplayTable::class);
    }
    public function filters(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'disp_table_row_filters', 'disp_table_row_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\DisplayTableFilter::class);
    }
}
