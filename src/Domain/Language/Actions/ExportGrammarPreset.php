<?php

namespace PeterMarkley\Tollerus\Domain\Language\Actions;

use Illuminate\Support\Facades\File;

use PeterMarkley\Tollerus\Models\Language;

final class ExportGrammarPreset
{
    private const OUT_DIR = 'app/tollerus/exports/grammar_presets';

    /**
     * Uses the grammar config of the given Language to
     * generate a pair of grammar preset files, e.g.:
     *   - data/myconlang.json
     *   - lang/en/myconlang.php
     *
     * These are placed inside the host app's
     * `storage/app/tollerus/grammar-presets` folder.
     */
    public function __invoke(Language $language, string $preset): void
    {
        /**
         * JSON data
         */
        $data = [
            'i18n_file' => $preset,
            'groups' => $language->wordClassGroups->map(function ($group) {
                // Determine base row
                $nullRows = $group->inflectionTables
                    ->flatMap->columns
                    ->flatMap->rows
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'src_base' => $r->src_base,
                    ])->filter(fn ($r) => $r['src_base'] === null)
                    ->pluck('id')
                    ->toArray();
                if (count($nullRows) !== 1) {
                    $baseRow = null;
                } else {
                    $baseRow = $nullRows[0];
                }
                // Build output
                $obj = [
                    'classes' => $group->wordClasses->sortBy('name')->values()->map(fn ($class) => [
                        'i18n_key' => $class->name,
                        'primary' => $class->id === $group->primary_class,
                    ])->toArray(),
                ];
                if ($group->features->isNotEmpty()) {
                    $obj['features'] = $group->features->sortBy('name')->values()->map(fn ($feature) => [
                        'i18n_key' => $feature->name,
                        'values' => $feature->featureValues->pluck('name')->toArray(),
                    ])->toArray();
                }
                if ($group->inflectionTables->isNotEmpty()) {
                    $obj['inflection_tables'] = $group->inflectionTables->sortBy('position')->values()->map(fn ($table) => [
                        'visible' => (bool)$table->visible,
                        'align_on_stack' => (bool)$table->align_on_stack,
                        'cols_fold' => (bool)$table->cols_fold,
                        'rows_fold' => (bool)$table->rows_fold,
                        'columns' => $table->columns->sortBy('position')->values()->map(fn ($column) => [
                            'i18n_key' => $column->label,
                            'visible' => (bool)$column->visible,
                            'show_label' => (bool)$column->show_label,
                            'filters' => $column->filterValues->map(fn ($filter) => [
                                'feature' => $filter->feature->name,
                                'value' => $filter->name,
                            ])->toArray(),
                            'rows' => $column->rows->sortBy('position')->values()->map(function ($row) use ($baseRow) {
                                $obj = [
                                    'i18n_key' => $row->label,
                                    'filters' => $row->filterValues->map(fn ($filter) => [
                                        'feature' => $filter->feature->name,
                                        'value' => $filter->name,
                                    ])->toArray(),
                                ];
                                if ($row->id === $baseRow) {
                                    $obj['base'] = true;
                                }
                                return $obj;
                            })->toArray(),
                        ])->toArray(),
                    ])->toArray();
                }
                return $obj;
            })->toArray(),
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $dataFolder = storage_path(self::OUT_DIR.'/data');
        File::ensureDirectoryExists($dataFolder);
        file_put_contents($dataFolder.'/'.$preset.'.json', $json);

        /**
         * Lang file
         */
        $langFile = <<<PHP
        <?php

        /**
         * TRANSLATOR NOTE:
         *
         * For grammar presets, abbreviated names may be an empty string ''.
         * This is intentional and means that the word is short enough that
         * it doesn't need an abbreviation, for example the word "past" for
         * past tense verbs.
         */
        return [

        PHP;

        $langFile .= <<<PHP
        ];

        PHP;
        $locale = app()->getLocale();
        $langFolder = storage_path(self::OUT_DIR.'/lang/'.$locale);
        File::ensureDirectoryExists($langFolder);
        file_put_contents($langFolder.'/'.$preset.'.php', $langFile);
    }
}