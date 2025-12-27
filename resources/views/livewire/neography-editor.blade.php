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
            delete_font_file_confirmation: @js(__('tollerus::ui.delete_font_file_confirmation')),
            delete_section_confirmation: @js(__('tollerus::ui.delete_section_confirmation')),
        },
        tab: $wire.entangle('tab'),
        updateTabFromUrl() {
            const parts = window.location.pathname.split('/');
            const last = parts.pop() || 'info';
            newTab = ['info','font','glyphs','keyboards'].includes(last) ? last : 'info';
            $wire.refreshForm(this.tab);
            this.dirty=false;
            document.activeElement.blur();
            this.tab = newTab;
        },
        infoForm: $wire.entangle('infoForm'),
        fontForm: $wire.entangle('fontForm'),
        get hasFont() {
            let fileFound = false;
            for (let formatStr in this.fontForm) {
                let format = this.fontForm[formatStr];
                if (this.fontForm.hasOwnProperty(formatStr) && typeof format === 'object' && format.blobExists) {
                    fileFound = true;
                    break;
                }
            }
            return fileFound;
        },
        glyphsForm: $wire.entangle('glyphsForm'),
        moveSection(sectElem, sectId, dir) {
            neighborId = $store.reorderFunctions.getNeighborId(this.glyphsForm, sectId, dir);
            if (neighborId === null) {
                return;
            }
            neighborElem = document.getElementById('sect_' + neighborId);
            $store.reorderFunctions.swapItems(sectElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapSections(sectId, neighborId);
            };
            sectElem.addEventListener('transitionend', onDone);
        },
    }"
    @tab-switch.window="tab = $event.detail.tab; $store.tabFunctions.updateAddress($event.detail.tab);"
    @popstate.window="updateTabFromUrl();"
    @modal-discard.window="$wire.refreshForm(tab); dirty=false;"
    @modal-save.window="if (typeof $event.detail.tab === 'undefined') {$wire.save(tab, '', {});} else {$wire.save(tab, 'tab-switch', {tab: $event.detail.tab});}"
    @font-delete.window="$wire.fontDelete($event.detail.fontFormat);"
    @sect-delete.window="$wire.deleteSection($event.detail.sectId);"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ $neography->name }}</span>
            <span>({{ __('tollerus::ui.neography') }})</span>
        </h1>
        <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="info"
                aria-controls="tabpanel-info"
                title="{{ __('tollerus::ui.info') }}"
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
                tabName="font"
                aria-controls="tabpanel-font"
                title="{{ __('tollerus::ui.font') }}"
                @click="$store.tabFunctions.click(dirty, tab, 'font');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'font');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'font');"
            >
                <x-tollerus::icons.paper-clip class="h-6"/>
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.font') }}</span>
                <span x-cloak x-show="tab=='font' && dirty">*</span>
            </x-tollerus::inputs.tab>
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="glyphs"
                aria-controls="tabpanel-glyphs"
                title="{{ __('tollerus::ui.glyphs') }}"
                @click="$store.tabFunctions.click(dirty, tab, 'glyphs');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'glyphs');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'glyphs');"
            >
                <x-tollerus::icons.glyphs class="h-6"/>
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.glyphs') }}</span>
                <span x-cloak x-show="tab=='glyphs' && dirty">*</span>
            </x-tollerus::inputs.tab>
            <x-tollerus::inputs.tab
                switcher="tab"
                tabName="keyboards"
                aria-controls="tabpanel-keyboards"
                title="{{ __('tollerus::ui.keyboards') }}"
                @click="$store.tabFunctions.click(dirty, tab, 'keyboards');"
                @keydown.enter.prevent="$store.tabFunctions.click(dirty, tab, 'keyboards');"
                @keydown.space.prevent="$store.tabFunctions.click(dirty, tab, 'keyboards');"
            >
                <x-tollerus::icons.keyboard class="h-6"/>
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.keyboards') }}</span>
                <span x-cloak x-show="tab=='keyboards' && dirty">*</span>
            </x-tollerus::inputs.tab>
        </ul>

        {{-- INFO TAB --}}
        @include('tollerus::livewire.neography-editor._info')

        {{-- FONT TAB --}}
        @include('tollerus::livewire.neography-editor._font')

        {{-- GLYPHS TAB --}}
        @include('tollerus::livewire.neography-editor._glyphs')

        {{-- KEYBOARDS TAB --}}
        @include('tollerus::livewire.neography-editor._keyboards')

    </div>
    <x-tollerus::modal/>
</div>
<x-tollerus::reorder-script/>
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
                    history.pushState({}, '', '{{ route('tollerus.admin.neographies.edit', [$neography]) }}');
                break;
                case 'font':
                    history.pushState({}, '', '{{ route('tollerus.admin.neographies.edit.tab', [$neography, 'font']) }}');
                break;
                case 'glyphs':
                    history.pushState({}, '', '{{ route('tollerus.admin.neographies.edit.tab', [$neography, 'glyphs']) }}');
                break;
                case 'keyboards':
                    history.pushState({}, '', '{{ route('tollerus.admin.neographies.edit.tab', [$neography, 'keyboards']) }}');
                break;
            }
        },
    });
});
</script>
@endpush
@endonce
