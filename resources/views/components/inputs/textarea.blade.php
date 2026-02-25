@props([
  'id' => '',
  'label' => '',
  'model' => '',
  'rows' => 10,
  'monospace' => false,
  'wysiwyg' => false,
])
<div
    class="flex flex-col gap-1 items-start"
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        wire:ignore
        data-tollerus-wysiwyg
        x-data="tollerusWysiwyg({
            state: $wire.entangle('{{ $model }}'),
        })"
    @endif
>
    <label for="{{ $id }}">{{ $label }}</label>
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        <div class="w-full border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600" data-tollerus-wysiwyg-mount></div>
    @else
        <textarea id="{{ $id }}" wire:model.defer="{{ $model }}" rows="{{ $rows }}" {{ $attributes }} class="@if(filter_var($monospace, FILTER_VALIDATE_BOOLEAN)) font-mono @endif border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"></textarea>
        @error($model)
            <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
        @enderror
    @endif
</div>
