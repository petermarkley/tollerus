<div x-data="{
    msgs: {
        inflection_table_nameless: @js(__('tollerus::ui.inflection_table_nameless')),
        stack: @js(__('tollerus::ui.stack')),
        stack_description: @js(__('tollerus::ui.stack_description')),
        align_on_stack: @js(__('tollerus::ui.align_on_stack')),
        align_on_stack_description: @js(__('tollerus::ui.align_on_stack_description')),
        table_fold: @js(__('tollerus::ui.table_fold')),
        table_fold_description: @js(__('tollerus::ui.table_fold_description')),
        rows_fold: @js(__('tollerus::ui.rows_fold')),
        rows_fold_description: @js(__('tollerus::ui.rows_fold_description')),
    },
    tableForm: $wire.entangle('tableForm'),
}">
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ mb_ucfirst($groupName) }}</span>
            <span>{{ __('tollerus::ui.inflection_tables') }}</span>
        </h1>
        <div class="flex flex-col gap-6">
            <template x-for="(table, tableId) in tableForm">
                <x-tollerus::panel class="flex flex-col gap-6">
                    <h2 class="flex flex-row gap-2 items-center justify-between">
                        <div class="font-bold text-xl flex flex-row gap-2 items-center">
                            <x-tollerus::icons.table class="h-8"/>
                            <span x-text="table.label.length>0 ? table.label : msgs['inflection_table_nameless']" x-bind:class="{ 'font-normal italic': table.label.length==0 }"></span>
                        </div>
                        <x-tollerus::inputs.button
                            type="secondary"
                            size="small"
                            title="{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}"
                        >
                            <x-tollerus::icons.delete/>
                            <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.inflection_table')]) }}</span>
                        </x-tollerus::inputs.button>
                    </h2>
                    <div class="flex flex-col items-start">
                        <x-tollerus::inputs.toggle idExpression="'table_' + tableId + '_visible'" model="table.visible" modelIsAlpine="true" label="{{ __('tollerus::ui.visible') }}" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-tollerus::inputs.text-saveable
                            showLabel="true"
                            idExpression="'table_' + tableId + '_label'"
                            model="table.label"
                            fieldName="{{ __('tollerus::ui.label') }}"
                            saveEvent="" />
                        <div class="flex flex-row justify-start">
                            <x-tollerus::inputs.checkbox idExpression="'table_' + tableId + '_show_label'" model="table.showLabel" modelIsAlpine="true" label="{{ __('tollerus::ui.show_label') }}" />
                        </div>
                    </div>
                    <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                        <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['stack_description']"></legend></div>
                        <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                            <x-tollerus::inputs.checkbox idExpression="'table_' + tableId + '_stack'" model="table.stack" modelIsAlpine="true" label="{{ __('tollerus::ui.stack') }}" />
                        </div>
                    </fieldset>
                    <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                        <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['align_on_stack_description']"></legend></div>
                        <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                            <x-tollerus::inputs.checkbox idExpression="'table_' + tableId + '_align_on_stack'" model="table.alignOnStack" modelIsAlpine="true" label="{{ __('tollerus::ui.align_on_stack') }}" />
                        </div>
                    </fieldset>
                    <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                        <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['table_fold_description']"></legend></div>
                        <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                            <x-tollerus::inputs.checkbox idExpression="'table_' + tableId + '_table_fold'" model="table.tableFold" modelIsAlpine="true" label="{{ __('tollerus::ui.table_fold') }}" />
                        </div>
                    </fieldset>
                    <fieldset class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-2 md:gap-4">
                        <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500" x-text="msgs['rows_fold_description']"></legend></div>
                        <div class="flex flex-row justify-start md:justify-end md:w-60 shrink-0 text-left md:text-right">
                            <x-tollerus::inputs.checkbox idExpression="'table_' + tableId + '_rows_fold'" model="table.rowsFold" modelIsAlpine="true" label="{{ __('tollerus::ui.rows_fold') }}" />
                        </div>
                    </fieldset>
                    <x-tollerus::pane class="flex flex-col gap-4 items-start">
                        <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                            <x-tollerus::icons.filter />
                            <span>{{ __('tollerus::ui.filters') }}</span>
                        </h3>
                        {{-- FIXME Add/remove filters --}}
                    </x-tollerus::pane>
                    <x-tollerus::pane class="flex flex-col gap-4 items-start">
                        <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                            <x-tollerus::icons.arrow-down-right />
                            <span>{{ __('tollerus::ui.rows') }}</span>
                        </h3>
                        <template x-if="Object.keys(table.rows).length > 0">
                            <div class="flex flex-col gap-4 items-start">
                                <template x-for="(row, rowId) in table.rows">
                                    <x-tollerus::panel class="flex flex-col gap-4 items-start">
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
                                        <div class="pl-12">
                                            {{-- FIXME Add/remove filters --}}
                                        </div>
                                    </x-tollerus::panel>
                                </template>
                            </div>
                        </template>
                        <x-tollerus::inputs.missing-data
                            size="small"
                            title="{{ __('tollerus::ui.add_row') }}"
                            class="relative flex flex-row gap-2 justify-center items-center w-full"
                        >
                            <x-tollerus::icons.plus/>
                            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_row') }}</span>
                        </x-tollerus::inputs.missing-data>
                    </x-tollerus::pane>
                </x-tollerus::panel>
            </template>
            <x-tollerus::inputs.missing-data
                size="medium"
                title="{{ __('tollerus::ui.add_inflection_table') }}"
                class="relative flex flex-row gap-2 justify-center items-center w-full"
            >
                <x-tollerus::icons.plus/>
                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_inflection_table') }}</span>
            </x-tollerus::inputs.missing-data>
        </div>
    </div>
    <x-tollerus::modal/>
</div>
