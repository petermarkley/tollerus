<div
    x-data="{
        msgs: {
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            inflection_table_nameless: @js(__('tollerus::ui.inflection_table_nameless')),
            stack: @js(__('tollerus::ui.stack')),
            stack_description: @js(__('tollerus::ui.stack_description')),
            align_on_stack: @js(__('tollerus::ui.align_on_stack')),
            align_on_stack_description: @js(__('tollerus::ui.align_on_stack_description')),
            table_fold: @js(__('tollerus::ui.table_fold')),
            table_fold_description: @js(__('tollerus::ui.table_fold_description')),
            rows_fold: @js(__('tollerus::ui.rows_fold')),
            rows_fold_description: @js(__('tollerus::ui.rows_fold_description')),
            delete_inflection_table_confirmation: @js(__('tollerus::ui.delete_inflection_table_confirmation')),
            delete_inflection_row_confirmation: @js(__('tollerus::ui.delete_inflection_row_confirmation')),
        },
        tableForm: $wire.entangle('tableForm'),
        features: $wire.entangle('features'),
        get tablesFiltered() {
            return Object.fromEntries(Object.entries(this.tableForm).filter(([k, v]) => !isNaN(k)));
        },
        moveTable(tableElem, tableId, dir) {
            neighborId = $store.reorderFunctions.getNeighborId(this.tablesFiltered, tableId, dir);
            if (neighborId === null) {
                return;
            }
            neighborElem = document.getElementById('table_' + neighborId);
            $store.reorderFunctions.swapItems(tableElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapTables(tableId, neighborId);
            };
            tableElem.addEventListener('transitionend', onDone);
        },
        moveRow(tableId, rowElem, rowId, dir) {
            neighborId = $store.reorderFunctions.getNeighborId(this.tableForm[tableId].rows, rowId, dir);
            if (neighborId === null) {
                return;
            }
            neighborElem = document.getElementById('row_' + neighborId);
            $store.reorderFunctions.swapItems(rowElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapRows(tableId, rowId, neighborId);
            };
            rowElem.addEventListener('transitionend', onDone);
        },
    }"
    @table-delete.window="$wire.deleteTable($event.detail.tableId);"
    @row-delete.window="$wire.deleteRow($event.detail.rowId);"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ mb_ucfirst($groupName) }}</span>
            <span>{{ __('tollerus::ui.inflection_tables') }}</span>
        </h1>
        <div class="flex flex-col gap-6">
            <x-tollerus::panel>
                <x-tollerus::inputs.select
                    id="base_row"
                    label="{{ __('tollerus::ui.base_row') }}"
                    model="tableForm.baseRow"
                    @change="$wire.updateBaseRow($el.value);"
                >
                    <option value="" class="cursor-pointer italic" x-bind:selected="tableForm.baseRow===null || tableForm.baseRow===''">{{ __('tollerus::ui.none') }}</option>
                    <template x-for="(table, tableId) in tablesFiltered">
                        <optgroup x-bind:label="table.label">
                            <template x-for="(row, rowId) in table.rows">
                                <option x-bind:value="rowId" class="cursor-pointer" x-text="row.label" x-bind:selected="tableForm.baseRow==rowId"></option>
                            </template>
                        </optgroup>
                    </template>
                </x-tollerus::inputs.select>
            </x-tollerus::panel>
            <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                <template x-for="(table, tableId) in tablesFiltered">
                    <div
                        x-bind:id="'table_' + tableId"
                        data-obj="table"
                        class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                        x-bind:style="'order: '+table.position"
                        @transitionend="$nextTick(() => {animating=false});"
                    >
                        <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_inflection_table_up') }}"
                                x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(tablesFiltered, tableId)"
                                @click="animating=true; moveTable($el.closest('[data-obj=&quot;table&quot;]'), tableId, -1);"
                            >
                                <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_inflection_table_up') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_inflection_table_down') }}"
                                x-bind:disabled="animating || $store.reorderFunctions.isLastItem(tablesFiltered, tableId)"
                                @click="animating=true; moveTable($el.closest('[data-obj=&quot;table&quot;]'), tableId, +1);"
                            >
                                <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_inflection_table_down') }}</span>
                            </x-tollerus::inputs.button>
                        </x-tollerus::panel>
                        <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                            <h2 class="flex flex-row gap-2 items-center justify-between">
                                <div class="font-bold text-xl flex flex-row gap-2 items-center">
                                    <x-tollerus::icons.table class="h-8"/>
                                    <span x-text="table.label.length>0 ? table.label : msgs['inflection_table_nameless']" x-bind:class="{ 'font-normal italic': table.label.length==0 }"></span>
                                </div>
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_inflection_table_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'table-delete', payload: {tableId: tableId} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}</span>
                                </x-tollerus::inputs.button>
                            </h2>
                            <div class="flex flex-col items-start">
                                <x-tollerus::inputs.toggle
                                    idExpression="'table_' + tableId + '_visible'"
                                    model="table.visible"
                                    modelIsAlpine="true"
                                    label="{{ __('tollerus::ui.visible') }}"
                                    @change="$wire.updateTable(tableId, 'visible', $el.checked, id);"
                                />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-tollerus::inputs.text-saveable
                                    showLabel="true"
                                    idExpression="'table_' + tableId + '_label'"
                                    model="table.label"
                                    fieldName="{{ __('tollerus::ui.label') }}"
                                    saveEvent="$wire.updateTable(tableId, 'label', document.getElementById(id).value, id);"
                                />
                                <div class="flex flex-row justify-start">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'table_' + tableId + '_show_label'"
                                        model="table.showLabel"
                                        modelIsAlpine="true"
                                        label="{{ __('tollerus::ui.show_label') }}"
                                        @change="$wire.updateTable(tableId, 'showLabel', $el.checked, id);"
                                    />
                                </div>
                            </div>
                            <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                                <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['stack_description']"></legend></div>
                                <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'table_' + tableId + '_stack'"
                                        model="table.stack"
                                        modelIsAlpine="true"
                                        label="{{ __('tollerus::ui.stack') }}"
                                        @change="$wire.updateTable(tableId, 'stack', $el.checked, id);"
                                    />
                                </div>
                            </fieldset>
                            <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                                <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['align_on_stack_description']"></legend></div>
                                <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'table_' + tableId + '_align_on_stack'"
                                        model="table.alignOnStack"
                                        modelIsAlpine="true"
                                        label="{{ __('tollerus::ui.align_on_stack') }}"
                                        @change="$wire.updateTable(tableId, 'alignOnStack', $el.checked, id);"
                                    />
                                </div>
                            </fieldset>
                            <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                                <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['table_fold_description']"></legend></div>
                                <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'table_' + tableId + '_table_fold'"
                                        model="table.tableFold"
                                        modelIsAlpine="true"
                                        label="{{ __('tollerus::ui.table_fold') }}"
                                        @change="$wire.updateTable(tableId, 'tableFold', $el.checked, id);"
                                    />
                                </div>
                            </fieldset>
                            <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                                <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['rows_fold_description']"></legend></div>
                                <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                                    <x-tollerus::inputs.checkbox
                                        idExpression="'table_' + tableId + '_rows_fold'"
                                        model="table.rowsFold"
                                        modelIsAlpine="true"
                                        label="{{ __('tollerus::ui.rows_fold') }}"
                                        @change="$wire.updateTable(tableId, 'rowsFold', $el.checked, id);"
                                    />
                                </div>
                            </fieldset>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.filter />
                                    <span>{{ __('tollerus::ui.filters') }}</span>
                                </h3>
                                <div class="flex flex-col gap-2 items-start w-full">
                                    <ul class="flex flex-row flex-wrap gap-2">
                                        <template x-for="(filter, filterId) in table.filters">
                                            <li class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1">
                                                <span x-text="filter.featureName + ': ' + filter.valueName"></span>
                                                <x-tollerus::inputs.button
                                                    type="inverse"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.remove_filter') }}"
                                                >
                                                    <x-tollerus::icons.x/>
                                                    <label class="sr-only">{{ __('tollerus::ui.remove_filter') }}</label>
                                                </x-tollerus::inputs.button>
                                            </li>
                                        </template>
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
                                        <template x-for="feature in features">
                                            <div class="flex flex-col items-start">
                                                <span x-text="feature.name" class="italic opacity-50"></span>
                                                <template x-for="value in feature.values">
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        size="small"
                                                        x-bind:class="{'ml-4': true, 'line-through': Object.values(table.filters).map((f)=>f.featureId).includes(feature.id)}"
                                                        x-bind:disabled="Object.values(table.filters).map((f)=>f.featureId).includes(feature.id);"
                                                        x-text="value.name"
                                                    />
                                                </template>
                                            </div>
                                        </template>
                                    </x-tollerus::inputs.dropdown>
                                </div>
                            </x-tollerus::pane>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.arrow-down-right />
                                    <span>{{ __('tollerus::ui.rows') }}</span>
                                </h3>
                                <template x-if="Object.keys(table.rows).length > 0">
                                    <div class="flex flex-col gap-4 items-start" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                        <template x-for="(row, rowId) in table.rows">
                                            <div
                                                x-bind:id="'row_' + rowId"
                                                data-obj="row"
                                                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                x-bind:style="'order: '+row.position"
                                                @transitionend="$nextTick(() => {animating=false});"
                                            >
                                                <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_row_up') }}"
                                                        x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(table.rows, rowId)"
                                                        @click="animating=true; moveRow(tableId, $el.closest('[data-obj=&quot;row&quot;]'), rowId, -1);"
                                                    >
                                                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_row_up') }}</span>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_row_down') }}"
                                                        x-bind:disabled="animating || $store.reorderFunctions.isLastItem(table.rows, rowId)"
                                                        @click="animating=true; moveRow(tableId, $el.closest('[data-obj=&quot;row&quot;]'), rowId, +1);"
                                                    >
                                                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_row_down') }}</span>
                                                    </x-tollerus::inputs.button>
                                                </x-tollerus::panel>
                                                <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none">
                                                    <div class="flex flex-row gap-4 justify-between items-start md:items-center">
                                                        <div class="flex flex-col md:flex-row gap-4 items-center">
                                                            <div class="w-80">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'row_' + rowId + '_label'"
                                                                    model="row.label"
                                                                    fieldName="{{ __('tollerus::ui.label') }}"
                                                                    showLabel="true" />
                                                            </div>
                                                            <div class="w-80">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'row_' + rowId + '_label_brief'"
                                                                    model="row.labelBrief"
                                                                    fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                                    showLabel="true" />
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
                                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'row-delete', payload: {rowId: rowId} }
                                                                ]
                                                            });"
                                                        >
                                                            <x-tollerus::icons.delete/>
                                                            <label class="sr-only">{{ __('tollerus::ui.delete_row') }}</label>
                                                        </x-tollerus::inputs.button>
                                                    </div>
                                                    <div class="w-full">
                                                        <x-tollerus::inputs.text-saveable
                                                            idExpression="'row_' + rowId + '_label_long'"
                                                            model="row.labelLong"
                                                            fieldName="{{ __('tollerus::ui.label_long') }}"
                                                            showLabel="true" />
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                                                        <div class="flex flex-col items-start">
                                                            <x-tollerus::inputs.checkbox idExpression="'row_' + rowId + '_visible'" model="row.visible" modelIsAlpine="true" label="{{ __('tollerus::ui.visible') }}" />
                                                        </div>
                                                        <div class="flex flex-row justify-start">
                                                            <x-tollerus::inputs.checkbox idExpression="'row_' + rowId + '_show_label'" model="row.showLabel" modelIsAlpine="true" label="{{ __('tollerus::ui.show_label') }}" />
                                                        </div>
                                                    </div>
                                                    <h4>{{ __('tollerus::ui.filters') }}</h4>
                                                    <div class="pl-12 flex flex-col gap-2 items-start w-full">
                                                        <ul class="flex flex-row flex-wrap gap-2">
                                                            <template x-for="(filter, filterId) in row.filters">
                                                                <li class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1">
                                                                    <span x-text="filter.featureName + ': ' + filter.valueName"></span>
                                                                    <x-tollerus::inputs.button
                                                                        type="inverse"
                                                                        size="small"
                                                                        class="align-middle"
                                                                        title="{{ __('tollerus::ui.remove_filter') }}"
                                                                    >
                                                                        <x-tollerus::icons.x/>
                                                                        <label class="sr-only">{{ __('tollerus::ui.remove_filter') }}</label>
                                                                    </x-tollerus::inputs.button>
                                                                </li>
                                                            </template>
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
                                                            <template x-for="feature in features">
                                                                <div class="flex flex-col items-start">
                                                                    <span x-text="feature.name" class="italic opacity-50"></span>
                                                                    <template x-for="value in feature.values">
                                                                        <x-tollerus::inputs.button
                                                                            type="inverse"
                                                                            size="small"
                                                                            x-bind:class="{'ml-4': true, 'line-through': Object.values(row.filters).map((f)=>f.featureId).includes(feature.id)}"
                                                                            x-bind:disabled="Object.values(row.filters).map((f)=>f.featureId).includes(feature.id);"
                                                                            x-text="value.name"
                                                                        />
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </x-tollerus::inputs.dropdown>
                                                    </div>
                                                </x-tollerus::panel>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_row') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createRow(tableId);"
                                    wire:loading.attr="disabled"
                                    wire:target="createRow"
                                >
                                    <x-tollerus::icons.plus/>
                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_row') }}</span>
                                </x-tollerus::inputs.missing-data>
                            </x-tollerus::pane>
                        </x-tollerus::panel>
                    </div>
                </template>
            </div>
            <x-tollerus::inputs.missing-data
                size="medium"
                title="{{ __('tollerus::ui.add_inflection_table') }}"
                class="relative flex flex-row gap-2 justify-center items-center w-full"
                @click="$wire.createTable();"
                wire:loading.attr="disabled"
                wire:target="createTable"
            >
                <x-tollerus::icons.plus/>
                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_inflection_table') }}</span>
            </x-tollerus::inputs.missing-data>
        </div>
    </div>
    <x-tollerus::modal/>
</div>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('reorderFunctions', {
        isFirstItem(parentObj, itemId) {
            let lowest = null;
            for (id in parentObj) {
                if (lowest === null || parentObj[id].position < lowest) {
                    lowest = parentObj[id].position;
                }
            }
            return (parentObj[itemId].position == lowest);
        },
        isLastItem(parentObj, itemId) {
            let highest = null;
            for (id in parentObj) {
                if (highest === null || parentObj[id].position > highest) {
                    highest = parentObj[id].position;
                }
            }
            return (parentObj[itemId].position == highest);
        },
        getNeighborId(parentObj, itemId, dir) {
            // Normalize input
            if (dir == 0) {
                return null;
            }
            dir = Math.round(dir / Math.abs(dir));
            // Get sorted numeric arrays
            let itemsNumeric = [];
            for (id in parentObj) {
                itemsNumeric.push({id: id, position: parentObj[id].position});
            }
            itemsNumeric.sort((a, b) => a.position - b.position);
            idsNumeric = itemsNumeric.map(item => item.id);
            // Get numeric indices
            itemIndex = idsNumeric.indexOf(itemId);
            if (itemIndex < 0) {
                return null;
            }
            neighborIndex = itemIndex + dir;
            if (neighborIndex < 0 || neighborIndex >= itemsNumeric.length) {
                return null;
            }
            // Return result
            return idsNumeric[neighborIndex];
        },
        swapItems(itemElem, neighborElem) {
            // Measure
            itemRect = itemElem.getBoundingClientRect();
            neighborRect = neighborElem.getBoundingClientRect();
            // Calculate
            if (itemRect.y > neighborRect.y) {
                // Item is moving upward
                itemMove = neighborRect.y - itemRect.y;
                gap = Math.abs(itemMove) - neighborRect.height;
                neighborMove = itemRect.height + gap;
            } else {
                // Item is moving downward
                neighborMove = itemRect.y - neighborRect.y;
                gap = Math.abs(neighborMove) - itemRect.height;
                itemMove = neighborRect.height + gap;
            }
            // Begin animation
            itemElem.style.transform = `translateY(${itemMove}px)`;
            neighborElem.style.transform = `translateY(${neighborMove}px)`;
            // After animation is over ...
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Disable CSS animations
                itemElem.classList.add('transition-none');
                neighborElem.classList.add('transition-none');
                // Remove transform
                itemElem.style.removeProperty('transform');
                neighborElem.style.removeProperty('transform');
                void itemElem.offsetWidth; // Force re-flow
                // Update position on same frame, ahead of Alpine
                let storedPosition = itemElem.style.order;
                itemElem.style.order = neighborElem.style.order;
                neighborElem.style.order = storedPosition;
                void itemElem.offsetWidth; // Force re-flow
                // Wait for repaint, then re-enable CSS animations
                requestAnimationFrame(() => {
                    itemElem.classList.remove('transition-none');
                    neighborElem.classList.remove('transition-none');
                });
            };
            itemElem.addEventListener('transitionend', onDone);
        },
    });
});
</script>
@endpush
@endonce
