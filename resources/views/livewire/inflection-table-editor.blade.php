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
                        <x-tollerus::inputs.toggle idExpression="'table_' + tableId + '_visible'" model="table.visible" modelAlpine="true" label="{{ __('tollerus::ui.visible') }}" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-tollerus::inputs.text-saveable
                            showLabel="true"
                            idExpression="'table_' + tableId + '_label'"
                            model="table.label"
                            fieldName="{{ __('tollerus::ui.label') }}"
                            saveEvent="" />
                        <div class="flex flex-row justify-start">
                            <x-tollerus::inputs.toggle idExpression="'table_' + tableId + '_show_label'" model="table.showLabel" modelAlpine="true" label="{{ __('tollerus::ui.show_label') }}" />
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row-reverse items-start md:items-center justify-end gap-4">
                        <x-tollerus::alert>
                            <p x-text="msgs['stack_description']"></p>
                        </x-tollerus::alert>
                        <div class="flex flex-row justify-start">
                            <x-tollerus::inputs.toggle idExpression="'table_' + tableId + '_stack'" model="table.stack" modelAlpine="true" label="{{ __('tollerus::ui.stack') }}" />
                        </div>
                    </div>
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
