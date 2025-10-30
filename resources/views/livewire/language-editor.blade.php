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
    @modal-discard.window="$wire.refreshInfoForm(); dirty=false;"
    @modal-save.window="$wire.save('tab-switch', {tab: $event.detail.tab});"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ $infoForm['name'] }}</span>
            <span>({{ __('tollerus::ui.language') }})</span>
        </h1>
        <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="info"
                aria-controls="tabpanel-info"
                @click="$store.tabFunctions.click(dirty, tab, 'info');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'info');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'info');"
            >
                <x-tollerus::icons.info class="h-6"/>
                <span class="hidden md:inline">{{ __('tollerus::ui.info') }}</span>
                <span x-cloak x-show="tab=='info' && dirty">*</span>
            </x-tollerus::inputs.tab>
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="neographies"
                aria-controls="tabpanel-neographies"
                @click="$store.tabFunctions.click(dirty, tab, 'neographies');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'neographies');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'neographies');"
            >
                <x-tollerus::icons.neography class="h-6"/>
                <span class="hidden md:inline">{{ __('tollerus::ui.neographies') }}</span>
                <span x-cloak x-show="tab=='neographies' && dirty">*</span>
            </x-tollerus::inputs.tab>
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="grammar"
                aria-controls="tabpanel-grammar"
                @click="$store.tabFunctions.click(dirty, tab, 'grammar');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'grammar');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'grammar');"
            >
                <x-tollerus::icons.grammar class="h-6"/>
                <span class="hidden md:inline">{{ __('tollerus::ui.grammar') }}</span>
                <span x-cloak x-show="tab=='grammar' && dirty">*</span>
            </x-tollerus::inputs.tab>
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="entries"
                aria-controls="tabpanel-entries"
                @click="$store.tabFunctions.click(dirty, tab, 'entries');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'entries');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'entries');"
            >
                <x-tollerus::icons.entries class="h-6"/>
                <span class="hidden md:inline">{{ __('tollerus::ui.entries') }}</span>
                <span x-cloak x-show="tab=='entries' && dirty">*</span>
            </x-tollerus::inputs.tab>
        </ul>
        <x-tollerus::panel id="tabpanel-info" role="tabpanel" x-cloak x-show="tab=='info'" class="flex flex-col gap-6">
            <x-tollerus::inputs.toggle id="visible" model="infoForm.visible" label="{{ __('tollerus::ui.visible') }}" @change="btn = 'save'; dirty=true;" />
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
                <x-tollerus::inputs.textarea id="intro" model="infoForm.intro" label="{{ __('tollerus::ui.intro') }}" @input="btn = 'save'; dirty=true;" />
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
        <x-tollerus::panel id="tabpanel-neographies" role="tabpanel" x-cloak x-show="tab=='neographies'" class="flex flex-col gap-6 items-start">
            <div class="flex flex-col gap-4 items-start">
                <p>{{ __('tollerus::ui.language_neographies_context_notice', ['language' => $language->name]) }} <a href="{{ route('tollerus.admin.neographies.index') }}">{{ __('tollerus::ui.edit_all_neographies') }}</a></p>
            </div>
            <div class="grid grid-cols-4 gap-y-4" x-data="{ neographiesForm: $wire.entangle('neographiesForm') }">
                <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-400 dark:border-zinc-600">
                    <span class="font-bold">{{ __('tollerus::ui.activate') }}</span>
                </div>
                <div class="col-span-1 flex flex-row justify-start items-center py-1 px-2 border-b-2 border-zinc-400 dark:border-zinc-600">
                    <span class="font-bold">{{ __('tollerus::ui.neography') }}</span>
                </div>
                <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-400 dark:border-zinc-600">
                    <span class="font-bold">{{ __('tollerus::ui.edit') }}</span>
                </div>
                <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-400 dark:border-zinc-600">
                    <span class="font-bold">{{ __('tollerus::ui.primary') }}</span>
                </div>
                @foreach ($neographies as $neography)
                    <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-300 dark:border-zinc-700">
                        <x-tollerus::inputs.toggle
                            showLabel="false"
                            id="neography_{{ $neography->id }}"
                            model="neographiesForm.{{ $neography->id }}"
                            x-effect="
                                if (!(neographiesForm[{{ $neography->id }}]) && neographiesForm.primary_neography == {{ $neography->id }}) {
                                    neographiesForm.primary_neography = null;
                                    $wire.set('neographiesForm.primary_neography', null);
                                }
                            "
                            label="{{ __('tollerus::ui.activate_neography_in_language', [
                                'neography' => $neography->name,
                                'language' => $language->name
                            ]) }}" />
                    </div>
                    <div class="col-span-1 flex flex-row justify-start items-center py-1 px-2 border-b-2 border-zinc-300 dark:border-zinc-700">
                        <span x-bind:class="neographiesForm[{{ $neography->id }}] ? 'font-bold' : 'font-bold opacity-40'">{{ $neography->name }}</span>
                    </div>
                    <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-300 dark:border-zinc-700">
                        <x-tollerus::button
                            type="secondary"
                            size="small"
                            title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                            href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography->id]) }}"
                        >
                            <x-tollerus::icons.edit />
                        </x-tollerus::button>
                    </div>
                    <div class="col-span-1 flex flex-row justify-center items-center py-1 px-2 border-b-2 border-zinc-300 dark:border-zinc-700">
                        {{-- FIXME: this should be a radio button, because there can only be one selected --}}
                        <label class="w-6 h-6 relative group">
                            <x-tollerus::icons.star
                                x-bind:fill="neographiesForm.primary_neography == {{ $neography->id }} ? 'currentColor' : 'none'"
                                class="text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700"
                            />
                            <input
                                type="radio"
                                name="primary_neography"
                                value="{{ $neography->id }}"
                                wire:model="neographiesForm.primary_neography"
                                title="{{ __('tollerus::ui.set_primary_as_name', ['name' => $neography->name]) }}"
                                x-bind:disabled="!(neographiesForm[{{ $neography->id }}])"
                                class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                            />
                        </label>
                    </div>
                @endforeach
            </div>
        </x-tollerus::panel>
        <x-tollerus::panel id="tabpanel-grammar" role="tabpanel" x-cloak x-show="tab=='grammar'" class="flex flex-col gap-6">
            <p>Lorem ipsum dolor sit amet.</p>
        </x-tollerus::panel>
        <x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
            <p>Lorem ipsum dolor sit amet.</p>
        </x-tollerus::panel>
    </div>
    <x-tollerus::modal/>
</div>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('tabFunctions', {
        click(dirty, currentTab, tab) {
            if (currentTab == tab) {
                return;
            }
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
