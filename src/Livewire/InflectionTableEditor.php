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
use PeterMarkley\Tollerus\Models\InflectionTableRow;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableRowFilter;
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
    // UI display properties
    #[Locked] public array $features = [];

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
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'grammar',
                    ]), 'text' => $this->language->name],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $group): void
    {
        $this->language = $language;
        $this->group = $group;
        $this->refreshTableForm();
    }

    /**
     * Refresh UI input layer from database
     */
    public function refreshTableForm(): void
    {
        $this->tables = $this->group->inflectionTables->sortBy('position')->all();
        $this->group->loadMissing([
            'features.featureValues',
        ]);
        $this->features = $this->group->features->sortBy('name')->map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'values' => $f->featureValues->sortBy('name')->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
            ])->toArray(),
        ])->toArray();
        foreach ($this->tables as $table) {
            $table->loadMissing([
                'filterValues.feature',
                'rows.filterValues.feature',
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
                        'label'      => $row->label,
                        'labelBrief' => $row->label_brief,
                        'labelLong'  => $row->label_long,
                        'visible'    => (bool)($row->visible),
                        'showLabel'  => (bool)($row->show_label),
                        'position'   => $row->position,
                        'srcBase'    => $row->src_base,
                        'filters' => $row->filterValues->mapWithKeys(function ($filterValue) {
                            return [$filterValue->id => [
                                'featureId'   => $filterValue->feature->id,
                                'featureName' => $filterValue->feature->name,
                                'valueId'     => $filterValue->id,
                                'valueName'   => $filterValue->name,
                            ]];
                        })->toArray(),
                        'autoInflectionUrl' => route('tollerus.admin.languages.auto-inflection', [
                            'language' => $this->language,
                            'group' => $this->group,
                            'row' => $row,
                        ]),
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
    public function updateBaseRow(string $val): void
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
    public function createTable(): void
    {
        try {
            $nextPosition = collect($this->tables)->max('position') + 1;
            $table = CreateWithUniqueName::handle(
                startNum: $this->group->inflectionTables()->count(),
                createFunc: fn ($tryName) => $this->group->inflectionTables()->create([
                    'label' => $tryName,
                    'position' => $nextPosition,
                    'stack' => false,
                    'align_on_stack' => false,
                    'table_fold' => false,
                    'rows_fold' => false,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('table-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function updateTable(string $tableId, string $propName, string $propVal, ?string $domId = ''): void
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
                throw \Illuminate\Validation\ValidationException::withMessages(['table.'.$propName => [__('tollerus::error.duplicate_of_unique_per_group')]]);
            } else {
                $this->dispatch('table-update-failure');
                throw $e;
            }
        }
    }
    public function deleteTable(string $tableId): void
    {
        InflectionTable::findOrFail((int)$tableId)->delete();
        $this->refreshTableForm();
    }
    public function swapTables(string $tableId, string $neighborId): void
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
    public function addTableFilter(string $tableId, string $valueId): void
    {
        // Find models
        $tableModel = $this->findInCache('table-filter-add-failure', [
            [
                'id' => $tableId,
                'objectType' => InflectionTable::class,
                'failMessage' => ['tableId' => [__('tollerus::error.invalid_inflection_table')]],
            ],
        ]);
        $valueModel = FeatureValue::find($valueId);
        if (!($valueModel instanceof FeatureValue)) {
            $this->dispatch('table-filter-add-failure');
            return;
        }
        // Create pivot row
        try {
            (new InflectionTableFilter([
                'inflect_table_id' => $tableModel->id,
                'feature_id' => $valueModel->feature_id,
                'value_id' => $valueModel->id,
            ]))->save();
        } catch (\Throwable $e) {
            $this->dispatch('table-filter-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function removeTableFilter(string $tableId, string $valueId): void
    {
        InflectionTableFilter::where('inflect_table_id', (int)$tableId)
            ->where('value_id', (int)$valueId)
            ->firstOrFail()
            ->delete();
        $this->refreshTableForm();
    }
    public function createRow(string $tableId): void
    {
        // Find model
        $tableModel = $this->findInCache('row-add-failure', [
            [
                'id' => $tableId,
                'objectType' => InflectionTable::class,
                'failMessage' => ['tableId' => [__('tollerus::error.invalid_inflection_table')]],
            ],
        ]);
        // Create row
        try {
            $nextPosition = $tableModel->rows->max('position') + 1;
            $row = CreateWithUniqueName::handle(
                startNum: $tableModel->rows()->count(),
                createFunc: fn ($tryName) => $tableModel->rows()->create([
                    'label' => $tryName,
                    'position' => $nextPosition,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('row-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function updateRow(string $tableId, string $rowId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $rowModel = $this->findInCache('row-update-failure', [
            [
                'id' => $tableId,
                'objectType' => InflectionTable::class,
                'failMessage' => ['tableId' => [__('tollerus::error.invalid_inflection_table')]],
                'relation' => 'rows',
            ],
            [
                'id' => $rowId,
                'objectType' => InflectionTableRow::class,
                'failMessage' => ['rowId' => [__('tollerus::error.invalid_inflection_table_row')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'label'      => ['type' => 'string', 'column' => 'label'],
            'labelBrief' => ['type' => 'string', 'column' => 'label_brief'],
            'labelLong'  => ['type' => 'string', 'column' => 'label_long'],
            'visible'    => ['type' => 'boolean', 'column' => 'visible'],
            'showLabel'  => ['type' => 'boolean', 'column' => 'show_label'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('row-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $rowModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'string':
            default:
                $rowModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $rowModel->save();
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['row.'.$propName => [__('tollerus::error.duplicate_of_row')]]);
            } else {
                $this->dispatch('row-update-failure');
                throw $e;
            }
        }
    }
    public function deleteRow(string $rowId): void
    {
        InflectionTableRow::findOrFail((int)$rowId)->delete();
        $this->refreshTableForm();
    }
    public function swapRows(string $tableId, string $rowId, string $neighborId): void
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
    public function addRowFilter(string $tableId, string $rowId, string $valueId): void
    {
        // Find models
        $rowModel = $this->findInCache('row-filter-add-failure', [
            [
                'id' => $tableId,
                'objectType' => InflectionTable::class,
                'failMessage' => ['tableId' => [__('tollerus::error.invalid_inflection_table')]],
                'relation' => 'rows',
            ],
            [
                'id' => $rowId,
                'objectType' => InflectionTableRow::class,
                'failMessage' => ['rowId' => [__('tollerus::error.invalid_inflection_table_row')]],
            ],
        ]);
        $valueModel = FeatureValue::find($valueId);
        if (!($valueModel instanceof FeatureValue)) {
            $this->dispatch('row-filter-add-failure');
            return;
        }
        // Create pivot row
        try {
            (new InflectionTableRowFilter([
                'inflect_table_row_id' => $rowModel->id,
                'feature_id' => $valueModel->feature_id,
                'value_id' => $valueModel->id,
            ]))->save();
        } catch (\Throwable $e) {
            $this->dispatch('row-filter-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function removeRowFilter(string $rowId, string $valueId): void
    {
        InflectionTableRowFilter::where('inflect_table_row_id', (int)$rowId)
            ->where('value_id', (int)$valueId)
            ->firstOrFail()
            ->delete();
        $this->refreshTableForm();
    }
}
