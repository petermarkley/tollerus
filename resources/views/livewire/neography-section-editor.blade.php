<div
    x-data="{
        dirty: false,
        btn: 'saved',
        msgs: {
            save: @js(__('tollerus::ui.save')),
            saved: @js(__('tollerus::ui.saved')),
            saving: @js(__('tollerus::ui.saving')),
            group_nameless: @js(__('tollerus::ui.group_nameless')),
            glyphs: @js(__('tollerus::ui.glyphs')),
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            delete_glyph_group_confirmation: @js(__('tollerus::ui.delete_glyph_group_confirmation')),
            delete_glyph_confirmation: @js(__('tollerus::ui.delete_glyph_confirmation')),
        },
        infoForm: $wire.entangle('infoForm'),
        groupsForm: $wire.entangle('groupsForm'),
        allSects: $wire.entangle('allSects'),
        moveGroup(groupElem, groupId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.groupsForm, groupId, dir);
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('group_' + neighborId);
            $store.reorderFunctions.swapItems(groupElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapGroups(groupId, neighborId);
            };
            groupElem.addEventListener('transitionend', onDone);
        },
        moveGlyph(groupId, glyphElem, glyphId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.groupsForm[groupId].glyphs, glyphId, dir);
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('glyph_' + neighborId);
            $store.reorderFunctions.swapItems(glyphElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapGlyphs(groupId, glyphId, neighborId);
            };
            glyphElem.addEventListener('transitionend', onDone);
        },
        deleteItem(id) {
            let e = document.getElementById(id);
            if (e) {
                e.remove();
            }
        },
    }"
    @group-delete.window="deleteItem('group_'+$event.detail.groupId); $wire.deleteGroup($event.detail.groupId);"
    @glyph-delete.window="deleteItem('glyph_'+$event.detail.glyphId); $wire.deleteGlyph($event.detail.glyphId);"
>
    <div id="non-modal-content" class="flex flex-col gap-6">
        <h1 class="font-bold text-2xl px-6 xl:px-0">
            <span>{{ $sect->name }}</span>
        </h1>
        <x-tollerus::panel class="flex flex-col gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-tollerus::inputs.text-saveable
                    showLabel="true"
                    idExpression="'sect_name'"
                    model="infoForm.name"
                    fieldName="{{ __('tollerus::ui.name') }}"
                    saveEvent="$wire.updateSection('name', document.getElementById(id).value, id);"
                />
                <x-tollerus::inputs.select
                    idExpression="'sect_type'"
                    label="{{ __('tollerus::ui.type') }}"
                    showLabel="true"
                    model="infoForm.type"
                    @change="$wire.updateSection('type', $el.value, id);"
                >
                    <option value="" class="cursor-pointer italic" x-bind:selected="infoForm.type===null || infoForm.type===''">{{ __('tollerus::ui.none') }}</option>
                    @foreach ($sectTypes as $sectType)
                        <option value="{{ $sectType['string'] }}" class="cursor-pointer" selected="infoForm.type=='{{ $sectType['string'] }}'">{{ $sectType['local'] }}</option>
                    @endforeach
                </x-tollerus::inputs.select>
            </div>
            <div class="flex flex-col gap-4">
                <x-tollerus::inputs.textarea id="intro" model="infoForm.intro" label="{{ __('tollerus::ui.intro') }}" @input="btn = 'save'; dirty=true;" />
            </div>
            <div class="flex flex-row justify-start gap-2">
                <x-tollerus::inputs.button
                    @click="btn = 'saving'; $wire.infoSave();"
                    x-bind:disabled="!dirty"
                    wire:loading.attr="disabled"
                    wire:target="infoSave"
                    @save-info-success.window="btn = 'saved'; dirty=false;"
                    @save-info-failure.window="btn = 'save';"
                    x-text="msgs[btn]" />
            </div>
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
                                <div>
                                    <x-tollerus::inputs.select
                                        idExpression="'group_' + groupId + '_type'"
                                        label="{{ __('tollerus::ui.type') }}"
                                        showLabel="true"
                                        model="group.type"
                                        @change="$wire.updateGroup(groupId, 'type', $el.value, id);"
                                    >
                                        <option value="" class="cursor-pointer italic" x-bind:selected="group.type===null || group.type===''">{{ __('tollerus::ui.none') }}</option>
                                        @foreach ($glyphTypes as $glyphType)
                                            <option value="{{ $glyphType['string'] }}" class="cursor-pointer" selected="group.type=='{{ $glyphType['string'] }}'">{{ $glyphType['local'] }}</option>
                                        @endforeach
                                    </x-tollerus::inputs.select>
                                </div>
                                <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                        <span>{{ __('tollerus::ui.glyphs') }}</span>
                                    </h3>
                                    <template x-if="Object.keys(group.glyphs).length > 0">
                                        <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                            <template x-for="([glyphId, glyph], i) in $store.reorderFunctions.sortItems(group.glyphs)">
                                                <div
                                                    x-bind:id="'glyph_' + glyphId"
                                                    data-obj="glyph"
                                                    class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                    x-bind:style="'order: '+i"
                                                    @transitionend="$nextTick(() => {animating=false});"
                                                >
                                                    <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            title="{{ __('tollerus::ui.move_glyph_earlier') }}"
                                                            x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(group.glyphs, glyphId)"
                                                            @click="animating=true; moveGlyph(groupId, $el.closest('[data-obj=&quot;glyph&quot;]'), glyphId, -1);"
                                                        >
                                                            <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                            <span class="sr-only">{{ __('tollerus::ui.move_glyph_earlier') }}</span>
                                                        </x-tollerus::inputs.button>
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            title="{{ __('tollerus::ui.move_glyph_later') }}"
                                                            x-bind:disabled="animating || $store.reorderFunctions.isLastItem(group.glyphs, glyphId)"
                                                            @click="animating=true; moveGlyph(groupId, $el.closest('[data-obj=&quot;glyph&quot;]'), glyphId, +1);"
                                                        >
                                                            <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                            <span class="sr-only">{{ __('tollerus::ui.move_glyph_later') }}</span>
                                                        </x-tollerus::inputs.button>
                                                    </x-tollerus::panel>
                                                    <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none flex-grow">
                                                        <div class="flex flex-row gap-4 justify-between items-start lg:items-center w-full">
                                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 flex-grow border-2 rounded-lg p-2 border-zinc-200 dark:border-zinc-600">
                                                                <x-tollerus::inputs.text-saveable
                                                                    showLabel="true"
                                                                    idExpression="'glyph_' + glyphId + '_unicode'"
                                                                    model="glyph.glyph"
                                                                    fieldName="{{ __('tollerus::ui.unicode') }}"
                                                                    saveEvent="$wire.updateGlyph(groupId, glyphId, 'glyph', document.getElementById(id).value, id);"
                                                                    height="67px"
                                                                    class="text-6xl tollerus_{{ $neography->machine_name }}"
                                                                />
                                                                <x-tollerus::inputs.text-saveable
                                                                    showLabel="true"
                                                                    idExpression="'glyph_' + glyphId + '_hex'"
                                                                    model="glyph.glyphHex"
                                                                    fieldName="{{ __('tollerus::ui.hexadecimal') }}"
                                                                    saveEvent="$wire.updateGlyph(groupId, glyphId, 'glyphHex', document.getElementById(id).value, id);"
                                                                />
                                                            </div>
                                                            <x-tollerus::inputs.button
                                                                type="inverse"
                                                                size="small"
                                                                class="align-middle"
                                                                title="{{ __('tollerus::ui.delete_glyph') }}"
                                                                @click="$dispatch('open-modal', {
                                                                    message: msgs['delete_glyph_confirmation'],
                                                                    buttons: [
                                                                        { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'glyph-delete', payload: {glyphId: glyphId} }
                                                                    ]
                                                                });"
                                                            >
                                                                <x-tollerus::icons.delete/>
                                                                <label class="sr-only">{{ __('tollerus::ui.delete_glyph') }}</label>
                                                            </x-tollerus::inputs.button>
                                                        </div>
                                                        <div class="flex flex-col w-full">
                                                            <p class="flex flex-row gap-4 items-center">
                                                                <span>{{ __('tollerus::ui.public_id') }}</span>
                                                                <span x-text="glyph.globalId" class="font-mono"></span>
                                                            </p>
                                                        </div>
                                                        <div class="flex flex-col md:flex-row lg:flex-col items-start gap-4 w-full">
                                                            <div class="flex flex-col gap-2 items-start w-full">
                                                                <h3 class="font-bold text-lg">{{ __('tollerus::ui.direct_meaning') }}</h3>
                                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 w-full">
                                                                    <div class="flex flex-row justify-start items-center">
                                                                        <x-tollerus::inputs.checkbox
                                                                            idExpression="'glyph_' + glyphId + '_render_base'"
                                                                            model="glyph.renderBase"
                                                                            modelIsAlpine="true"
                                                                            label="{{ __('tollerus::ui.render_on_base') }}"
                                                                            @change="$wire.updateGlyph(groupId, glyphId, 'renderBase', $el.checked, id);"
                                                                        />
                                                                    </div>
                                                                    <x-tollerus::inputs.text-saveable
                                                                        showLabel="true"
                                                                        idExpression="'glyph_' + glyphId + '_transliterated'"
                                                                        model="glyph.transliterated"
                                                                        fieldName="{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}"
                                                                        saveEvent="$wire.updateGlyph(groupId, glyphId, 'transliterated', document.getElementById(id).value, id);"
                                                                    />
                                                                    <x-tollerus::inputs.text-saveable
                                                                        showLabel="true"
                                                                        idExpression="'glyph_' + glyphId + '_phonemic'"
                                                                        model="glyph.phonemic"
                                                                        fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                                        saveEvent="$wire.updateGlyph(groupId, glyphId, 'phonemic', document.getElementById(id).value, id);"
                                                                    />
                                                                    <div class="col-span-1 lg:col-span-3">
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_' + glyphId + '_note'"
                                                                            model="glyph.note"
                                                                            fieldName="{{ __('tollerus::ui.note') }}"
                                                                            saveEvent="$wire.updateGlyph(groupId, glyphId, 'note', document.getElementById(id).value, id);"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2 items-start w-full">
                                                                <h3 class="font-bold text-lg">{{ __('tollerus::ui.spoken_form') }}</h3>
                                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 w-full">
                                                                    <x-tollerus::inputs.text-saveable
                                                                        showLabel="true"
                                                                        idExpression="'glyph_' + glyphId + '_pronunciation_transliterated'"
                                                                        model="glyph.pronunciationTransliterated"
                                                                        fieldName="{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}"
                                                                        saveEvent="$wire.updateGlyph(groupId, glyphId, 'pronunciationTransliterated', document.getElementById(id).value, id);"
                                                                    />
                                                                    <x-tollerus::inputs.text-saveable
                                                                        showLabel="true"
                                                                        idExpression="'glyph_' + glyphId + '_pronunciation_phonemic'"
                                                                        model="glyph.pronunciationPhonemic"
                                                                        fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                                        saveEvent="$wire.updateGlyph(groupId, glyphId, 'pronunciationPhonemic', document.getElementById(id).value, id);"
                                                                    />
                                                                    <x-tollerus::inputs.text-saveable
                                                                        showLabel="true"
                                                                        idExpression="'glyph_' + glyphId + '_pronunciation_native'"
                                                                        model="glyph.pronunciationNative"
                                                                        fieldName="{{ __('tollerus::ui.native') }}"
                                                                        saveEvent="$wire.updateGlyph(groupId, glyphId, 'pronunciationNative', document.getElementById(id).value, id);"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <x-tollerus::inputs.dropdown class="relative w-full">
                                                            <x-slot:button>
                                                                <x-tollerus::inputs.button
                                                                    type="secondary"
                                                                    title="{{ __('tollerus::ui.transfer_to') }}"
                                                                    @click="open=true"
                                                                >
                                                                    <span>{{ __('tollerus::ui.transfer_to') }}</span>
                                                                </x-tollerus::inputs.button>
                                                            </x-slot:button>
                                                            <template x-for="destSect in allSects">
                                                                <div class="flex flex-col items-start">
                                                                    <span x-text="destSect.name" class="italic opacity-50"></span>
                                                                    <template x-for="destGroup in destSect.groups">
                                                                        <x-tollerus::inputs.button
                                                                            type="inverse"
                                                                            size="small"
                                                                            x-bind:class="{'ml-4': true, 'line-through': destGroup.glyphs.includes(glyph.glyph)}"
                                                                            x-bind:disabled="destGroup.glyphs.includes(glyph.glyph)"
                                                                            x-text="msgs['group_nameless'] + ' - ' + destGroup.glyphs.length + ' ' + msgs['glyphs']"
                                                                            @click="open=false; $wire.transferGlyph(groupId, glyphId, destSect.id, destGroup.id);"
                                                                        />
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </x-tollerus::inputs.dropdown>
                                                    </x-tollerus::panel>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <x-tollerus::inputs.missing-data
                                        size="small"
                                        title="{{ __('tollerus::ui.add_glyph') }}"
                                        class="relative flex flex-row gap-2 justify-center items-center w-full"
                                        @click="$wire.createGlyph(groupId);"
                                        wire:loading.attr="disabled"
                                        wire:target="createGlyph"
                                    >
                                        <x-tollerus::icons.plus/>
                                        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_glyph') }}</span>
                                    </x-tollerus::inputs.missing-data>
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
