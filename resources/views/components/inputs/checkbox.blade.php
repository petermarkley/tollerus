@props([
  'id' => '',
  'idExpression' => '',
  'label' => '',
  'showLabel' => true,
  'model' => null,
  'modelAlpine' => false,
  'checked' => false,
])
<div class="flex flex-col gap-1 items-center">
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
        <div class="relative inline-block w-[28px] h-[28px] group">
            <input
                type="checkbox"
                @if (!empty($id) && empty($idExpression))
                    id="{{ $id }}"
                @else
                    x-bind:id="{{ $idExpression }}"
                @endif
                title="{{ $showLabel ? $label : '' }}"
                class="absolute opacity-0 inset-0 w-full h-full cursor-pointer disabled:cursor-not-allowed z-10"
                @if (filter_var($modelAlpine, FILTER_VALIDATE_BOOLEAN))
                    x-model="{{ $model }}"
                @else
                    wire:model="{{ $model }}"
                @endif
                {{ $attributes }}
            />
            <span class="absolute rounded-full cursor-pointer inset-shadow-sm border-2 border-zinc-500 bg-zinc-300 dark:bg-zinc-700 group-has-checked:border-cyan-600 group-has-checked:bg-cyan-200 group-has-checked:saturate-50 group-has-checked:dark:border-cyan-500 group-has-checked:dark:bg-cyan-900 group-has-disabled:cursor-not-allowed group-has-disabled:border-zinc-300 group-has-disabled:bg-zinc-100 group-has-disabled:dark:border-zinc-700 group-has-disabled:dark:bg-zinc-800 group-has-disabled:group-has-checked:border-cyan-300 group-has-disabled:group-has-checked:bg-cyan-100 group-has-disabled:group-has-checked:dark:border-cyan-800 group-has-disabled:group-has-checked:dark:bg-cyan-900 group-has-disabled:group-has-checked:saturate-20 inset-0 w-full h-full group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white">
                <x-tollerus::icons.check class="absolute bottom-0 left-0 w-[28px] h-[28px] hidden group-has-checked:block group-has-disabled:opacity-50" />
            </span>
        </div>
    </div>
    @error($model)
        <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
    @enderror
</div>
