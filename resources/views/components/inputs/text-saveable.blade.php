@props([
    'idExpression' => '',
    'model' => '',
    'fieldName' => '',
    'saveEvent' => '',
])
<div x-data="{ id: {{ $idExpression }}, editing: false, originalValue: {{ $model }} }">
    <template x-if="editing">
        <div class="flex flex-row gap-2 justify-start items-center w-full">
            <x-tollerus::inputs.text x-bind:id="id" x-model="{{ $model }}" />
            <x-tollerus::inputs.button
                type="primary"
                size="small"
                title="{{ __('tollerus::ui.save') }}"
                @click="{{ $saveEvent }} originalValue = {{ $model }}; editing = false;"
            >
                <x-tollerus::icons.check/>
                <span class="sr-only">{{ __('tollerus::ui.save') }}</span>
            </x-tollerus::inputs.button>
            <x-tollerus::inputs.button
                type="secondary"
                size="small"
                title="{{ __('tollerus::ui.cancel') }}"
                @click="{{ $model }} = originalValue; editing = false;"
            >
                <x-tollerus::icons.cancel/>
                <span class="sr-only">{{ __('tollerus::ui.cancel') }}</span>
            </x-tollerus::inputs.button>
        </div>
    </template>
    <template x-if="!editing">
        <div class="flex flex-row gap-2 justify-start items-center w-full">
            <template x-if="{{ $model }}!==null && {{ $model }}.length>0"><span x-text="{{ $model }}"></span></template>
            <template x-if="{{ $model }}===null || {{ $model }}.length==0"><span class="italic text-zinc-500 dark:text-zinc-500">({{ __('tollerus::ui.empty') }})</span></template>
            <x-tollerus::inputs.button
                type="secondary"
                size="small"
                title="{{ __('tollerus::ui.edit_thing', ['thing' => $fieldName]) }}"
                @click="editing = true; $nextTick(()=>{document.getElementById(id).focus()});"
            >
                <x-tollerus::icons.edit/>
                <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => $fieldName]) }}</span>
            </x-tollerus::inputs.button>
        </div>
    </template>
</div>
