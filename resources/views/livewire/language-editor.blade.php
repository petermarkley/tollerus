<div>
    <h1 class="font-bold text-2xl mb-4">{{ $form['name'] }}</h1>
    <x-tollerus::panel class="flex flex-col gap-6">
        <x-tollerus::inputs.toggle id="visible" model="form.visible" label="{{ __('tollerus::ui.visible') }}" :checked="$form['visible']" />
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text id="name" model="form.name" label="{{ __('tollerus::ui.human_friendly') }}" />
                <x-tollerus::inputs.text id="machine_name" model="form.machine_name" label="{{ __('tollerus::ui.machine_friendly') }}" />
            </div>
        </div>
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.dictionary_info') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text id="dict_title" model="form.dict_title" label="{{ __('tollerus::ui.title_short') }}" />
                <x-tollerus::inputs.text id="dict_title_full" model="form.dict_title_full" label="{{ __('tollerus::ui.title_full') }}" />
            </div>
            <x-tollerus::inputs.text id="dict_author" model="form.dict_author" label="{{ __('tollerus::ui.author') }}" />
            <x-tollerus::inputs.textarea id="intro" model="form.intro" label="{{ __('tollerus::ui.intro') }}" />
        </div>
        <div>
            <button wire:click="save" class="bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 saturate-50 font-bold cursor-pointer rounded-lg py-2 px-4 shadow">Save</button>
        </div>
    </x-tollerus::panel>
</div>
