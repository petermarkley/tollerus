@props([
  'id' => '',
  'label' => '',
  'model' => '',
])
<div class="flex flex-col gap-1">
    <label for="{{ $id }}">{{ $label }}</label>
    <input type="text" id="{{ $id }}" wire:model.defer="{{ $model }}" class="border p-2 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-800 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror">
    @error($model)
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>
