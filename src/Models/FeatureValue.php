<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class FeatureValue extends Model
{
    use HasTablePrefix;
    protected $table = 'feature_values';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
    public function inflectionExamples(): BelongsToMany
    {
        return $this
            ->belongsToMany(Form::class, 'form_feature_values', 'value_id', 'form_id')
            ->using(Pivots\FormFeatureValue::class);
    }
    public function inflectionColumns(): BelongsToMany
    {
        return $this
            ->belongsToMany(InflectionColumn::class, 'form_feature_values', 'value_id', 'inflect_column_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionColumnFilter::class);
    }
    public function inflectionRows(): BelongsToMany
    {
        return $this
            ->belongsToMany(InflectionRow::class, 'form_feature_values', 'value_id', 'inflect_row_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionRowFilter::class);
    }
}
