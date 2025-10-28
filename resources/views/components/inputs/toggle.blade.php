@props([
  'id' => '',
  'label' => '',
  'model' => null,
  'checked' => false,
])
<div class="flex flex-col gap-1 items-start">
    <div class="flex flex-row gap-4 justify-start items-center">
        <label for="{{ $id }}">{{ $label }}</label>
        <div class="relative inline-block w-[56px] h-[32px] group">
            <input type="checkbox" id="{{ $id }}" class="absolute opacity-0 inset-0 w-full h-full cursor-pointer z-10" wire:model="{{ $model }}" {{ $attributes }}>
            <span class="absolute rounded-full cursor-pointer inset-shadow-sm bg-zinc-500 group-has-checked:bg-cyan-800 group-has-checked:dark:bg-cyan-500 group-has-checked:dark:saturate-50 transition duration-200 inset-0 w-full h-full">
                <span class="absolute rounded-full shadow-sm top-[2px] left-[2px] w-[28px] h-[28px] bg-white dark:bg-zinc-800 group-has-checked:translate-x-[24px] transition duration-200"> </span>
            </span>
        </div>
    </div>
    @error($model)
        <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
    @enderror
</div>
