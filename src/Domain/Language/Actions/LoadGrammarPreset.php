<?php

namespace PeterMarkley\Tollerus\Domain\Language\Actions;

use Illuminate\Support\Facades\DB;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\InflectionTableRow;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableRowFilter;

final class LoadGrammarPreset
{
    /**
     * This will add WordClassGroups, Features/FeatureValues, and InflectionTables
     * based on the given preset template.
     */
    public function __invoke(Language $language, string $preset): int
    {
        $connection = config('tollerus.connection', 'tollerus');
        return DB::connection($connection)->transaction(function () use ($language, $preset) {
            // Read the file
            $fileName = __DIR__.'/../../../../resources/data/grammar_presets/'.$preset.'.json';
            if (!file_exists($fileName)) {
                throw new \InvalidArgumentException("File '{$fileName}' doesn't exist.");
            }
            $json = json_decode(file_get_contents($fileName));

            // Prevent collisions from any other processes
            Language::query()
                ->whereKey($language->getKey())
                ->lockForUpdate()
                ->first();
            // Prevent duplicate grammar data
            $exists = WordClassGroup::query()
                ->whereBelongsTo($language)
                ->exists();
            if ($exists) {
                throw new \DomainException('Grammar already initialized for this language.');
            }

            $prefix = 'tollerus::grammar_presets/'.$json->i18n_file;
            foreach ($json->groups as $groupJson) {
                $wordClassGroup = $language->wordClassGroups()->create();

                // classes
                foreach ($groupJson->classes as $classJson) {
                    $wordClass = $wordClassGroup->wordClasses()->create([
                        'name' => __("{$prefix}.word_classes.{$classJson->i18n_key}.name"),
                        'name_brief' => tollerus_tr_optional("{$prefix}.word_classes.{$classJson->i18n_key}.name_brief"),
                        'language_id' => $language->id
                    ]);
                    if (isset($classJson->primary) && $classJson->primary) {
                        $wordClassGroup->primary_class = $wordClass->id;
                        $wordClassGroup->save();
                    }
                }

                // features
                if (!isset($groupJson->features)) {
                    /**
                     * Without grammatical features, inflection tables
                     * are meaningless. Move on to the next group.
                     */
                    continue;
                }
                /**
                 * We're building a keyed array that we can easily reference later.
                 * The structure is something like this:
                 * [
                 *     'feature_name1' => [
                 *         'model' => <Feature>,
                 *         'values' => [
                 *             'value_name1' => <FeatureValue>,
                 *             'value_name2' => <FeatureValue>,
                 *             ...
                 *         ]
                 *     ],
                 *     'feature_name2' => [...],
                 *     ...
                 * ]
                 * The array keys should all correspond to string keys in the i18n
                 * file.
                 */
                $features = collect($groupJson->features)->mapWithKeys(function ($featureJson) use ($prefix, $wordClassGroup) {
                    // Build next segment of i18n key
                    $featureKey = "{$prefix}.{$featureJson->i18n_key}";
                    // Create model
                    $model = $wordClassGroup->features()->create([
                        'name' => __("{$featureKey}._name"),
                        'name_brief' => tollerus_tr_optional("{$featureKey}._name_brief"),
                    ]);
                    // Build output
                    return [$featureJson->i18n_key => [
                        'model' => $model,
                        'values' => collect($featureJson->values)->mapWithKeys(function ($valueStr) use ($featureKey, $model) {
                            // Build another segment of i18n key
                            $valueKey = "{$featureKey}.{$valueStr}";
                            // Build output
                            return [$valueStr => $model->featureValues()->create([
                                'name' => __("{$valueKey}.name"),
                                'name_brief' => tollerus_tr_optional("{$valueKey}.name_brief"),
                            ])];
                        })->all()
                    ]];
                })->toArray();

                // inflection tables
                if (!isset($groupJson->inflection_tables)) {
                    continue;
                }
                $baseRow = null;
                /**
                 * We're doing a similar thing here as above, except the keys
                 * are numeric and represent the 'position' fields:
                 * [
                 *     0 => [
                 *         'model' => <InflectionTable>,
                 *         'rows' => [
                 *             0 => <InflectionTableRow>,
                 *             1 => <InflectionTableRow>,
                 *             ...
                 *         ]
                 *     ],
                 *     1 => [...],
                 *     ...
                 * ]
                 */
                $tables = collect($groupJson->inflection_tables)->map(function ($tableJson, $tablePos) use ($prefix, $wordClassGroup, $features, &$baseRow) {
                    // Build next segment of i18n key
                    $tableKey = "{$prefix}.inflection_tables.{$tableJson->i18n_key}";
                    if (isset($tableJson->i18n_subkey)) {
                        $tableKey .= ".{$tableJson->i18n_subkey}";
                    } else {
                        $tableKey .= "._label";
                    }
                    // Create model
                    $table = $wordClassGroup->inflectionTables()->create([
                        'label'          => __($tableKey),
                        'position'       => $tablePos,
                        'visible'        => $tableJson->visible ?? true,
                        'show_label'     => $tableJson->show_label ?? true,
                        'stack'          => $tableJson->stack,
                        'align_on_stack' => $tableJson->align_on_stack,
                        'table_fold'     => $tableJson->table_fold,
                        'rows_fold'      => $tableJson->rows_fold
                    ]);
                    // Add filters
                    foreach ($tableJson->filters as $filterJson) {
                        /**
                         * Look up models in the '$features' array that we built
                         * earlier, so that we can assign IDs.
                         */
                        $feature = $features[$filterJson->feature]['model'];
                        $value = $features[$filterJson->feature]['values'][$filterJson->value];
                        (new InflectionTableFilter([
                            'inflect_table_id' => $table->id,
                            'feature_id' => $feature->id,
                            'value_id' => $value->id,
                        ]))->save();
                    }
                    // Build output
                    return [
                        'model' => $table,
                        'rows' => collect($tableJson->rows)->map(function ($rowJson, $rowPos) use ($prefix, $table, $features, &$baseRow) {
                            // Build another segment of i18n key
                            $rowKey = "{$prefix}.inflection_tables.{$rowJson->i18n_key}";
                            // Create model
                            $row = $table->rows()->create([
                                'label' => __("{$rowKey}.label"),
                                'label_brief' => tollerus_tr_optional("{$rowKey}.label_brief"),
                                'label_long' => tollerus_tr_optional("{$rowKey}.label_long"),
                                'position' => $rowPos,
                                'src_base' => null,
                            ]);
                            // Add filters
                            foreach ($rowJson->filters as $filterJson) {
                                /**
                                 * And again, look up IDs in the '$features' array
                                 */
                                $feature = $features[$filterJson->feature]['model'];
                                $value = $features[$filterJson->feature]['values'][$filterJson->value];
                                (new InflectionTableRowFilter([
                                    'inflect_table_row_id' => $row->id,
                                    'feature_id' => $feature->id,
                                    'value_id' => $value->id,
                                ]))->save();
                            }
                            // Track base row
                            if (isset($rowJson->base) && $rowJson->base) {
                                $baseRow = $row;
                            }
                            // Pass output
                            return $row;
                        })->all()
                    ];
                })->toArray();
                /**
                 * Now that we know $baseRow, we need to make a second pass on the
                 * array and assign the `src_base` fields.
                 *
                 * In our use cases so far, this wasn't an issue because we just
                 * defined the base row first and then assigned it as each row
                 * was defined. But with the JSON templates, we can't really assume
                 * that the base row is first. That's why we need a 2nd pass.
                 */
                foreach ($tables as $table) {
                    foreach ($table['rows'] as $row) {
                        if ($baseRow !== $row && $baseRow !== null) {
                            $row->src_base = $baseRow->id;
                            $row->save();
                        }
                    }
                }
            }
            return 1;
        });
    }
}
