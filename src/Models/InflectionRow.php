<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class InflectionRow extends Model
{
    use HasTablePrefix;
    protected $table = 'inflect_rows';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(InflectionColumn::class, 'inflect_column_id');
    }
    public function filterValues(): BelongsToMany
    {
        return $this
            ->belongsToMany(FeatureValue::class, 'inflect_row_filters', 'inflect_row_id', 'value_id')
            ->withPivot('feature_id')
            ->using(Pivots\InflectionRowFilter::class);
    }
    public function sourceParticle(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'src_particle');
    }
    public function sourceBase(): BelongsTo
    {
        return $this->belongsTo(InflectionRow::class, 'src_base');
    }
    public function builtRows(): HasMany
    {
        return $this->hasMany(InflectionRow::class, 'src_base');
    }
    public function morphRules(): HasMany
    {
        return $this->hasMany(MorphRule::class, 'inflect_row_id');
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['inflect_column_id', 'src_particle', 'src_base'])) {
                return;
            }
            // src_base must not be this row
            if (!is_null($model->src_base) && (int)$model->src_base === (int)$model->id) {
                throw new \LogicException('An InflectionRow\'s src_base cannot refer to itself.');
            }
            // If the main FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->inflect_column_id)) {
                return;
            }

            // All the rules below need this
            $inflectionTableIdOfColumn = InflectionColumn::query()
                ->whereKey($model->inflect_column_id)
                ->value('inflect_table_id');
            $groupIdOfInflectionTable = InflectionTable::query()
                ->whereKey($inflectionTableIdOfColumn)
                ->value('word_class_group_id');

            if (!is_null($model->src_particle)) {
                /**
                 * Rule 1: src_particle must belong to language
                 */

                // Get the two `language_id`s via minimal scalar lookups
                $languageIdOfParticle = Form::query()
                    ->whereKey($model->src_particle)
                    ->value('language_id');
                $languageIdOfInflectionTable = WordClassGroup::query()
                    ->whereKey($groupIdOfInflectionTable)
                    ->value('language_id');

                if ((int)$languageIdOfParticle !== (int)$languageIdOfInflectionTable) {
                    throw new \LogicException('InflectionRow.src_particle must belong to the same language as the InflectionRow.');
                }
            }

            if (!is_null($model->src_base)) {
                // Get some values via minimal lookups
                $lookup = InflectionRow::select('inflect_column_id', 'src_base')
                    ->whereKey($model->src_base)
                    ->first();
                if ($lookup !== null) {
                    $tableIdOfBaseRow = InflectionColumn::query()
                        ->whereKey($lookup->inflect_column_id)
                        ->value('inflect_table_id');
                    $srcOfBaseRow = $lookup->src_base;
                } else {
                    $tableIdOfBaseRow = null;
                    $srcOfBaseRow = null;
                }

                /**
                 * Rule 2: src_base must belong to word_class_group
                 */

                // If it's in the same table, we're fine
                if ((int)$inflectionTableIdOfColumn !== (int)$tableIdOfBaseRow) {
                    // Otherwise proceed with `word_class_group_id` lookup
                    $groupIdOfBaseRow = InflectionTable::query()
                        ->whereKey($tableIdOfBaseRow)
                        ->value('word_class_group_id');

                    if ((int)$groupIdOfBaseRow !== (int)$groupIdOfInflectionTable) {
                        throw new \LogicException('InflectionRow.src_base must belong to the same WordClassGroup as the InflectionRow\'s parent InflectionTable.');
                    }
                }

                /**
                 * Rule 3: src_base must point to a base row
                 */

                if (!is_null($srcOfBaseRow)) {
                    throw new \LogicException('InflectionRow.src_base must point to a base row (i.e. a row whose own src_base is NULL).');
                }
            }
        });
    }
}
