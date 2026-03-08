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
use PeterMarkley\Tollerus\Models\InflectionColumn;
use PeterMarkley\Tollerus\Models\InflectionRow;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\InflectionColumnFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionRowFilter;
use PeterMarkley\Tollerus\Traits\HasOrderedObjects;

class InflectionTableEditor extends Component
{
    use HasOrderedObjects;
    // Models
    #[Locked] public Language $language;
    #[Locked] public WordClassGroup $group;
    #[Locked] public InflectionTable $table;
    #[Locked] public array $columns;
    // UI input layer
    public array $tableForm = [];
    // UI display properties
    #[Locked] public array $features = [];
    #[Locked] public $baseRow = null;

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
        $pageTitle = $this->language->name . ': ' . mb_ucfirst($groupName) . ': ' . __('tollerus::ui.inflection_table');
        return view('tollerus::livewire.inflection-table-editor', [
                'groupName' => $groupName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layouts.admin', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'grammar',
                    ]), 'text' => $this->language->name],
                    ['href' => route('tollerus.admin.languages.inflections.edit', [
                        'language' => $this->language->id,
                        'wordClassGroup' => $this->group->id,
                    ]), 'text' => __('tollerus::ui.inflections')],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $wordClassGroup, InflectionTable $inflectionTable): void
    {
        $this->language = $language;
        $this->group = $wordClassGroup;
        $this->table = $inflectionTable;
        $this->refreshTableForm();
    }

    /**
     * Refresh UI input layer from database
     */
    public function refreshTableForm(): void
    {
        $this->columns = $this->table->columns->sortBy('position')->all();
        $this->group->loadMissing([
            'features.featureValues',
            'inflectionTables.columns.rows',
        ]);
        $this->features = $this->group->features->sortBy('name')->map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'values' => $f->featureValues->sortBy('name')->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
            ])->toArray(),
        ])->toArray();
        foreach ($this->columns as $column) {
            $column->loadMissing([
                'filterValues.feature',
                'rows.filterValues.feature',
            ]);
        }
        $nullRows = collect($this->group->inflectionTables)
            ->flatMap->columns
            ->flatMap->rows
            ->map(fn ($r) => [
                'id' => $r->id,
                'src_base' => $r->src_base,
            ])->filter(fn ($r) => $r['src_base'] === null)
            ->pluck('id')
            ->toArray();
        if (count($nullRows) !== 1) {
            $this->baseRow = null;
        } else {
            $this->baseRow = $nullRows[0];
        }
        $this->tableForm = [
            'visible'      => (bool)($this->table->visible),
            'position'     => $this->table->position,
            'alignOnStack' => (bool)($this->table->align_on_stack),
            'colsFold'     => (bool)($this->table->cols_fold),
            'rowsFold'     => (bool)($this->table->rows_fold),
            'columns' => collect($this->columns)->mapWithKeys(function ($column) {
                return [$column->id => [
                    'label'        => $column->label,
                    'visible'      => (bool)($column->visible),
                    'showLabel'    => (bool)($column->show_label),
                    'position'     => $column->position,
                    'filters' => $column->filterValues->mapWithKeys(function ($filterValue) {
                        return [$filterValue->id => [
                            'featureId'   => $filterValue->feature->id,
                            'featureName' => $filterValue->feature->name,
                            'valueId'     => $filterValue->id,
                            'valueName'   => $filterValue->name,
                        ]];
                    })->toArray(),
                    'rows' => $column->rows->sortBy('position')->mapWithKeys(function ($row) {
                        return [$row->id => [
                            'label'      => $row->label,
                            'labelBrief' => $row->label_brief,
                            'labelLong'  => $row->label_long,
                            'visible'    => (bool)($row->visible),
                            'showLabel'  => (bool)($row->show_label),
                            'position'   => $row->position,
                            'srcBase'    => $row->src_base,
                            'isBaseRow'  => $row->id === $this->baseRow,
                            'inflectionsUrl' => route('tollerus.admin.languages.inflections.edit', [
                                'language' => $this->language->id,
                                'wordClassGroup' => $this->group->id,
                            ]),
                            'filters' => $row->filterValues->mapWithKeys(function ($filterValue) {
                                return [$filterValue->id => [
                                    'featureId'   => $filterValue->feature->id,
                                    'featureName' => $filterValue->feature->name,
                                    'valueId'     => $filterValue->id,
                                    'valueName'   => $filterValue->name,
                                ]];
                            })->toArray(),
                            'autoInflectionUrl' => route('tollerus.admin.languages.inflections.table.auto-inflection', [
                                'language' => $this->language,
                                'wordClassGroup' => $this->group,
                                'inflectionTable' => $this->table,
                                'row' => $row,
                            ]),
                        ]];
                    })->toArray(),
                ]];
            })->toArray(),
        ];
    }

    /**
     * Granular UI functions
     */
    public function updateTable(string $propName, string $propVal, ?string $domId = ''): void
    {
        // $propName whitelist
        $allowedPropData = [
            'visible'      => ['type' => 'boolean', 'column' => 'visible'],
            'alignOnStack' => ['type' => 'boolean', 'column' => 'align_on_stack'],
            'colsFold'     => ['type' => 'boolean', 'column' => 'cols_fold'],
            'rowsFold'     => ['type' => 'boolean', 'column' => 'rows_fold'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('column-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $this->table[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'string':
            default:
                $this->table[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $this->table->save();
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            $this->dispatch('table-update-failure');
            throw $e;
        }
    }
    public function createColumn(): void
    {
        try {
            $nextPosition = collect($this->columns)->max('position') + 1;
            $column = CreateWithUniqueName::handle(
                startNum: $this->table->columns()->count(),
                createFunc: fn ($tryName) => $this->table->columns()->create([
                    'label' => $tryName,
                    'position' => $nextPosition,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('column-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function updateColumn(string $columnId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $columnModel = InflectionColumn::find($columnId);
        if (!($columnModel instanceof InflectionColumn)) {
            $this->dispatch('column-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['columnId' => [__('tollerus::error.invalid_inflection_column')]]);
        }
        // $propName whitelist
        $allowedPropData = [
            'label'        => ['type' => 'string', 'column' => 'label'],
            'visible'      => ['type' => 'boolean', 'column' => 'visible'],
            'showLabel'    => ['type' => 'boolean', 'column' => 'show_label'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('column-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $columnModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'string':
            default:
                $columnModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $columnModel->save();
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['column.'.$propName => [__('tollerus::error.duplicate_of_unique_per_group')]]);
            } else {
                $this->dispatch('column-update-failure');
                throw $e;
            }
        }
    }
    public function deleteColumn(string $columnId): void
    {
        InflectionColumn::findOrFail((int)$columnId)->delete();
        $this->refreshTableForm();
    }
    public function swapColumns(string $columnId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($columnId, $neighborId) {
                $columnsCollection = collect($this->columns);
                $columnsModel  = $columnsCollection->firstWhere('id', $columnId);
                $neighborModel = $columnsCollection->firstWhere('id', $neighborId);
                $oldColumnPosition   = (int) $this->tableForm['columns'][$columnId]['position'];
                $oldNeighborPosition = (int) $this->tableForm['columns'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $columnsCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $columnsModel->position = $oldNeighborPosition;
                $columnsModel->save();
                $neighborModel->position = $oldColumnPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('column-swap-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function addColumnFilter(string $columnId, string $valueId): void
    {
        // Find models
        $columnModel = InflectionColumn::find($columnId);
        if (!($columnModel instanceof InflectionColumn)) {
            $this->dispatch('column-filter-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['columnId' => [__('tollerus::error.invalid_inflection_column')]]);
        }
        $valueModel = FeatureValue::find($valueId);
        if (!($valueModel instanceof FeatureValue)) {
            $this->dispatch('column-filter-add-failure');
            return;
        }
        // Create pivot row
        try {
            (new InflectionColumnFilter([
                'inflect_column_id' => $columnModel->id,
                'feature_id' => $valueModel->feature_id,
                'value_id' => $valueModel->id,
            ]))->save();
        } catch (\Throwable $e) {
            $this->dispatch('column-filter-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function removeColumnFilter(string $columnId, string $valueId): void
    {
        InflectionColumnFilter::where('inflect_column_id', (int)$columnId)
            ->where('value_id', (int)$valueId)
            ->firstOrFail()
            ->delete();
        $this->refreshTableForm();
    }
    public function createRow(string $columnId): void
    {
        // Find model
        $columnModel = InflectionColumn::find($columnId);
        if (!($columnModel instanceof InflectionColumn)) {
            $this->dispatch('row-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['columnId' => [__('tollerus::error.invalid_inflection_column')]]);
        }
        // Create row
        try {
            $nextPosition = $columnModel->rows->max('position') + 1;
            $row = CreateWithUniqueName::handle(
                startNum: $columnModel->rows()->count(),
                createFunc: fn ($tryName) => $columnModel->rows()->create([
                    'label' => $tryName,
                    'position' => $nextPosition,
                    'src_base' => $this->baseRow,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('row-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
    }
    public function updateRow(string $columnId, string $rowId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $rowModel = InflectionRow::find($rowId);
        if (!($rowModel instanceof InflectionRow)) {
            $this->dispatch('row-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['rowId' => [__('tollerus::error.invalid_inflection_row')]]);
        }
        // $propName whitelist
        $allowedPropData = [
            'label'      => ['type' => 'string', 'column' => 'label', 'nullable' => false],
            'labelBrief' => ['type' => 'string', 'column' => 'label_brief', 'nullable' => true],
            'labelLong'  => ['type' => 'string', 'column' => 'label_long', 'nullable' => true],
            'visible'    => ['type' => 'boolean', 'column' => 'visible', 'nullable' => false],
            'showLabel'  => ['type' => 'boolean', 'column' => 'show_label', 'nullable' => false],
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
                if ($allowedPropData[$propName]['nullable'] && mb_strlen($propVal)==0) {
                    $propVal = null;
                }
                $rowModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $rowModel->save();
            $this->dispatch('text-save-success', id: $domId);
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
        InflectionRow::findOrFail((int)$rowId)->delete();
        $this->refreshTableForm();
    }
    public function swapRows(string $columnId, string $rowId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($columnId, $rowId, $neighborId) {
                $columnModel = collect($this->columns)->firstWhere('id', $columnId);
                $rowModel      = $columnModel->rows->firstWhere('id', $rowId);
                $neighborModel = $columnModel->rows->firstWhere('id', $neighborId);
                $oldRowPosition      = (int) $this->tableForm['columns'][$columnId]['rows'][$rowId]['position'];
                $oldNeighborPosition = (int) $this->tableForm['columns'][$columnId]['rows'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $columnModel->rows->min('position');
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
    public function addRowFilter(string $columnId, string $rowId, string $valueId): void
    {
        // Find models
        $rowModel = InflectionRow::find($rowId);
        if (!($rowModel instanceof InflectionRow)) {
            $this->dispatch('row-filter-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['rowId' => [__('tollerus::error.invalid_inflection_row')]]);
        }
        $valueModel = FeatureValue::find($valueId);
        if (!($valueModel instanceof FeatureValue)) {
            $this->dispatch('row-filter-add-failure');
            return;
        }
        // Create pivot row
        try {
            (new InflectionRowFilter([
                'inflect_row_id' => $rowModel->id,
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
        InflectionRowFilter::where('inflect_row_id', (int)$rowId)
            ->where('value_id', (int)$valueId)
            ->firstOrFail()
            ->delete();
        $this->refreshTableForm();
    }
}
