<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class InflectionTableEditor extends Component
{
    use HasModelCache;
    private $cacheRoot = 'tables';
    // Models
    #[Locked] public Language $language;
    #[Locked] public WordClassGroup $group;
    #[Locked] public array $tables;
    // UI input layer
    public array $tableForm = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        if ($this->group->primaryClass === null) {
            $groupName = __('tollerus::ui.group_nameless');
        } else {
            $groupName = $this->group->primaryClass->name;
        }
        $pageTitle = $this->language->name . ': ' . mb_ucfirst($groupName) . ': ' . __('tollerus::ui.inflection_tables');
        return view('tollerus::livewire.inflection-table-editor', [
                'groupName' => $groupName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', ['language' => $this->language->id, 'tab' => 'grammar']), 'text' => $this->language->name],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $group): void
    {
        $this->language = $language;
        $this->group = $group;
        $this->tables = $group->inflectionTables->sortBy('position')->all();
        $this->refreshTableForm();
    }

    /**
     * Refresh UI input layer from database
     */
    public function refreshTableForm(): void
    {
        $this->group->loadMissing([
            'features.featureValues',
        ]);
        foreach ($this->tables as $table) {
            $table->loadMissing([
                'filterValues.feature',
                'rows.filterValues.feature',
                'rows.sourceParticle.nativeSpellings',
            ]);
        }
        $primaryNeographyId = $this->language->primary_neography;
        $neographies = $this->language->neographies;
        $this->tableForm = collect($this->tables)->mapWithKeys(function ($table) use ($primaryNeographyId, $neographies) {
            return [$table->id => [
                'label'        => $table->label,
                'visible'      => (bool)($table->visible),
                'showLabel'    => (bool)($table->show_label),
                'position'     => $table->position,
                'stack'        => (bool)($table->stack),
                'alignOnStack' => (bool)($table->align_on_stack),
                'tableFold'    => (bool)($table->table_fold),
                'rowsFold'     => (bool)($table->rows_fold),
                'filters' => $table->filterValues->mapWithKeys(function ($filterValue) {
                    return [$filterValue->id => [
                        'featureId'   => $filterValue->feature->id,
                        'featureName' => $filterValue->feature->name,
                        'valueId'     => $filterValue->id,
                        'valueName'   => $filterValue->name,
                    ]];
                })->toArray(),
                'rows' => $table->rows->sortBy('position')->mapWithKeys(function ($row) use ($primaryNeographyId, $neographies) {
                    return [$row->id => [
                        'label'         => $row->label,
                        'labelBrief'    => $row->label_brief,
                        'labelLong'     => $row->label_long,
                        'visible'       => (bool)($row->visible),
                        'showLabel'     => (bool)($row->show_label),
                        'position'      => $row->position,
                        'morphTemplate' => $row->morph_template,
                        'srcParticle' => ($row->sourceParticle === null ? null : [
                            'id' => $row->sourceParticle->id,
                            'transliterated' => $row->sourceParticle->transliterated,
                            'phonemic' => $row->sourceParticle->phonemic,
                            'primaryNativeSpelling' => $row->sourceParticle->nativeSpellings
                                ->firstWhere('neography_id', $primaryNeographyId),
                        ]),
                        'filters' => $row->filterValues->mapWithKeys(function ($filterValue) {
                            return [$filterValue->id => [
                                'featureId'   => $filterValue->feature->id,
                                'featureName' => $filterValue->feature->name,
                                'valueId'     => $filterValue->id,
                                'valueName'   => $filterValue->name,
                            ]];
                        })->toArray(),
                        'morphRules' => [
                            'onBaseTransliterated' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::BaseInput && $r->pattern_type == MorphRulePatternType::Transliterated)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onBasePhonemic' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::BaseInput && $r->pattern_type == MorphRulePatternType::Phonemic)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onBaseNative' => $neographies->mapWithKeys(fn ($neography) => [
                                $neography->id => [
                                    'neographyId' => $neography->id,
                                    'rules' => $row->morphRules->filter(
                                        fn ($r) => (
                                            $r->target_type == MorphRuleTargetType::BaseInput &&
                                            $r->pattern_type == MorphRulePatternType::Transliterated &&
                                            $r->neography_id == $neography->id
                                        )
                                    )->sortBy('order')->mapWithKeys(function ($rule) {
                                        return [$rule->id => [
                                            'order' => $rule->order,
                                            'pattern' => $rule->pattern,
                                            'replacement' => $rule->replacement,
                                        ]];
                                    })->toArray(),
                                ]
                            ])->toArray(),
                            'onParticleTransliterated' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::ParticleInput && $r->pattern_type == MorphRulePatternType::Transliterated)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onParticlePhonemic' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::ParticleInput && $r->pattern_type == MorphRulePatternType::Phonemic)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onParticleNative' => $neographies->mapWithKeys(fn ($neography) => [
                                $neography->id => [
                                    'neographyId' => $neography->id,
                                    'rules' => $row->morphRules->filter(
                                        fn ($r) => (
                                            $r->target_type == MorphRuleTargetType::ParticleInput &&
                                            $r->pattern_type == MorphRulePatternType::Transliterated &&
                                            $r->neography_id == $neography->id
                                        )
                                    )->sortBy('order')->mapWithKeys(function ($rule) {
                                        return [$rule->id => [
                                            'order' => $rule->order,
                                            'pattern' => $rule->pattern,
                                            'replacement' => $rule->replacement,
                                        ]];
                                    })->toArray(),
                                ]
                            ])->toArray(),
                        ],
                    ]];
                })->toArray(),
            ]];
        })->toArray();
        $nullRows = collect($this->tables)
            ->flatMap->rows
            ->map(fn ($r) => [
                'id' => $r->id,
                'src_base' => $r->src_base,
            ])->filter(fn ($r) => $r['src_base'] === null)
            ->pluck('id')
            ->toArray();
        if (count($nullRows) !== 1) {
            $this->tableForm['baseRow'] = null;
        } else {
            $this->tableForm['baseRow'] = $nullRows[0];
        }
    }

    /**
     * Granular UI functions
     */
    function updateBaseRow(string $val): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($val) {
                $rowsCollection = collect($this->tables)->flatMap->rows;
                /**
                 * We need to first set the base row to comply with
                 * the model's logic constraints.
                 */
                if (!empty($val)) {
                    foreach ($rowsCollection as $row) {
                        if ($row->id == $val) {
                            $row->src_base = null;
                            $row->save();
                            break;
                        }
                    }
                }
                /**
                 * Now we can point all the other rows to it.
                 */
                foreach ($rowsCollection as $row) {
                    if (empty($val)) {
                        // If there is no base row, set everything to null.
                        $row->src_base = null;
                        $row->save();
                    } elseif ($row->id != $val) {
                        // If there is a base row, set all others to reference it.
                        $row->src_base = (int)$val;
                        $row->save();
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->dispatch('baserow-update-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    function updateTable(string $tableId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $tableModel = $this->findInCache('table-update-failure', [
            [
                'id' => $tableId,
                'objectType' => InflectionTable::class,
                'failMessage' => ['tableId' => [__('tollerus::error.invalid_inflection_table')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'label'        => ['type' => 'string', 'column' => 'label'],
            'visible'      => ['type' => 'boolean', 'column' => 'visible'],
            'showLabel'    => ['type' => 'boolean', 'column' => 'show_label'],
            'stack'        => ['type' => 'boolean', 'column' => 'stack'],
            'alignOnStack' => ['type' => 'boolean', 'column' => 'align_on_stack'],
            'tableFold'    => ['type' => 'boolean', 'column' => 'table_fold'],
            'rowsFold'     => ['type' => 'boolean', 'column' => 'rows_fold'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('table-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $tableModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'string':
            default:
                $tableModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $tableModel->save();
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['table.'.$propName => [__('tollerus::error.duplicate_of_unique')]]);
            } else {
                $this->dispatch('table-update-failure');
                throw $e;
            }
        }
    }
    function swapTables(string $tableId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($tableId, $neighborId) {
                $tablesCollection = collect($this->tables);
                $tableModel    = $tablesCollection->firstWhere('id', $tableId);
                $neighborModel = $tablesCollection->firstWhere('id', $neighborId);
                $oldTablePosition    = (int) $this->tableForm[$tableId]['position'];
                $oldNeighborPosition = (int) $this->tableForm[$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $tablesCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $tableModel->position = $oldNeighborPosition;
                $tableModel->save();
                $neighborModel->position = $oldTablePosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('table-swap-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    function swapRows(string $tableId, string $rowId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($tableId, $rowId, $neighborId) {
                $tableModel = collect($this->tables)->firstWhere('id', $tableId);
                $rowModel      = $tableModel->rows->firstWhere('id', $rowId);
                $neighborModel = $tableModel->rows->firstWhere('id', $neighborId);
                $oldRowPosition      = (int) $this->tableForm[$tableId]['rows'][$rowId]['position'];
                $oldNeighborPosition = (int) $this->tableForm[$tableId]['rows'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $tableModel->rows->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $rowModel->position = $oldNeighborPosition;
                $rowModel->save();
                $neighborModel->position = $oldRowPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('row-swap-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
}
