<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\FeatureValueFactory;

class FeatureValue extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'feature_values';
    public $timestamps = false;

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
    public function displayTables(): BelongsToMany
    {
        return $this
            ->belongsToMany(DisplayTable::class, 'form_feature_values', 'value_id', 'disp_table_id')
            ->withPivot('feature_id')
            ->using(Pivots\DisplayTableFilter::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return FeatureValueFactory::new();
    }
}
