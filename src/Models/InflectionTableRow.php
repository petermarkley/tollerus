<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\InflectionTableRowFactory;

class InflectionTableRow extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'inflect_table_rows';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function inflectionTable(): BelongsTo
    {
        return $this->belongsTo(InflectionTable::class, 'inflect_table_id');
    }
    public function filters(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'inflect_table_row_filters', 'inflect_table_row_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionTableFilter::class);
    }
    public function sourceLexeme(): BelongsTo
    {
        return $this->belongsTo(Lexeme::class, 'src_lexeme');
    }
    public function sourceBase(): BelongsTo
    {
        return $this->belongsTo(InflectionTableRow::class, 'src_base');
    }
    public function builtRows(): HasMany
    {
        return $this->hasMany(InflectionTableRow::class, 'src_base');
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return InflectionTableRowFactory::new();
    }
}
