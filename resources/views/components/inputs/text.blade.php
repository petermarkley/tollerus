@props([
    'id' => '',
    'label' => '',
    'model' => '',
    'modelIsAlpine' => false,
])
<div class="flex flex-col gap-1 items-start flex-grow">
    @if (empty($label))
        {{ $slot }}
    @else
        <label for="{{ $id }}">{{ $label }}</label>
    @endif
    @if (empty($model))
        <input type="text" {{ $attributes }} class="border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror">
    @else
        <input
            type="text"
            id="{{ $id }}"
            @if (filter_var($modelIsAlpine, FILTER_VALIDATE_BOOLEAN))
                x-model="{{ $model }}"
            @else
                wire:model.defer="{{ $model }}"
            @endif
            {{ $attributes }}
            class="border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"
        />
        @error($model)
            <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
        @enderror
    @endif
</div>
