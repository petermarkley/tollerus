<?php

namespace PeterMarkley\Tollerus\Domain\Language\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
    public function __invoke(
        Language $language,
        string $preset,
        ?string $presetName = null,
        ?string $presetDesc = null,
    ): void
    {
        $locale = app()->getLocale();
        $preset = self::snakeCase($preset);
        if (empty($presetName)) {
            $presetName = $language->name;
        }
        if (empty($presetDesc)) {
            $presetDesc = "This preset scaffolds a conlang grammar that resembles {$language->name}.";
        }

        /**
         * JSON data
         */
        $wordClasses = [];
        $allFeatures = [];
        $data = [
            'i18n_file' => $preset,
            'groups' => $language->wordClassGroups->map(function ($group) use (&$wordClasses, &$allFeatures) {
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
                    'classes' => $group->wordClasses->sortBy('name')->values()->map(function ($class) use ($group, &$wordClasses) {
                        $wordClasses[] = [
                            'key' => ExportGrammarPreset::snakeCase($class->name),
                            'name' => $class->name,
                            'name_brief' => $class->name_brief,
                        ];
                        return [
                            'i18n_key' => ExportGrammarPreset::snakeCase($class->name),
                            'primary' => $class->id === $group->primary_class,
                        ];
                    })->toArray(),
                ];
                if ($group->features->isNotEmpty()) {
                    $obj['features'] = $group->features->sortBy('name')->values()->map(function ($feature) use (&$allFeatures) {
                        $key = ExportGrammarPreset::snakeCase($feature->name);
                        if (!isset($allFeatures[$key])) {
                            $allFeatures[$key] = [
                                '_name' => $feature->name,
                                '_name_brief' => $feature->name_brief,
                                'values' => [],
                            ];
                        }
                        foreach ($feature->featureValues as $v) {
                            $subkey = ExportGrammarPreset::snakeCase($v->name);
                            if (!isset($allFeatures[$key]['values'][$subkey])) {
                                $allFeatures[$key]['values'][$subkey] = [
                                    'name' => $v->name,
                                    'name_brief' => $v->name_brief,
                                ];
                            }
                        }
                        return [
                            'i18n_key' => ExportGrammarPreset::snakeCase($feature->name),
                            'values' => $feature->featureValues->pluck('name')->toArray(),
                        ];
                    })->toArray();
                }
                if ($group->inflectionTables->isNotEmpty()) {
                    $obj['inflection_tables'] = $group->inflectionTables->sortBy('position')->values()->map(fn ($table) => [
                        'visible' => (bool)$table->visible,
                        'align_on_stack' => (bool)$table->align_on_stack,
                        'cols_fold' => (bool)$table->cols_fold,
                        'rows_fold' => (bool)$table->rows_fold,
                        'columns' => $table->columns->sortBy('position')->values()->map(fn ($column) => [
                            'i18n_key' => ExportGrammarPreset::snakeCase($column->label),
                            'visible' => (bool)$column->visible,
                            'show_label' => (bool)$column->show_label,
                            'filters' => $column->filterValues->map(fn ($filter) => [
                                'feature' => $filter->feature->name,
                                'value' => $filter->name,
                            ])->toArray(),
                            'rows' => $column->rows->sortBy('position')->values()->map(function ($row) use ($baseRow) {
                                $obj = [
                                    'i18n_key' => ExportGrammarPreset::snakeCase($row->label),
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
            'preset_name' => '{$presetName}',
            'preset_description' => '{$presetDesc}',

            /**
             * ===========================================================
             *                 TECHNICAL/ADMIN LABELS
             * ===========================================================
             *
             * TRANSLATOR NOTE:
             *
             * This first portion is mostly a list of technical terms. It
             * will display to admins who are configuring the conlang
             * grammar, not to general users who are viewing/browsing the
             * dictionary.
             *
             * Later on, there are other labels for display to lay people.
             */
            'word_classes' => [

        PHP;
        foreach ($wordClasses as $class) {
            $name = var_export($class["name"], true);
            $nameBrief = var_export($class["name_brief"], true);
            $langFile .= <<<PHP
                    '{$class["key"]}' => [
                        'name' => {$name},
                        'name_brief' => {$nameBrief},
                    ],

            PHP;
        }
        $langFile .= <<<PHP
            ],

        PHP;
        foreach ($allFeatures as $key => $feature) {
            $name = var_export($feature["_name"] ?? '', true);
            $nameBrief = var_export($feature["_name_brief"] ?? '', true);
            $langFile .= <<<PHP
                '{$key}' => [
                    '_name' => {$name},
                    '_name_brief' => {$nameBrief},

            PHP;
            foreach ($feature['values'] as $subkey => $value) {
                $name = var_export($value["name"] ?? '', true);
                $nameBrief = var_export($value["name_brief"] ?? '', true);
                $langFile .= <<<PHP
                        '{$subkey}' => [
                            'name' => {$name},
                            'name_brief' => {$nameBrief},
                        ],

                PHP;
            }
            $langFile .= <<<PHP
                ],

            PHP;
        }

        // ...

        $langFile .= <<<PHP
        ];

        PHP;

        /**
         * Dump output to files
         */
        $dataFolder = storage_path(self::OUT_DIR.'/data');
        File::ensureDirectoryExists($dataFolder);
        file_put_contents($dataFolder.'/'.$preset.'.json', $json);
        $langFolder = storage_path(self::OUT_DIR.'/lang/'.$locale);
        File::ensureDirectoryExists($langFolder);
        file_put_contents($langFolder.'/'.$preset.'.php', $langFile);
    }

    public function snakeCase(string $value): string
    {
        return str_replace('-', '_', Str::slug($value));
    }
}
