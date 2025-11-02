<div
    x-data="{
        dirty: false,
        btn: 'saved',
        msgs: {
            save: @js(__('tollerus::ui.save')),
            saved: @js(__('tollerus::ui.saved')),
            saving: @js(__('tollerus::ui.saving')),
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            load: @js(__('tollerus::ui.load')),
            loading: @js(__('tollerus::ui.loading')),
        },
        tab: 'info',
        neographiesForm: $wire.entangle('neographiesForm'),
        nativeSpellingCounts: $wire.entangle('nativeSpellingCounts'),
        nativeSpellingsMsgSrc: @js(__('tollerus::ui.will_delete_native_spellings')),
        get nativeSpellingsToDelete() {
            let count = 0;
            for (let neographyId in this.neographiesForm) {
                let active = this.neographiesForm[neographyId];
                if (this.neographiesForm.hasOwnProperty(neographyId) && typeof active === 'boolean' && !active) {
                    count += Number(this.nativeSpellingCounts[neographyId]);
                }
            }
            return count;
        },
        get nativeSpellingsMsg() {
            return this.nativeSpellingsMsgSrc.replaceAll(':#', this.nativeSpellingsToDelete.toLocaleString());
        },
        grammarForm: $wire.entangle('grammarForm'),
    }"
    @tab-switch.window="tab = $event.detail.tab;"
    @modal-discard.window="$wire.refreshForm(tab); dirty=false;"
    @modal-save.window="if (typeof $event.detail.tab === 'undefined') {$wire.save(tab, '', {});} else {$wire.save(tab, 'tab-switch', {tab: $event.detail.tab});}"
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
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.info') }}</span>
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
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.neographies') }}</span>
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
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.grammar') }}</span>
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
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.entries') }}</span>
                <span x-cloak x-show="tab=='entries' && dirty">*</span>
            </x-tollerus::inputs.tab>
        </ul>

        {{-- INFO TAB --}}
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
                <x-tollerus::inputs.textarea id="intro" model="infoForm.intro" label="{{ __('tollerus::ui.intro') }}" @input="btn = 'save'; dirty=true;" />
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

        {{-- NEOGRAPHIES TAB --}}
        <x-tollerus::panel
            id="tabpanel-neographies"
            role="tabpanel"
            x-cloak x-show="tab=='neographies'"
            class="flex flex-col gap-6 items-start"
        >
        @if (count($neographies)>0)
            <x-tollerus::alert>
                <p>{{ __('tollerus::ui.language_neographies_context_notice', ['language' => $language->name]) }} <a href="{{ route('tollerus.admin.neographies.index') }}">{{ __('tollerus::ui.edit_all_neographies') }}</a></p>
            </x-tollerus::alert>
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                            <span class="font-bold">{{ __('tollerus::ui.activate') }}</span>
                        </th>
                        <th scope="col" class="text-left py-1 px-2 border-b-2 min-w-24 border-zinc-400 dark:border-zinc-600">
                            <span class="font-bold">{{ __('tollerus::ui.neography') }}</span>
                        </th>
                        <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                            <span class="font-bold">{{ __('tollerus::ui.edit') }}</span>
                        </th>
                        <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                            <span class="font-bold">{{ __('tollerus::ui.primary') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($neographies as $neography)
                        <tr>
                            <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
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
                                    ]) }}"
                                    @change="btn = 'save'; dirty=true;" />
                            </td>
                            <th scope="row" class="text-left px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                                <span x-bind:class="neographiesForm[{{ $neography->id }}] ? 'font-bold' : 'font-bold opacity-40'">{{ $neography->name }}</span>
                            </th>
                            <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                                <x-tollerus::button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                                    href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography->id]) }}"
                                    class="inline-flex align-middle justify-center items-center"
                                >
                                    <x-tollerus::icons.edit />
                                    <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}</span>
                                </x-tollerus::button>
                            </td>
                            <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                                {{-- FIXME: this should be a radio button, because there can only be one selected --}}
                                <label class="inline-block align-middle w-6 h-6 relative group">
                                    <x-tollerus::icons.star
                                        x-bind:fill="neographiesForm.primary_neography == {{ $neography->id }} ? 'currentColor' : 'none'"
                                        class="rounded-lg text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700 group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white"
                                    />
                                    <input
                                        type="radio"
                                        name="primary_neography"
                                        value="{{ $neography->id }}"
                                        wire:model="neographiesForm.primary_neography"
                                        x-bind:title="(neographiesForm[{{ $neography->id }}]) ? @js(__('tollerus::ui.set_primary_as_name', ['name' => $neography->name])) : @js(__('tollerus::ui.primary_must_be_active'))"
                                        x-bind:disabled="!(neographiesForm[{{ $neography->id }}])"
                                        class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                        @change="btn = 'save'; dirty=true;"
                                    />
                                    <span class="sr-only">{{ __('tollerus::ui.set_primary_as_name', ['name' => $neography->name]) }}</span>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex flex-col items-start gap-2">
                <x-tollerus::alert type="warning" x-cloak x-show="nativeSpellingsToDelete > 0">{{ __('tollerus::ui.associated_delete') }}</x-tollerus::alert>
                <div class="flex flex-row justify-start gap-2">
                    <x-tollerus::inputs.button type="secondary" x-bind:disabled="!dirty" @click="$wire.refreshNeographiesForm(); dirty=false;">{{ __('tollerus::ui.reset') }}</x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        @click="if (nativeSpellingsToDelete > 0) {$dispatch('open-modal', {
                            message: nativeSpellingsMsg,
                            buttons: [
                                { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                { text: msgs.yes_delete, type: 'primary', clickEvent: 'modal-save' }
                            ]
                        })} else {btn = 'saving'; $wire.neographiesSave('',{});}"
                        x-bind:disabled="!dirty"
                        wire:loading.attr="disabled"
                        wire:target="neographiesSave"
                        @save-neographies-success.window="btn = 'saved'; dirty=false; if ($event.detail[0].afterSuccess) {$dispatch($event.detail[0].afterSuccess, $event.detail[0].payload);}"
                        @save-neographies-failure.window="btn = 'save';"
                        x-text="msgs[btn]" />
                </div>
            </div>
            @if ($errors->has('neographiesForm.*'))
                <div class="flex flex-row gap-4">
                    @foreach (collect($errors->get('neographiesForm.*'))->flatten() as $message)
                        <x-tollerus::alert type="error">{{ $message }}</x-tollerus::alert>
                    @endforeach
                </div>
            @endif
        @else
            <x-tollerus::missing-data>{{ __('tollerus::ui.no_neographies') }}</x-tollerus::missing-data>
        @endif
        </x-tollerus::panel>

        {{-- GRAMMAR TAB --}}
        <x-tollerus::panel id="tabpanel-grammar" role="tabpanel" x-cloak x-show="tab=='grammar'" class="flex flex-col gap-4 items-start">
            <div x-cloak x-show="grammarForm.length == 0" class="flex flex-col gap-4 items-start w-full" x-data="{ btn: 'load', preset: '' }">
                <x-tollerus::alert>
                    <p>{{ __('tollerus::ui.preset_notice') }}</p>
                </x-tollerus::alert>
                <div class="flex flex-col gap-4 items-start">
                    <x-tollerus::inputs.select id="preset" :options="$presetSelectOpts" label="{{ __('tollerus::ui.preset') }}" model="preset"/>
                    <template x-if="preset.length > 0">
                        <div class="flex flex-col items-start gap-4">
                            <h3 class="font-bold text-lg" x-text="$wire.presetData[preset]['previewHeading']"></h3>
                            <x-tollerus::pane>
                                <div class="flex flex-col sm:flex-row gap-y-4 gap-x-12">
                                    <div class="flex flex-col gap-1 justify-start items-start">
                                        <h4 class="font-bold text-base">{{ __('tollerus::ui.word_classes') }}</h4>
                                        <ul>
                                            <template x-for="group in $wire.presetData[preset]['groups']">
                                                <li class="flex flex-row gap-1 items-center">
                                                    <span x-text="group.name"></span>
                                                    <template x-if="group.featureNum > 0">
                                                        <span x-text="group.featureNum" class="block font-bold text-white dark:text-zinc-900 bg-zinc-700 dark:bg-zinc-300 rounded-full w-4 h-4 flex justify-center items-center text-center text-sm"></span>
                                                    </template>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="flex flex-col gap-1 justify-start items-start">
                                        <h4 class="font-bold text-base">{{ __('tollerus::ui.features') }}</h4>
                                        <ul>
                                            <template x-for="feature in $wire.presetData[preset]['features']">
                                                <li><span x-text="feature"></span></li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </x-tollerus::pane>
                            <x-tollerus::inputs.button
                                x-text="msgs[btn]"
                                @click="btn = 'loading'; $wire.loadGrammarPreset(preset);"
                                @preset-button-done.window="btn = 'load';"
                                x-bind:disabled="preset.length == 0"
                                wire:loading.attr="disabled"
                                wire:target="loadGrammarPreset"
                            />
                        </div>
                    </template>
                </div>
                @error('preset')
                    <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-6 items-center w-full">
                <x-tollerus::inputs.missing-data title="{{ __('tollerus::ui.add_word_class_group') }}" class="relative flex flex-row gap-2 justify-center items-center w-full">
                    <x-tollerus::icons.plus/>
                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_class_group') }}</span>
                </x-tollerus::inputs.missing-data>
            </div>
        </x-tollerus::panel>

        {{-- ENTRIES TAB --}}
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
