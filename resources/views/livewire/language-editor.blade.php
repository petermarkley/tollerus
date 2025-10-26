<div>
    <h1 class="font-bold text-2xl mb-4">{{ $form['name'] }}</h1>
    <x-tollerus::panel class="flex flex-col gap-4">
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label for="name">{{ __('tollerus::ui.human_friendly') }}</label>
                    <input type="text" id="name" wire:model.defer="form.name" class="border p-2 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error('form.name') border-red-800 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror">
                    @error('form.name')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label for="machine_name">{{ __('tollerus::ui.machine_friendly') }}</label>
                    <input type="text" id="machine_name" wire:model.defer="form.machine_name" class="border p-2 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error('form.machine_name') border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror">
                    @error('form.machine_name')
                        <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <div>
            <button wire:click="save" class="bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 saturate-50 font-bold cursor-pointer rounded-lg py-2 px-4 shadow">Save</button>
        </div>
    </x-tollerus::panel>
</div>
