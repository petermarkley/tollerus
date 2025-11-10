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
            group_nameless: @js(__('tollerus::ui.group_nameless')),
            delete_word_class_group_confirmation: @js(__('tollerus::ui.delete_word_class_group_confirmation')),
            delete_word_class_confirmation: @js(__('tollerus::ui.delete_word_class_confirmation')),
            delete_feature_confirmation: @js(__('tollerus::ui.delete_feature_confirmation')),
            delete_feature_value_confirmation: @js(__('tollerus::ui.delete_feature_value_confirmation')),
        },
        tab: $wire.entangle('tab'),
        updateTabFromUrl() {
            const parts = window.location.pathname.split('/');
            const last = parts.pop() || 'info';
            newTab = ['info','neographies','grammar','entries'].includes(last) ? last : 'info';
            $wire.refreshForm(this.tab);
            this.dirty=false;
            document.activeElement.blur();
            this.tab = newTab;
        },
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
    @tab-switch.window="tab = $event.detail.tab; $store.tabFunctions.updateAddress($event.detail.tab);"
    @popstate.window="updateTabFromUrl();"
    @modal-discard.window="$wire.refreshForm(tab); dirty=false;"
    @modal-save.window="if (typeof $event.detail.tab === 'undefined') {$wire.save(tab, '', {});} else {$wire.save(tab, 'tab-switch', {tab: $event.detail.tab});}"
    @grammar-group-delete.window="$wire.deleteGroup($event.detail.groupId);"
    @grammar-class-delete.window="$wire.deleteWordClass($event.detail.wordClassId);"
    @grammar-feature-delete.window="$wire.deleteFeature($event.detail.featureId);"
    @grammar-value-delete.window="$wire.deleteFeatureValue($event.detail.featureValueId);"
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
        @include('tollerus::livewire.language-editor._info')

        {{-- NEOGRAPHIES TAB --}}
        @include('tollerus::livewire.language-editor._neographies')

        {{-- GRAMMAR TAB --}}
        @include('tollerus::livewire.language-editor._grammar')

        {{-- ENTRIES TAB --}}
        @include('tollerus::livewire.language-editor._entries')

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
        updateAddress(tab) {
            switch (tab) {
                case 'info':
                    history.pushState({}, '', '{{ route('tollerus.admin.languages.edit', [$language]) }}');
                break;
                case 'neographies':
                    history.pushState({}, '', '{{ route('tollerus.admin.languages.edit.tab', [$language, 'neographies']) }}');
                break;
                case 'grammar':
                    history.pushState({}, '', '{{ route('tollerus.admin.languages.edit.tab', [$language, 'grammar']) }}');
                break;
                case 'entries':
                    history.pushState({}, '', '{{ route('tollerus.admin.languages.edit.tab', [$language, 'entries']) }}');
                break;
            }
        },
    });
});
</script>
@endpush
@endonce
