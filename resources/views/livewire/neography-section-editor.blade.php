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
            delete_glyph_group_confirmation: @js(__('tollerus::ui.delete_glyph_group_confirmation')),
        },
        infoForm: $wire.entangle('infoForm'),
        groupsForm: $wire.entangle('groupsForm'),
        moveGroup(groupElem, groupId, dir) {
            neighborId = $store.reorderFunctions.getNeighborId(this.groupsForm, groupId, dir);
            if (neighborId === null) {
                return;
            }
            neighborElem = document.getElementById('group_' + neighborId);
            $store.reorderFunctions.swapItems(groupElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapGroups(groupId, neighborId);
            };
            groupElem.addEventListener('transitionend', onDone);
        },
    }"
    @group-delete.window="$wire.deleteGroup($event.detail.groupId);"
>
    <div id="non-modal-content" class="flex flex-col gap-6">
        <h1 class="font-bold text-2xl px-6 xl:px-0">
            <span>{{ $sect->name }}</span>
        </h1>
        <x-tollerus::panel class="flex flex-col gap-6">
            Lorem ipsum dolor sit amet.
        </x-tollerus::panel>
        <div class="flex flex-col gap-6">
            <h1 class="font-bold text-2xl px-6 xl:px-0">
                <span>{{ __('tollerus::ui.glyph_groups') }}</span>
            </h1>
            <template x-if="Object.keys(groupsForm).length > 0">
                <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                    <template x-for="([groupId, group], i) in $store.reorderFunctions.sortItems(groupsForm)">
                        <div
                            x-bind:id="'group_' + groupId"
                            data-obj="group"
                            class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                            x-bind:style="'order: '+i"
                            @transitionend="$nextTick(() => {animating=false});"
                        >
                            <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                                <x-tollerus::inputs.button
                                    type="inverse"
                                    title="{{ __('tollerus::ui.move_glyph_group_up') }}"
                                    x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(groupsForm, groupId)"
                                    @click="animating=true; moveGroup($el.closest('[data-obj=&quot;group&quot;]'), groupId, -1);"
                                >
                                    <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                    <span class="sr-only">{{ __('tollerus::ui.move_glyph_group_up') }}</span>
                                </x-tollerus::inputs.button>
                                <x-tollerus::inputs.button
                                    type="inverse"
                                    title="{{ __('tollerus::ui.move_glyph_group_down') }}"
                                    x-bind:disabled="animating || $store.reorderFunctions.isLastItem(groupsForm, groupId)"
                                    @click="animating=true; moveGroup($el.closest('[data-obj=&quot;group&quot;]'), groupId, +1);"
                                >
                                    <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                    <span class="sr-only">{{ __('tollerus::ui.move_glyph_group_down') }}</span>
                                </x-tollerus::inputs.button>
                            </x-tollerus::panel>
                            <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                                <div class="flex flex-row gap-2 items-center justify-between">
                                    <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                                        <x-tollerus::icons.folder class="h-8"/>
                                        <span class="font-normal italic">{{ __('tollerus::ui.group_nameless') }}</span>
                                    </h2>
                                    <div class="flex flex-row gap-2 items-center">
                                        <x-tollerus::inputs.button
                                            type="secondary"
                                            size="small"
                                            title="{{ __('tollerus::ui.delete_glyph_group') }}"
                                            @click="$dispatch('open-modal', {
                                                message: msgs['delete_glyph_group_confirmation'],
                                                buttons: [
                                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'group-delete', payload: {groupId: groupId} }
                                                ]
                                            });"
                                        >
                                            <x-tollerus::icons.delete/>
                                            <span class="sr-only">{{ __('tollerus::ui.delete_glyph_group') }}</span>
                                        </x-tollerus::inputs.button>
                                    </div>
                                </div>
                                <x-tollerus::pane>
                                    Lorem ipsum dolor sit amet.
                                </x-tollerus::pane>
                            </x-tollerus::panel>
                        </div>
                    </template>
                </div>
            </template>
            <x-tollerus::inputs.missing-data
                size="medium"
                title="{{ __('tollerus::ui.add_glyph_group') }}"
                class="relative flex flex-row gap-2 justify-center items-center w-full"
                @click="$wire.createGroup();"
                wire:loading.attr="disabled"
                wire:target="createGroup"
            >
                <x-tollerus::icons.plus/>
                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_glyph_group') }}</span>
            </x-tollerus::inputs.missing-data>
        </div>
    </div>
    <x-tollerus::modal/>
</div>
<x-tollerus::reorder-script/>
