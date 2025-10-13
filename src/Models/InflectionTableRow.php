<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class InflectionTableRow extends Model
{
    use HasTablePrefix;
    protected $table = 'inflect_table_rows';
    public $timestamps = false;
    protected $guarded = [];

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
    public function morphRules(): HasMany
    {
        return $this->hasMany(MorphRule::class, 'inflect_table_row_id');
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['inflect_table_id', 'src_lexeme', 'src_base'])) {
                return;
            }
            // src_base must not be this row
            if (!is_null($model->src_base) && (int)$model->src_base === (int)$model->id) {
                throw new \LogicException('An InflectionTableRow\'s src_base cannot refer to itself.');
            }
            // If the main FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->inflect_table_id)) {
                return;
            }

            // Both rules below need this
            $groupIdOfInflectionTable = InflectionTable::query()
                ->whereKey($model->inflect_table_id)
                ->value('word_class_group_id');

            if (!is_null($model->src_lexeme)) {
                /**
                 * Rule 1: src_lexeme must belong to word_class_group
                 */

                // Get the lexeme's `group_id` via a minimal scalar lookup
                $wordClassId = Lexeme::query()
                    ->whereKey($model->src_lexeme)
                    ->value('word_class_id');
                $groupIdOfLexeme = WordClass::query()
                    ->whereKey($wordClassId)
                    ->value('group_id');

                if ((int)$groupIdOfLexeme !== (int)$groupIdOfInflectionTable) {
                    throw new \LogicException('InflectionTableRow.src_lexeme must belong to the same WordClassGroup as the InflectionTableRow\'s parent InflectionTable.');
                }
            }

            if (!is_null($model->src_base)) {
                /**
                 * Rule 2: src_base must belong to word_class_group
                 */

                // Get the base row via a minimal scalar lookup
                $tableIdOfBaseRow = InflectionTableRow::query()
                    ->whereKey($model->src_base)
                    ->value('inflect_table_id');

                // If it's in the same table, we're fine
                if ((int)$model->inflect_table_id !== (int)$tableIdOfBaseRow) {
                    // Otherwise proceed with `word_class_group_id` lookup
                    $groupIdOfBaseRow = InflectionTable::query()
                        ->whereKey($tableIdOfBaseRow)
                        ->value('word_class_group_id');

                    if ((int)$groupIdOfBaseRow !== (int)$groupIdOfInflectionTable) {
                        throw new \LogicException('InflectionTableRow.src_base must belong to the same WordClassGroup as the InflectionTableRow\'s parent InflectionTable.');
                    }
                }
            }
        });
    }
}
