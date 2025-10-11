<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\DisplayTableRowFactory;

class DisplayTableRow extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'disp_table_rows';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function displayTable(): BelongsTo
    {
        return $this->belongsTo(DisplayTable::class, 'disp_table_id');
    }
    public function filters(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'disp_table_row_filters', 'disp_table_row_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\DisplayTableFilter::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return DisplayTableRowFactory::new();
    }
}
