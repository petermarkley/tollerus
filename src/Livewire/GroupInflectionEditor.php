<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class GroupInflectionEditor extends Component
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
    public array $baseRowOpts = [];

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
        return view('tollerus::livewire.group-inflection-editor', [
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
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $wordClassGroup): void
    {
        $this->language = $language;
        $this->group = $wordClassGroup;
        $this->refreshTableForm();
    }

    public function refreshTableForm(): void
    {
        $this->tables = $this->group->inflectionTables->sortBy('position')->all();
        foreach ($this->tables as $table) {
            $table->loadMissing([
                'columns.rows',
            ]);
        }
        $this->baseRowOpts = collect($this->tables)
            ->sortBy('position')
            ->map(fn ($t) => $t->columns->sortBy('position'))
            ->flatten(1)
            ->map(fn ($column) => [
                'columnId' => $column->id,
                'label' => $column->label,
                'rows' => $column->rows->sortBy('position')->map(fn ($row) => [
                    'rowId' => $row->id,
                    'label' => $row->label,
                ])->values()->toArray(),
            ])->values()->toArray();
        $this->tableForm = collect($this->tables)->mapWithKeys(function ($table) {
            return [$table->id => [
                'position' => $table->position,
                'columns' => $table->columns->sortBy('position')->map(function ($column) {
                    return [
                        'columnId' => $column->id,
                        'label' => $column->label,
                        'position' => $column->position,
                        'rows' => $column->rows->sortBy('position')->map(function ($row) {
                            return [
                                'rowId' => $row->id,
                                'label' => $row->label,
                                'labelBrief' => $row->label_brief,
                                'labelLong' => $row->label_long,
                                'position' => $row->position,
                                'srcBase' => $row->src_base,
                            ];
                        })->values()->toArray(),
                    ];
                })->values()->toArray(),
                'tableEditUrl' => route('tollerus.admin.languages.inflections.table.edit', [
                    'language' => $this->language,
                    'wordClassGroup' => $this->group,
                    'inflectionTable' => $table,
                ]),
            ]];
        })->toArray();
        $nullRows = collect($this->tables)
            ->flatMap->columns
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
     * Granular CRUD-type functions
     */
    public function updateBaseRow(string $val): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($val) {
                $rowsCollection = collect($this->tables)
                    ->flatMap->columns
                    ->flatMap->rows;
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
            $table = $this->group->inflectionTables()->create([
                'position' => $nextPosition,
                'align_on_stack' => false,
                'cols_fold' => false,
                'rows_fold' => false,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('table-add-failure');
            throw $e;
        }
        $this->refreshTableForm();
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
}
