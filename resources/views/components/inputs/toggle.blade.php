@props([
  'id' => '',
  'idExpression' => '',
  'label' => '',
  'showLabel' => true,
  'model' => null,
  'modelIsAlpine' => false,
  'checked' => false,
])
<div
    @if (!empty($id) && empty($idExpression))
        x-data="{ id: {{ $id }} }"
    @else
        x-data="{ id: {{ $idExpression }} }"
    @endif
    class="flex flex-col gap-1 items-center"
>
    <div class="flex flex-row gap-4 justify-start items-center">
        <label
            @if (!empty($id) && empty($idExpression))
                for="{{ $id }}"
            @else
                x-bind:for="{{ $idExpression }}"
            @endif
            @if (!filter_var($showLabel, FILTER_VALIDATE_BOOLEAN))
                class="sr-only"
            @endif
        >{{ $label }}</label>
        <div class="relative inline-block w-[56px] h-[32px] group">
            <input
                type="checkbox"
                @if (!empty($id) && empty($idExpression))
                    id="{{ $id }}"
                @else
                    x-bind:id="{{ $idExpression }}"
                @endif
                title="{{ $showLabel ? $label : '' }}"
                class="absolute opacity-0 inset-0 w-full h-full cursor-pointer disabled:cursor-not-allowed z-10"
                @if (filter_var($modelIsAlpine, FILTER_VALIDATE_BOOLEAN))
                    x-model="{{ $model }}"
                @else
                    wire:model="{{ $model }}"
                @endif
                {{ $attributes }}
            />
            <span class="absolute rounded-full cursor-pointer inset-shadow-sm bg-zinc-500 group-has-checked:bg-cyan-800 group-has-checked:dark:bg-cyan-500 group-has-checked:dark:saturate-50 group-has-disabled:cursor-not-allowed group-has-disabled:saturate-100 group-has-disabled:bg-zinc-300 group-has-disabled:dark:bg-zinc-700 group-has-disabled:group-has-checked:bg-cyan-300 group-has-disabled:group-has-checked:dark:bg-cyan-800 group-has-disabled:group-has-checked:saturate-20 transition duration-200 inset-0 w-full h-full group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white">
                <span class="absolute rounded-full shadow-sm top-[2px] left-[2px] w-[28px] h-[28px] bg-white dark:bg-zinc-800 group-has-checked:translate-x-[24px] transition duration-200"> </span>
            </span>
        </div>
    </div>
    @error($model)
        <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
    @enderror
</div>
