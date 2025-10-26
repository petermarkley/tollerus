<div>
    <h1 class="font-bold text-2xl mb-4">{{ $form['name'] }}</h1>
    <x-tollerus::panel class="flex flex-col gap-4">
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text id="name" model="form.name" label="{{ __('tollerus::ui.human_friendly') }}" />
                <x-tollerus::inputs.text id="machine_name" model="form.machine_name" label="{{ __('tollerus::ui.machine_friendly') }}" />
            </div>
        </div>
        <div>
            <button wire:click="save" class="bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 saturate-50 font-bold cursor-pointer rounded-lg py-2 px-4 shadow">Save</button>
        </div>
    </x-tollerus::panel>
</div>
