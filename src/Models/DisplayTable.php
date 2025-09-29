<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class DisplayTable extends Model
{
    use HasTablePrefix;
    protected $table = 'disp_tables';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function wordClassGroup(): BelongsTo
    {
        return $this->belongsTo(WordClassGroup::class);
    }
    public function rows(): HasMany
    {
        return $this->hasMany(DisplayTableRows::class, 'disp_table_id');
    }
    public function filters(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'disp_table_filters', 'disp_table_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\DisplayTableFilter::class);
    }
}
