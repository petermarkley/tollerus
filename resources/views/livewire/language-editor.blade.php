<div
    x-data="{
        dirty: false,
        btn: 'saved',
        msgs: {
            save: @js(__('tollerus::ui.save')),
            saved: @js(__('tollerus::ui.saved')),
            saving: @js(__('tollerus::ui.saving'))
        },
        tab: 'info'
    }"
    @tab-switch.window="tab = $event.detail.tab;"
    @modal-cancel.window=""
    @modal-discard.window="$wire.refreshForm(); dirty=false;"
    @modal-save.window="$wire.save('tab-switch', {tab: $event.detail.tab});"
>
    <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">{{ $form['name'] }}</h1>
    <ul class="px-4 flex flex-row gap-4 justify-start items-end">
        <li
            x-bind:class="{
                'rounded-t-lg flex flex-row justify-start items-center gap-2 cursor-pointer py-2 px-4 flex': true,
                'bg-zinc-50 dark:bg-zinc-900 hover:bg-white hover:dark:bg-zinc-800': tab!='info',
                'bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': tab=='info'
            }"
            @click="$store.tabFunctions.click(dirty, 'info');"
        >
            <x-tollerus::icons.info class="h-6"/>
            <span class="hidden md:inline">{{ __('tollerus::ui.info') }}</span>
            <span x-cloak x-show="tab=='info' && dirty">*</span>
        </li>
        <li
            x-bind:class="{
                'rounded-t-lg flex flex-rowjustify-start items-center gap-2 cursor-pointer py-2 px-4': true,
                'bg-zinc-50 dark:bg-zinc-900 hover:bg-white hover:dark:bg-zinc-800': tab!='neographies',
                'bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': tab=='neographies'
            }"
            @click="$store.tabFunctions.click(dirty, 'neographies');"
        >
            <x-tollerus::icons.neography class="h-6"/>
            <span class="hidden md:inline">{{ __('tollerus::ui.neographies') }}</span>
            <span x-cloak x-show="tab=='neographies' && dirty">*</span>
        </li>
        <li
            x-bind:class="{
                'rounded-t-lg flex flex-rowjustify-start items-center gap-2 cursor-pointer py-2 px-4': true,
                'bg-zinc-50 dark:bg-zinc-900 hover:bg-white hover:dark:bg-zinc-800': tab!='grammar',
                'bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': tab=='grammar'
            }"
            @click="$store.tabFunctions.click(dirty, 'grammar');"
        >
            <x-tollerus::icons.grammar class="h-6"/>
            <span class="hidden md:inline">{{ __('tollerus::ui.grammar') }}</span>
            <span x-cloak x-show="tab=='grammar' && dirty">*</span>
        </li>
        <li
            x-bind:class="{
                'rounded-t-lg flex flex-rowjustify-start items-center gap-2 cursor-pointer py-2 px-4': true,
                'bg-zinc-50 dark:bg-zinc-900 hover:bg-white hover:dark:bg-zinc-800': tab!='entries',
                'bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': tab=='entries'
            }"
            @click="$store.tabFunctions.click(dirty, 'entries');"
        >
            <x-tollerus::icons.entries class="h-6"/>
            <span class="hidden md:inline">{{ __('tollerus::ui.entries') }}</span>
            <span x-cloak x-show="tab=='entries' && dirty">*</span>
        </li>
    </ul>
    <x-tollerus::panel x-cloak x-show="tab=='info'" class="flex flex-col gap-6">
        <x-tollerus::inputs.toggle id="visible" model="form.visible" label="{{ __('tollerus::ui.visible') }}" @change="btn = 'save'; dirty=true;" />
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
                @click="btn = 'saving'; $wire.save('',{});"
                x-bind:disabled="!dirty"
                wire:loading.attr="disabled"
                @save-success.window="btn = 'saved'; dirty=false; if ($event.detail[0].afterSuccess) {$dispatch($event.detail[0].afterSuccess, $event.detail[0].payload);}"
                @save-failure.window="btn = 'save';"
                x-text="msgs[btn]" />
        </div>
    </x-tollerus::panel>
    <x-tollerus::panel x-cloak x-show="tab=='neographies'" class="flex flex-col gap-6">
        <p>Lorem ipsum dolor sit amet.</p>
    </x-tollerus::panel>
    <x-tollerus::panel x-cloak x-show="tab=='grammar'" class="flex flex-col gap-6">
        <p>Lorem ipsum dolor sit amet.</p>
    </x-tollerus::panel>
    <x-tollerus::panel x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
        <p>Lorem ipsum dolor sit amet.</p>
    </x-tollerus::panel>
    <x-tollerus::modal/>
</div>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('tabFunctions', {
        click(dirty, tab) {
            if (dirty) {
                window.dispatchEvent(new CustomEvent('open-modal', {detail: {
                    message: @js(__('tollerus::ui.unsaved_alert')),
                    buttons: [
                        {
                            text: @js(__('tollerus::ui.cancel')),
                            type: 'secondary',
                            clickEvent: 'modal-cancel',
                        },
                        {
                            text: @js(__('tollerus::ui.discard')),
                            type: 'secondary',
                            clickEvent: 'modal-discard',
                        },
                        {
                            text: @js(__('tollerus::ui.save')),
                            type: 'primary',
                            clickEvent: 'modal-save',
                            payload: {tab: tab},
                        },
                    ],
                }}));
            } else {
                window.dispatchEvent(new CustomEvent('tab-switch', {detail: {tab: tab}}));
            }
        },
    });
});
</script>
@endpush
@endonce
