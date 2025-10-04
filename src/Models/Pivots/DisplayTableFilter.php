<?php

namespace PeterMarkley\Tollerus\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class DisplayTableFilter extends Pivot
{
    use HasTablePrefix;

    protected $table = 'disp_table_filters';
    public $timestamps = false;
    public $incrementing = true;

    protected static function booted(): void
    {
        // Validate extended model relations
        static::saving(function (self $model): void {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['disp_table_id', 'feature_id', 'value_id'])) {
                return;
            }

            // If any FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->disp_table_id) || is_null($model->feature_id) || is_null($model->value_id)) {
                return;
            }

            /**
             * Rule 1: value_id must belong to feature_id
             */

            $valueMatchesFeature = \PeterMarkley\Tollerus\Models\FeatureValue::query()
                ->whereKey($model->value_id)
                ->where('feature_id', $model->feature_id)
                ->exists();

            if (!$valueMatchesFeature) {
                throw new \LogicException('DisplayTableFilter.value_id must reference a FeatureValue that belongs to feature_id.');
            }

            /**
             * Rule 2: feature must be in the same WordClassGroup as the display table
             */

            // Get the two `word_class_group_id`s via minimal scalar lookups
            $groupIdOfTable = \PeterMarkley\Tollerus\Models\DisplayTable::query()
                ->whereKey($model->disp_table_id)
                ->value('word_class_group_id');
            $groupIdOfFeature = \PeterMarkley\Tollerus\Models\Feature::query()
                ->whereKey($model->feature_id)
                ->value('word_class_group_id');

            if ((int)$groupIdOfTable !== (int)$groupIdOfFeature) {
                throw new \LogicException('DisplayTableFilter.feature must belong to the same WordClassGroup as its DisplayTable.');
            }
        });
    }
}
