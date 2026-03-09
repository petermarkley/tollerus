<x-tollerus::panel id="tabpanel-info" role="tabpanel" x-cloak x-show="tab=='info'" class="flex flex-col gap-6">
    <div class="flex justify-start items-start">
        <x-tollerus::inputs.toggle id="visible" model="infoForm.visible" label="{{ __('tollerus::ui.visible') }}" @change="btn = 'save'; dirty=true;" />
    </div>
    <div class="flex flex-col gap-4">
        <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-tollerus::inputs.text id="name" model="infoForm.name" label="{{ __('tollerus::ui.human_friendly') }}" @input="btn = 'save'; dirty=true;" />
            <x-tollerus::inputs.text id="machine_name" model="infoForm.machine_name" label="{{ __('tollerus::ui.machine_friendly') }}" @input="btn = 'save'; dirty=true;" />
        </div>
    </div>
    <div class="flex flex-col gap-4">
        <h3 class="font-bold text-lg">{{ __('tollerus::ui.dictionary_info') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-tollerus::inputs.text id="dict_title" model="infoForm.dict_title" label="{{ __('tollerus::ui.title_short') }}" @input="btn = 'save'; dirty=true;" />
            <x-tollerus::inputs.text id="dict_title_full" model="infoForm.dict_title_full" label="{{ __('tollerus::ui.title_full') }}" @input="btn = 'save'; dirty=true;" />
        </div>
        <x-tollerus::inputs.text id="dict_author" model="infoForm.dict_author" label="{{ __('tollerus::ui.author') }}" @input="btn = 'save'; dirty=true;" />
        <x-tollerus::inputs.textarea
            wysiwyg="true"
            :nativeKeyboards="$nativeKeyboards"
            :language="$language"
            id="intro"
            model="infoForm.intro"
            label="{{ __('tollerus::ui.intro') }}"
            @input="$dispatch('tollerus-wysiwyg-input')"
        />
    </div>
    <div class="flex flex-row justify-start gap-2">
        <x-tollerus::inputs.button type="secondary" x-bind:disabled="!dirty" @click="$wire.refreshInfoForm(); dirty=false;">{{ __('tollerus::ui.reset') }}</x-tollerus::inputs.button>
        <x-tollerus::inputs.button
            @click="btn = 'saving'; $wire.infoSave('',{});"
            x-bind:disabled="!dirty"
            wire:loading.attr="disabled"
            wire:target="infoSave"
            @save-info-success.window="btn = 'saved'; dirty=false; if ($event.detail[0].afterSuccess) {$dispatch($event.detail[0].afterSuccess, $event.detail[0].payload);}"
            @save-info-failure.window="btn = 'save';"
            x-text="msgs[btn]" />
    </div>
</x-tollerus::panel>
