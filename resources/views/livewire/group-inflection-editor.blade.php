<div
    x-data="{
        msgs: {
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            delete_inflection_table_confirmation: @js(__('tollerus::ui.delete_inflection_table_confirmation')),
        },
        tableForm: $wire.entangle('tableForm'),
        get tablesFiltered() {
            return Object.fromEntries(Object.entries(this.tableForm).filter(([k, v]) => !isNaN(k)));
        },
        moveTable(tableElem, tableId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.tablesFiltered, tableId, dir);
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('table_' + neighborId);
            $store.reorderFunctions.swapItems(tableElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapTables(tableId, neighborId);
            };
            tableElem.addEventListener('transitionend', onDone);
        },
    }"
    @table-delete.window="$wire.deleteTable($event.detail.tableId);"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ mb_ucfirst($groupName) }}</span>
            <span>{{ __('tollerus::ui.inflection_tables') }}</span>
        </h1>
        <div class="flex flex-col gap-6">
            <x-tollerus::panel>
                <fieldset class="flex flex-col gap-2 items-start">
                    <h3 class="font-bold text-lg">
                        <label for="base_row" class="flex flex-row gap-4 items-center">
                            <x-tollerus::icons.bricks />
                            <span>{{ __('tollerus::ui.base_row') }}</span>
                        </label>
                    </h3>
                    <div class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                        <div><legend class="font-normal italic text-zinc-500 dark:text-zinc-500">{{ __('tollerus::ui.base_row_description') }} {{ __('tollerus::ui.used_in_auto_inflection') }}</legend></div>
                        <div>
                            <x-tollerus::inputs.select
                                idExpression="'base_row'"
                                label="{{ __('tollerus::ui.base_row') }}"
                                showLabel="false"
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
                        </div>
                    </div>
                </fieldset>
            </x-tollerus::panel>
            <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                <template x-for="([tableId, table], i) in $store.reorderFunctions.sortItems(tablesFiltered)">
                    <div
                        x-bind:id="'table_' + tableId"
                        data-obj="table"
                        class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                        x-bind:style="'order: '+i"
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
                                <a
                                    x-bind:href="table.tableEditUrl"
                                    title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}"
                                    class="font-bold text-xl flex flex-row gap-2 items-center text-zinc-900 dark:text-zinc-300"
                                >
                                    <x-tollerus::icons.table class="h-8"/>
                                    <span class="italic">{{ __('tollerus::ui.table_nameless') }}</span>
                                </a>
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
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <a
                                    x-bind:href="table.tableEditUrl"
                                    title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}"
                                    class="font-bold flex flex-row gap-4 items-center text-lg text-zinc-900 dark:text-zinc-300"
                                >
                                    <x-tollerus::icons.columns />
                                    <span>{{ __('tollerus::ui.columns') }}</span>
                                </a>
                                <template x-if="Object.keys(table.columns).length > 0">
                                    <div class="w-full flex flex-row flex-wrap gap-2 justify-center items-center">
                                        <template x-for="column in table.columns">
                                            <div class="rounded-lg overflow-hidden border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                                <table>
                                                    <thead>
                                                        <tr><th scope="col" colspan="2" x-text="column.label" class="text-center px-4 py-2 font-normal"></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <template x-for="row in column.rows">
                                                            <tr>
                                                                <th scope="row" class="text-right p-1 font-normal border-t border-r border-zinc-300 dark:border-zinc-600">
                                                                    <abbr x-bind:title="row.labelLong || row.label" x-text="row.labelBrief || row.label.slice(0,3)" class="no-underline"></abbr>
                                                                </th>
                                                                <td class="text-center px-4 py-1 border-t border-zinc-300 dark:border-zinc-600">&hellip;</td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </template>
                                        <x-tollerus::button
                                            type="secondary"
                                            size="small"
                                            title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}"
                                            x-bind:href="table.tableEditUrl"
                                            class="flex flex-row gap-2 items-center"
                                        >
                                            <x-tollerus::icons.edit class="m-2"/>
                                            <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}</span>
                                        </x-tollerus::button>
                                    </div>
                                </template>
                                <template x-if="Object.keys(table.columns).length == 0">
                                    <div class="flex flex-row justify-center items-center w-full">
                                        <x-tollerus::missing-data href x-bind:href="table.tableEditUrl">{{ __('tollerus::ui.no_columns') }}</x-tollerus::missing-data>
                                    </div>
                                </template>
                            </x-tollerus::pane>
                        </x-tollerus::panel>
                    </div>
                </template>
            </div>
            <div class="px-6 xl:px-0">
                <x-tollerus::inputs.missing-data
                    size="medium" floating="true"
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
    </div>
    <x-tollerus::modal/>
</div>
<x-tollerus::reorder-script/>
