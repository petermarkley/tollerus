@props([
    'idExpression' => '',
    'model' => '',
    'fieldName' => '',
    'showLabel' => false,
    'saveEvent' => '',
    'height' => '',
    'type' => 'text',
])
<div
    x-data="{
        id: {{ $idExpression }},
        prop: $wire.entangle('{{ $model }}'),
        editing: false,
        originalValue: '',
    }"
    x-init="originalValue = prop;"
    @text-save-failure.window="if ($event.detail.id==id) {editing=true;}"
    @text-save-success.window="if ($event.detail.id==id) {originalValue = prop;}"
    class="flex flex-row gap-4 justify-start items-center"
>
    @if (filter_var($showLabel, FILTER_VALIDATE_BOOLEAN))
        <label x-bind:for="id">{{ $fieldName }}:</label>
    @endif
    @isset($before)
        {{ $before }}
    @endisset
    <div
        x-show="editing" x-cloak
        class="flex flex-row gap-2 justify-start items-center flex-grow"
    >
        @if (empty($height))
            <x-tollerus::inputs.text
                x-bind:id="id"
                model="prop"
                :modelIsAlpine="true"
                :type="$type"
                {{ $attributes }}
            />
        @else
            <x-tollerus::inputs.text
                x-bind:id="id"
                model="prop"
                :modelIsAlpine="true"
                :type="$type"
                {{ $attributes }}
                style="height:{{ $height }};"
            />
        @endif
        <x-tollerus::inputs.button
            type="primary"
            size="small"
            title="{{ __('tollerus::ui.save') }}"
            @click="{{ $saveEvent }} editing = false;"
        >
            <x-tollerus::icons.check/>
            <span class="sr-only">{{ __('tollerus::ui.save') }}</span>
        </x-tollerus::inputs.button>
        <x-tollerus::inputs.button
            type="secondary"
            size="small"
            title="{{ __('tollerus::ui.cancel') }}"
            @click="prop = originalValue; editing = false;"
        >
            <x-tollerus::icons.cancel/>
            <span class="sr-only">{{ __('tollerus::ui.cancel') }}</span>
        </x-tollerus::inputs.button>
    </div>
    <div
        x-show="!editing"
        class="flex flex-row gap-2 justify-start items-center p-2 rounded-lg border border-zinc-100/40 bg-zinc-100/80 dark:border-zinc-700/10 dark:bg-zinc-700/20"
        @if (!empty($height))
            style="height:{{ $height }};"
        @endif
    >
        <template x-if="prop!==null && prop.length>0"><span x-text="prop" {{ $attributes }}></span></template>
        <template x-if="prop===null || prop.length==0"><span {{ $attributes->merge(['class' => 'italic text-zinc-500 dark:text-zinc-500']) }} style="direction:ltr;">({{ __('tollerus::ui.empty') }})</span></template>
        <x-tollerus::inputs.button
            type="inverse"
            size="small"
            title="{{ __('tollerus::ui.edit_thing', ['thing' => $fieldName]) }}"
            @click="editing = true; $nextTick(()=>{document.getElementById(id).focus()});"
        >
            <x-tollerus::icons.edit/>
            <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => $fieldName]) }}</span>
        </x-tollerus::inputs.button>
    </div>
</div>
