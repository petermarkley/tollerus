<div
    x-data="{
        msgs: {
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            align_on_stack: @js(__('tollerus::ui.align_on_stack')),
            align_on_stack_description: @js(__('tollerus::ui.align_on_stack_description')),
            cols_fold: @js(__('tollerus::ui.cols_fold')),
            cols_fold_description: @js(__('tollerus::ui.cols_fold_description')),
            rows_fold: @js(__('tollerus::ui.rows_fold')),
            rows_fold_description: @js(__('tollerus::ui.rows_fold_description')),
            delete_inflection_column_confirmation: @js(__('tollerus::ui.delete_inflection_column_confirmation')),
            delete_inflection_row_confirmation: @js(__('tollerus::ui.delete_inflection_row_confirmation')),
        },
        moveColumn(columnElem, columnId, neighborId) {
            let neighborElem = document.getElementById('column_' + neighborId);
            $store.reorderFunctions.swapItems(columnElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapColumns(columnId, neighborId);
            };
            columnElem.addEventListener('transitionend', onDone);
        },
        moveRow(columnId, rowElem, rowId, neighborId) {
            let neighborElem = document.getElementById('row_' + neighborId);
            $store.reorderFunctions.swapItems(rowElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapRows(columnId, rowId, neighborId);
            };
            rowElem.addEventListener('transitionend', onDone);
        },
        deleteItem(id) {
            let e = document.getElementById(id);
            if (e) {
                e.remove();
            }
        },
    }"
    @column-delete.window="deleteItem('column_'+$event.detail.columnId); $wire.deleteColumn($event.detail.columnId);"
    @row-delete.window="deleteItem('row_'+$event.detail.rowId); $wire.deleteRow($event.detail.rowId);"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ mb_ucfirst($groupName) }}</span>
            <span>{{ __('tollerus::ui.inflection_table') }}</span>
        </h1>
        <div class="flex flex-col gap-6">
            <x-tollerus::panel class="flex flex-col gap-4">
                <div class="flex flex-col items-start">
                    <x-tollerus::inputs.toggle
                        idExpression="'table_visible'"
                        model="tableForm.visible"
                        modelIsAlpine="false"
                        label="{{ __('tollerus::ui.visible') }}"
                        @change="$wire.updateTable('visible', $el.checked, id);"
                    />
                </div>
                <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                    <div><legend class="font-normal italic text-zinc-500 dark:text-zinc-500" x-text="msgs['align_on_stack_description']"></legend></div>
                    <div class="flex flex-row justify-start md:justify-end md:w-80 shrink-0 text-left md:text-right">
                        <x-tollerus::inputs.checkbox
                            idExpression="'table_align_on_stack'"
                            model="tableForm.alignOnStack"
                            modelIsAlpine="false"
                            label="{{ __('tollerus::ui.align_on_stack') }}"
                            @change="$wire.updateTable('alignOnStack', $el.checked, id);"
                        />
                    </div>
                </fieldset>
                <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                    <div><legend class="font-normal italic text-zinc-500 dark:text-zinc-500" x-text="msgs['cols_fold_description']"></legend></div>
                    <div class="flex flex-row justify-start md:justify-end md:w-80 shrink-0 text-left md:text-right">
                        <x-tollerus::inputs.checkbox
                            idExpression="'table_cols_fold'"
                            model="tableForm.colsFold"
                            modelIsAlpine="false"
                            label="{{ __('tollerus::ui.cols_fold') }}"
                            @change="$wire.updateTable('colsFold', $el.checked, id);"
                        />
                    </div>
                </fieldset>
                <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                    <div><legend class="font-normal italic text-zinc-500 dark:text-zinc-500" x-text="msgs['rows_fold_description']"></legend></div>
                    <div class="flex flex-row justify-start md:justify-end md:w-80 shrink-0 text-left md:text-right">
                        <x-tollerus::inputs.checkbox
                            idExpression="'table_rows_fold'"
                            model="tableForm.rowsFold"
                            modelIsAlpine="false"
                            label="{{ __('tollerus::ui.rows_fold') }}"
                            @change="$wire.updateTable('rowsFold', $el.checked, id);"
                        />
                    </div>
                </fieldset>
            </x-tollerus::panel>
            <h1 class="font-bold text-2xl px-6 xl:px-0">
                <span>{{ __('tollerus::ui.columns') }}</span>
            </h1>
            <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                @foreach (collect($tableForm['columns'])->sortBy('position') as $columnId => $column)
                    @php
                        $prevNeighborId = $this->getNeighborId($tableForm['columns'], $columnId, -1);
                        $nextNeighborId = $this->getNeighborId($tableForm['columns'], $columnId, +1);
                    @endphp
                    <div
                        id="column_{{ $columnId }}"
                        wire:key="column-{{ $columnId }}"
                        data-obj="column"
                        class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                        style="order: {{ $loop->index }}"
                        @transitionend="$nextTick(() => {animating=false});"
                    >
                        <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_column_up') }}"
                                x-bind:disabled="animating || {{ $this->isFirstItem($tableForm['columns'], $columnId) ? 'true' : 'false' }}"
                                @click="animating=true; moveColumn($el.closest('[data-obj=column]'), {{ $columnId }}, {{ $prevNeighborId ?? 'null' }});"
                            >
                                <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_column_up') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_column_down') }}"
                                x-bind:disabled="animating || {{ $this->isLastItem($tableForm['columns'], $columnId) ? 'true' : 'false' }}"
                                @click="animating=true; moveColumn($el.closest('[data-obj=column]'), {{ $columnId }}, {{ $nextNeighborId ?? 'null' }});"
                            >
                                <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_column_down') }}</span>
                            </x-tollerus::inputs.button>
                        </x-tollerus::panel>
                        <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                            <h2 class="flex flex-row gap-2 items-center justify-between">
                                <div class="font-bold text-xl flex flex-row gap-2 items-center">
                                    <x-tollerus::icons.columns class="h-8"/>
                                    @if (empty($column['label']))
                                        <span class="font-normal italic">{{ __('tollerus::ui.column_nameless') }}</span>
                                    @else
                                        <span>{{ $column['label'] }}</span>
                                    @endif
                                </div>
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.column')]) }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_inflection_column_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'column-delete', payload: {columnId: '{{ $columnId }}'} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.column')]) }}</span>
                                </x-tollerus::inputs.button>
                            </h2>
                            <div class="flex flex-col items-start">
                                <x-tollerus::inputs.toggle
                                    idExpression="'column_{{ $columnId }}_visible'"
                                    model="tableForm.columns.{{ $columnId }}.visible"
                                    modelIsAlpine="false"
                                    label="{{ __('tollerus::ui.visible') }}"
                                    @change="$wire.updateColumn({{ $columnId }}, 'visible', $el.checked, id);"
                                />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-tollerus::inputs.text-saveable
                                    showLabel="true"
                                    idExpression="'column_{{ $columnId }}_label'"
                                    model="tableForm.columns.{{ $columnId }}.label"
                                    fieldName="{{ __('tollerus::ui.label') }}"
                                    saveEvent="$wire.updateColumn({{ $columnId }}, 'label', prop, id);"
                                />
                                <div class="flex flex-row justify-start">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'column_{{ $columnId }}_show_label'"
                                        model="tableForm.columns.{{ $columnId }}.showLabel"
                                        modelIsAlpine="false"
                                        label="{{ __('tollerus::ui.show_label') }}"
                                        @change="$wire.updateColumn({{ $columnId }}, 'showLabel', $el.checked, id);"
                                    />
                                </div>
                            </div>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.filter />
                                    <span>{{ __('tollerus::ui.filters') }}</span>
                                </h3>
                                <div class="flex flex-col gap-2 items-start w-full">
                                    <ul class="flex flex-row flex-wrap gap-2">
                                        @foreach ($column['filters'] as $filterId => $filter)
                                            <li
                                                id="column_filter_{{ $filterId }}"
                                                wire:key="column-filter-{{ $filterId }}"
                                                class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1"
                                            >
                                                <span>{{ $filter['featureName'] }}: {{ $filter['valueName'] }}</span>
                                                <x-tollerus::inputs.button
                                                    type="inverse"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.remove_filter') }}"
                                                    @click="$wire.removeColumnFilter({{ $columnId }}, {{ $filter['valueId'] }});"
                                                    wire:loading.attr="disabled"
                                                    wire:target="removeColumnFilter"
                                                >
                                                    <x-tollerus::icons.x/>
                                                    <label class="sr-only">{{ __('tollerus::ui.remove_filter') }}</label>
                                                </x-tollerus::inputs.button>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <x-tollerus::inputs.dropdown class="relative w-full">
                                        <x-slot:button>
                                            <x-tollerus::inputs.missing-data
                                                size="small"
                                                title="{{ __('tollerus::ui.add_filter') }}"
                                                class="relative flex flex-row gap-2 justify-center items-center"
                                                @click="open=true"
                                            >
                                                <x-tollerus::icons.plus/>
                                                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_filter') }}</span>
                                            </x-tollerus::inputs.missing-data>
                                        </x-slot:button>
                                        @foreach ($features as $feature)
                                            <div
                                                wire:key="add-column-filter-{{ $feature['id'] }}"
                                                class="flex flex-col items-start"
                                            >
                                                <span class="italic opacity-50">{{ $feature['name'] }}</span>
                                                @foreach ($feature['values'] as $value)
                                                    @if (collect($column['filters'])->pluck('featureId')->contains($feature['id']))
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            size="small"
                                                            disabled
                                                            class="ml-4 line-through"
                                                        >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                    @else
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            size="small"
                                                            class="ml-4"
                                                            @click="open=false; $wire.addColumnFilter({{ $columnId }}, {{ $value['id'] }});"
                                                        >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </x-tollerus::inputs.dropdown>
                                </div>
                            </x-tollerus::pane>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.arrow-down-right />
                                    <span>{{ __('tollerus::ui.rows') }}</span>
                                </h3>
                                @if (count($column['rows']) > 0)
                                    <div class="flex flex-col gap-4 items-start" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                        @foreach (collect($column['rows'])->sortBy('position') as $rowId => $row)
                                            @php
                                                $prevNeighborId = $this->getNeighborId($column['rows'], $rowId, -1);
                                                $nextNeighborId = $this->getNeighborId($column['rows'], $rowId, +1);
                                            @endphp
                                            <div
                                                id="row_{{ $rowId }}"
                                                wire:key="row-{{ $rowId }}"
                                                data-obj="row"
                                                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                style="order: {{ $loop->index }}"
                                                @transitionend="$nextTick(() => {animating=false});"
                                            >
                                                <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_row_up') }}"
                                                        x-bind:disabled="animating || {{ $this->isFirstItem($column['rows'], $rowId) ? 'true' : 'false' }}"
                                                        @click="animating=true; moveRow({{ $columnId }}, $el.closest('[data-obj=row]'), {{ $rowId }}, {{ $prevNeighborId ?? 'null' }});"
                                                    >
                                                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_row_up') }}</span>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_row_down') }}"
                                                        x-bind:disabled="animating || {{ $this->isLastItem($column['rows'], $rowId) ? 'true' : 'false' }}"
                                                        @click="animating=true; moveRow({{ $columnId }}, $el.closest('[data-obj=row]'), {{ $rowId }}, {{ $nextNeighborId ?? 'null' }});"
                                                    >
                                                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_row_down') }}</span>
                                                    </x-tollerus::inputs.button>
                                                </x-tollerus::panel>
                                                <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none">
                                                    <div class="flex flex-row gap-4 justify-between items-start lg:items-center w-full">
                                                        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center flex-grow">
                                                            <div class="lg:w-80">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'row_{{ $rowId }}_label'"
                                                                    model="tableForm.columns.{{ $columnId }}.rows.{{ $rowId }}.label"
                                                                    fieldName="{{ __('tollerus::ui.label') }}"
                                                                    showLabel="true"
                                                                    saveEvent="$wire.updateRow({{ $columnId }}, {{ $rowId }}, 'label', prop, id);"
                                                                />
                                                            </div>
                                                            <div class="lg:w-80">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'row_{{ $rowId }}_label_brief'"
                                                                    model="tableForm.columns.{{ $columnId }}.rows.{{ $rowId }}.labelBrief"
                                                                    fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                                    showLabel="true"
                                                                    saveEvent="$wire.updateRow({{ $columnId }}, {{ $rowId }}, 'labelBrief', prop, id);"
                                                                />
                                                            </div>
                                                        </div>
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            size="small"
                                                            class="align-middle"
                                                            title="{{ __('tollerus::ui.delete_row') }}"
                                                            @click="$dispatch('open-modal', {
                                                                message: msgs['delete_inflection_row_confirmation'],
                                                                buttons: [
                                                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'row-delete', payload: {rowId: '{{ $rowId }}'} }
                                                                ]
                                                            });"
                                                        >
                                                            <x-tollerus::icons.delete/>
                                                            <label class="sr-only">{{ __('tollerus::ui.delete_row') }}</label>
                                                        </x-tollerus::inputs.button>
                                                    </div>
                                                    <div class="w-full">
                                                        <x-tollerus::inputs.text-saveable
                                                            idExpression="'row_{{ $rowId }}_label_long'"
                                                            model="tableForm.columns.{{ $columnId }}.rows.{{ $rowId }}.labelLong"
                                                            fieldName="{{ __('tollerus::ui.label_long') }}"
                                                            showLabel="true"
                                                            saveEvent="$wire.updateRow({{ $columnId }}, {{ $rowId }}, 'labelLong', prop, id);"
                                                        />
                                                    </div>
                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 w-full">
                                                        <div class="flex flex-col items-start">
                                                            <x-tollerus::inputs.checkbox
                                                                idExpression="'row_{{ $rowId }}_visible'"
                                                                model="tableForm.columns.{{ $columnId }}.rows.{{ $rowId }}.visible"
                                                                modelIsAlpine="false"
                                                                label="{{ __('tollerus::ui.visible') }}"
                                                                @change="$wire.updateRow({{ $columnId }}, {{ $rowId }}, 'visible', $el.checked, id);"
                                                            />
                                                        </div>
                                                        <div class="flex flex-row justify-start">
                                                            <x-tollerus::inputs.checkbox
                                                                idExpression="'row_{{ $rowId }}_show_label'"
                                                                model="tableForm.columns.{{ $columnId }}.rows.{{ $rowId }}.showLabel"
                                                                modelIsAlpine="false"
                                                                label="{{ __('tollerus::ui.show_label') }}"
                                                                @change="$wire.updateRow({{ $columnId }}, {{ $rowId }}, 'showLabel', $el.checked, id);"
                                                            />
                                                        </div>
                                                    </div>
                                                    <h4>{{ __('tollerus::ui.filters') }}</h4>
                                                    <div class="pl-12 flex flex-col gap-2 items-start w-full">
                                                        <ul class="flex flex-row flex-wrap gap-2">
                                                            @foreach ($row['filters'] as $filterId => $filter)
                                                                <li
                                                                    id="row_filter_{{ $filterId }}"
                                                                    wire:key="row-filter-{{ $filterId }}"
                                                                    class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1"
                                                                >
                                                                    <span>{{ $filter['featureName'] }}: {{ $filter['valueName'] }}</span>
                                                                    <x-tollerus::inputs.button
                                                                        type="inverse"
                                                                        size="small"
                                                                        class="align-middle"
                                                                        title="{{ __('tollerus::ui.remove_filter') }}"
                                                                        @click="$wire.removeRowFilter({{ $rowId }}, {{ $filter['valueId'] }});"
                                                                        wire:loading.attr="disabled"
                                                                        wire:target="removeRowFilter"
                                                                    >
                                                                        <x-tollerus::icons.x/>
                                                                        <label class="sr-only">{{ __('tollerus::ui.remove_filter') }}</label>
                                                                    </x-tollerus::inputs.button>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        <x-tollerus::inputs.dropdown class="relative w-full">
                                                            <x-slot:button>
                                                                <x-tollerus::inputs.missing-data
                                                                    size="small"
                                                                    title="{{ __('tollerus::ui.add_filter') }}"
                                                                    class="relative flex flex-row gap-2 justify-center items-center"
                                                                    @click="open=true"
                                                                >
                                                                    <x-tollerus::icons.plus/>
                                                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_filter') }}</span>
                                                                </x-tollerus::inputs.missing-data>
                                                            </x-slot:button>
                                                            @foreach ($features as $feature)
                                                                <div
                                                                    wire:key="add-row-filter-{{ $feature['id'] }}"
                                                                    class="flex flex-col items-start"
                                                                >
                                                                    <span class="italic opacity-50">{{ $feature['name'] }}</span>
                                                                    @foreach ($feature['values'] as $value)
                                                                        @if (collect($row['filters'])->pluck('featureId')->contains($feature['id']))
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4 line-through"
                                                                                disabled
                                                                            >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                                        @else
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4"
                                                                                @click="open=false; $wire.addRowFilter({{ $columnId }}, {{ $rowId }}, {{ $value['id'] }});"
                                                                            >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </x-tollerus::inputs.dropdown>
                                                    </div>
                                                    @if ($row['isBaseRow'])
                                                        <p class="flex flex-row gap-2 justify-start items-center text-zinc-500 dark:text-zinc-500 italic">
                                                            <x-tollerus::icons.bricks />
                                                            <span>{{ __('tollerus::ui.base_row_cannot_auto_inflect') }}</span>
                                                            <a href="{{ $row['inflectionsUrl'] }}">{{ __('tollerus::ui.edit_at_group_level') }}</a>
                                                        </p>
                                                    @endif
                                                    @if ($row['srcBase'] !== null)
                                                        <x-tollerus::button
                                                            type="secondary"
                                                            size="small"
                                                            href="{{ $row['autoInflectionUrl'] }}"
                                                            class="flex flex-row gap-1 items-center"
                                                        >
                                                            <x-tollerus::icons.bolt fill="currentColor" />
                                                            <span>{{ __('tollerus::ui.configure_auto_inflection') }}</span>
                                                        </x-tollerus::button>
                                                    @endif
                                                </x-tollerus::panel>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_row') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createRow({{ $columnId }});"
                                    wire:loading.attr="disabled"
                                    wire:target="createRow"
                                >
                                    <x-tollerus::icons.plus/>
                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_row') }}</span>
                                </x-tollerus::inputs.missing-data>
                            </x-tollerus::pane>
                        </x-tollerus::panel>
                    </div>
                @endforeach
            </div>
            <div class="px-6 xl:px-0">
                <x-tollerus::inputs.missing-data
                    size="medium" floating="true"
                    title="{{ __('tollerus::ui.add_column') }}"
                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                    @click="$wire.createColumn();"
                    wire:loading.attr="disabled"
                    wire:target="createColumn"
                >
                    <x-tollerus::icons.plus/>
                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_column') }}</span>
                </x-tollerus::inputs.missing-data>
            </div>
        </div>
    </div>
    <x-tollerus::modal/>
</div>
<x-tollerus::reorder-script/>
