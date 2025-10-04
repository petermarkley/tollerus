<?php

namespace PeterMarkley\Tollerus\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class FormFeatureValue extends Pivot
{
    use HasTablePrefix;

    protected $table = 'form_feature_values';
    public $timestamps = false;
    public $incrementing = true;

    protected static function booted(): void
    {
        // Validate extended model relations
        static::saving(function (self $model): void {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['form_id', 'feature_id', 'value_id'])) {
                return;
            }

            // If any FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->form_id) || is_null($model->feature_id) || is_null($model->value_id)) {
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
                throw new \LogicException('FormFeatureValue.value_id must reference a FeatureValue that belongs to feature_id.');
            }

            /**
             * Rule 2: feature must be in the same WordClassGroup as the form’s WordClass
             */

            // Get the two `group_id`s via minimal scalar lookups
            $lexemeId = \PeterMarkley\Tollerus\Models\Form::query()
                ->whereKey($model->form_id)
                ->value('lexeme_id');
            $wordClassId = \PeterMarkley\Tollerus\Models\Lexeme::query()
                ->whereKey($lexemeId)
                ->value('word_class_id');
            $groupIdOfForm = \PeterMarkley\Tollerus\Models\WordClass::query()
                ->whereKey($wordClassId)
                ->value('group_id');
            $groupIdOfFeature = \PeterMarkley\Tollerus\Models\Feature::query()
                ->whereKey($model->feature_id)
                ->value('word_class_group_id');

            if ((int)$groupIdOfForm !== (int)$groupIdOfFeature) {
                throw new \LogicException('FormFeatureValue.feature must belong to the same WordClassGroup as the Form\'s WordClass.');
            }
        });
    }
}
