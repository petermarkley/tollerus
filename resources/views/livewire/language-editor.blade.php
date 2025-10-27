<div x-data="{
    dirty: false,
    btn: 'saved',
    msgs: {
        save: '{{ __('tollerus::ui.save') }}',
        saved: '{{ __('tollerus::ui.saved') }}',
        saving: '{{ __('tollerus::ui.saving') }}'
    }
}">
    <h1 class="font-bold text-2xl mb-4 flex flex-row gap-2 justify-start items-baseline">
        <span>{{ $form['name'] }}</span>
        <span x-show="dirty">*</span>
    </h1>
    <x-tollerus::panel class="flex flex-col gap-6">
        <x-tollerus::inputs.toggle id="visible" model="form.visible" label="{{ __('tollerus::ui.visible') }}" :checked="$form['visible']" @change="btn = 'save'; dirty=true;" />
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text id="name" model="form.name" label="{{ __('tollerus::ui.human_friendly') }}" @input="btn = 'save'; dirty=true;" />
                <x-tollerus::inputs.text id="machine_name" model="form.machine_name" label="{{ __('tollerus::ui.machine_friendly') }}" @input="btn = 'save'; dirty=true;" />
            </div>
        </div>
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ __('tollerus::ui.dictionary_info') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text id="dict_title" model="form.dict_title" label="{{ __('tollerus::ui.title_short') }}" @input="btn = 'save'; dirty=true;" />
                <x-tollerus::inputs.text id="dict_title_full" model="form.dict_title_full" label="{{ __('tollerus::ui.title_full') }}" @input="btn = 'save'; dirty=true;" />
            </div>
            <x-tollerus::inputs.text id="dict_author" model="form.dict_author" label="{{ __('tollerus::ui.author') }}" @input="btn = 'save'; dirty=true;" />
            <x-tollerus::inputs.textarea id="intro" model="form.intro" label="{{ __('tollerus::ui.intro') }}" @input="btn = 'save'; dirty=true;" />
        </div>
        <div>
            <x-tollerus::inputs.button
                @click="btn = 'saving'; $wire.save();"
                x-bind:disabled="!dirty"
                wire:loading.attr="disabled"
                @save-success.window="btn = 'saved'; dirty=false;"
                @save-failure.window="btn = 'save';"
                x-text="msgs[btn]" />
        </div>
    </x-tollerus::panel>
</div>
