@props([
    'idExpression' => '',
    'options' => [],
    'label' => '',
    'showLabel' => true,
    'model' => '',
])
<div x-data="{ id: {{ $idExpression }} }" class="flex flex-row gap-4 justify-start items-center">
    @if (filter_var($showLabel, FILTER_VALIDATE_BOOLEAN))
        <label x-bind:for="id">{{ $label }}</label>
    @endif
    <select x-bind:id="id" {{ $attributes }} x-model="{{ $model }}" class="bg-white dark:bg-zinc-800 hover:bg-zinc-100 hover:dark:bg-zinc-700 border-2 border-zinc-200 dark:border-zinc-900 hover:border-zinc-300 hover:dark:border-zinc-800 cursor-pointer rounded-lg py-2 px-4 h-11 flex justify-center items-center shadow-lg disabled:cursor-not-allowed">
        @if (count($options)>0)
            <option disabled selected value="">({{ __('tollerus::ui.select') }})</option>
            @foreach ($options as $key => $option)
                <option value="{{ $key }}" class="cursor-pointer">{{ $option }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</div>
