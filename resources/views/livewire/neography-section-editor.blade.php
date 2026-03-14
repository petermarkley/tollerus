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
        hasGlyphGroups: $wire.entangle('hasGlyphGroups'),
        moveGroup(groupElem, groupId, neighborId) {
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
        moveGlyph(groupId, glyphElem, glyphId, neighborId) {
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
                    saveEvent="$wire.updateSection('name', prop, id);"
                />
                <x-tollerus::inputs.select
                    idExpression="'sect_type'"
                    label="{{ __('tollerus::ui.type') }}"
                    showLabel="true"
                    model="infoForm.type"
                    modelIsAlpine="false"
                    @change="$wire.updateSection('type', $el.value, id);"
                >
                    <option value="" class="cursor-pointer italic">{{ __('tollerus::ui.none') }}</option>
                    @foreach ($sectTypes as $sectType)
                        <option value="{{ $sectType['string'] }}" class="cursor-pointer">{{ $sectType['local'] }}</option>
                    @endforeach
                </x-tollerus::inputs.select>
            </div>
            <div class="flex flex-col gap-4" @tollerus-wysiwyg-input="btn = 'save'; dirty = true;">
                <x-tollerus::inputs.textarea
                    wysiwyg="true"
                    :nativeKeyboards="$nativeKeyboards"
                    id="intro"
                    model="infoForm.intro"
                    label="{{ __('tollerus::ui.intro') }}"
                    @input="$dispatch('tollerus-wysiwyg-input')"
                />
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
            <div x-show="hasGlyphGroups" x-cloak>
                <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                    @foreach (collect($groupsForm)->sortBy('position') as $groupId => $group)
                        @php
                            $prevNeighborId = $this->getNeighborId($groupsForm, $groupId, -1);
                            $nextNeighborId = $this->getNeighborId($groupsForm, $groupId, +1);
                        @endphp
                        <div
                            id="group_{{ $groupId }}"
                            wire:key="group-{{ $groupId }}"
                            data-obj="group"
                            class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                            style="order: {{ $loop->index }}"
                            @transitionend="$nextTick(() => {animating=false});"
                        >
                            <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                                <x-tollerus::inputs.button
                                    type="inverse"
                                    title="{{ __('tollerus::ui.move_glyph_group_up') }}"
                                    x-bind:disabled="animating || {{ $this->isFirstItem($groupsForm, $groupId) ? 'true' : 'false' }}"
                                    @click="animating=true; moveGroup($el.closest('[data-obj=group]'), {{ $groupId }}, {{ $prevNeighborId ?? 'null' }});"
                                >
                                    <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                    <span class="sr-only">{{ __('tollerus::ui.move_glyph_group_up') }}</span>
                                </x-tollerus::inputs.button>
                                <x-tollerus::inputs.button
                                    type="inverse"
                                    title="{{ __('tollerus::ui.move_glyph_group_down') }}"
                                    x-bind:disabled="animating || {{ $this->isLastItem($groupsForm, $groupId) ? 'true' : 'false' }}"
                                    @click="animating=true; moveGroup($el.closest('[data-obj=group]'), {{ $groupId }}, {{ $nextNeighborId ?? 'null' }});"
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
                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'group-delete', payload: {groupId: {{ $groupId }}} }
                                                ]
                                            });"
                                        >
                                            <x-tollerus::icons.delete/>
                                            <span class="sr-only">{{ __('tollerus::ui.delete_glyph_group') }}</span>
                                        </x-tollerus::inputs.button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 flex-grow">
                                    <x-tollerus::inputs.select
                                        idExpression="'group_{{ $groupId }}_type'"
                                        label="{{ __('tollerus::ui.type') }}"
                                        showLabel="true"
                                        model="groupsForm.{{ $groupId }}.type"
                                        modelIsAlpine="false"
                                        @change="$wire.updateGroup({{ $groupId }}, 'type', $el.value, id);"
                                    >
                                        <option value="" class="cursor-pointer italic">{{ __('tollerus::ui.none') }}</option>
                                        @foreach ($glyphTypes as $glyphType)
                                            <option value="{{ $glyphType['string'] }}" class="cursor-pointer">{{ $glyphType['local'] }}</option>
                                        @endforeach
                                    </x-tollerus::inputs.select>
                                    <x-tollerus::inputs.dropdown class="relative w-full">
                                        <x-slot:button>
                                            <x-tollerus::inputs.button
                                                type="secondary"
                                                title="{{ __('tollerus::ui.transfer_group_to') }}"
                                                @click="open=true"
                                            >
                                                <span>{{ __('tollerus::ui.transfer_group_to') }}</span>
                                            </x-tollerus::inputs.button>
                                        </x-slot:button>
                                        @foreach ($allSects as $destSect)
                                            @if ($destSect['isThis'])
                                                <x-tollerus::inputs.button
                                                    wire:key="group-dest-sect-{{ $destSect['id'] }}"
                                                    type="inverse"
                                                    size="small"
                                                    class="ml-4 line-through"
                                                    disabled
                                                >{{ $destSect['name'] }}</x-tollerus::inputs.button>
                                            @else
                                                <x-tollerus::inputs.button
                                                    wire:key="group-dest-sect-{{ $destSect['id'] }}"
                                                    type="inverse"
                                                    size="small"
                                                    class="ml-4"
                                                    @click="
                                                        open=false;
                                                        $wire.transferGroup({{ $groupId }}, {{ $destSect['id'] }});
                                                        deleteItem('group_{{ $groupId }}');
                                                    "
                                                >{{ $destSect['name'] }}</x-tollerus::inputs.button>
                                            @endif
                                        @endforeach
                                    </x-tollerus::inputs.dropdown>
                                </div>
                                <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                        <span>{{ __('tollerus::ui.glyphs') }}</span>
                                    </h3>
                                    @if (count($group['glyphs']) > 0)
                                        <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                            @foreach (collect($group['glyphs'])->sortBy('position') as $glyphId => $glyph)
                                                @php
                                                    $prevNeighborId = $this->getNeighborId($group['glyphs'], $glyphId, -1);
                                                    $nextNeighborId = $this->getNeighborId($group['glyphs'], $glyphId, +1);
                                                @endphp
                                                <div
                                                    id="glyph_{{ $glyphId }}"
                                                    wire:key="glyph-{{ $glyphId }}"
                                                    data-obj="glyph"
                                                    class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                    style="order: {{ $loop->index }}"
                                                    @transitionend="$nextTick(() => {animating=false});"
                                                >
                                                    <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            title="{{ __('tollerus::ui.move_glyph_earlier') }}"
                                                            x-bind:disabled="animating || {{ $this->isFirstItem($group['glyphs'], $glyphId) ? 'true' : 'false' }}"
                                                            @click="animating=true; moveGlyph({{ $groupId }}, $el.closest('[data-obj=glyph]'), {{ $glyphId }}, {{ $prevNeighborId ?? 'null' }});"
                                                        >
                                                            <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                            <span class="sr-only">{{ __('tollerus::ui.move_glyph_earlier') }}</span>
                                                        </x-tollerus::inputs.button>
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            title="{{ __('tollerus::ui.move_glyph_later') }}"
                                                            x-bind:disabled="animating || {{ $this->isLastItem($group['glyphs'], $glyphId) ? 'true' : 'false' }}"
                                                            @click="animating=true; moveGlyph({{ $groupId }}, $el.closest('[data-obj=glyph]'), {{ $glyphId }}, {{ $nextNeighborId ?? 'null' }});"
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
                                                                    idExpression="'glyph_{{ $glyphId }}_unicode'"
                                                                    model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.glyph"
                                                                    fieldName="{{ __('tollerus::ui.unicode') }}"
                                                                    saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'glyph', prop, fieldKey, id);"
                                                                    height="67px"
                                                                    class="text-6xl tollerus_{{ $neography->machine_name }}"
                                                                />
                                                                <x-tollerus::inputs.text-saveable
                                                                    showLabel="true"
                                                                    idExpression="'glyph_{{ $glyphId }}_hex'"
                                                                    model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.glyphHex"
                                                                    fieldName="{{ __('tollerus::ui.hexadecimal') }}"
                                                                    saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'glyphHex', prop, fieldKey, id);"
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
                                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'glyph-delete', payload: {glyphId: {{ $glyphId }}} }
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
                                                                <span class="font-mono">{{ $glyph['globalId'] }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="flex flex-col md:flex-row lg:flex-col items-start gap-4 w-full">
                                                            <div class="flex flex-col gap-2 items-start w-full">
                                                                <h3 class="font-bold text-lg">{{ __('tollerus::ui.direct_meaning') }}</h3>
                                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 w-full">
                                                                    <div class="flex flex-row justify-start items-center">
                                                                        <x-tollerus::inputs.checkbox
                                                                            idExpression="'glyph_{{ $glyphId }}_render_base'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.renderBase"
                                                                            modelIsAlpine="false"
                                                                            label="{{ __('tollerus::ui.render_on_base') }}"
                                                                            @change="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'renderBase', $el.checked, 'groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.renderBase', id);"
                                                                        />
                                                                    </div>
                                                                    <div>
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_transliterated'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.transliterated"
                                                                            fieldName="{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'transliterated', prop, fieldKey, id);"
                                                                        />
                                                                    </div>
                                                                    <div data-keyboard-elem="territory">
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_phonemic'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.phonemic"
                                                                            fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'phonemic', prop, fieldKey, id);"
                                                                        >
                                                                            <x-slot:before>
                                                                                <div
                                                                                    x-data="{ showKeyboard: false }"
                                                                                    class="relative"
                                                                                    @close-virtual-keyboard.window="showKeyboard=false;"
                                                                                >
                                                                                    <x-tollerus::inputs.button
                                                                                        x-cloak x-show="!showKeyboard"
                                                                                        type="secondary"
                                                                                        size="small"
                                                                                        class="align-middle"
                                                                                        title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                                                        @click="
                                                                                            editing=true;
                                                                                            $nextTick(()=>{
                                                                                                showKeyboard=true;
                                                                                                $store.virtualKeyboard.mount({
                                                                                                    virtualKeyboardType: 'phonemic',
                                                                                                    mountPoint: $el.parentNode,
                                                                                                    inputFieldId: id
                                                                                                });
                                                                                            });
                                                                                        "
                                                                                    >
                                                                                        <x-tollerus::icons.keyboard/>
                                                                                        <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                                                    </x-tollerus::inputs.button>
                                                                                    <x-tollerus::inputs.button
                                                                                        x-cloak x-show="showKeyboard"
                                                                                        type="primary"
                                                                                        size="small"
                                                                                        class="align-middle"
                                                                                        title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                                                        @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                                                    >
                                                                                        <x-tollerus::icons.keyboard/>
                                                                                        <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                                                    </x-tollerus::inputs.button>
                                                                                </div>
                                                                            </x-slot:before>
                                                                        </x-tollerus::inputs.text-saveable>
                                                                    </div>
                                                                    <div class="col-span-1 lg:col-span-3">
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_note'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.note"
                                                                            fieldName="{{ __('tollerus::ui.note') }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'note', prop, fieldKey, id);"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2 items-start w-full">
                                                                <h3 class="font-bold text-lg">{{ __('tollerus::ui.spoken_form') }}</h3>
                                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 w-full">
                                                                    <div>
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_pronunciation_transliterated'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.pronunciationTransliterated"
                                                                            fieldName="{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'pronunciationTransliterated', prop, fieldKey, id);"
                                                                        />
                                                                    </div>
                                                                    <div data-keyboard-elem="territory">
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_pronunciation_phonemic'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.pronunciationPhonemic"
                                                                            fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'pronunciationPhonemic', prop, fieldKey, id);"
                                                                        >
                                                                            <x-slot:before>
                                                                                <div
                                                                                    x-data="{ showKeyboard: false }"
                                                                                    class="relative"
                                                                                    @close-virtual-keyboard.window="showKeyboard=false;"
                                                                                >
                                                                                    <x-tollerus::inputs.button
                                                                                        x-cloak x-show="!showKeyboard"
                                                                                        type="secondary"
                                                                                        size="small"
                                                                                        class="align-middle"
                                                                                        title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                                                        @click="
                                                                                            editing=true;
                                                                                            $nextTick(()=>{
                                                                                                showKeyboard=true;
                                                                                                $store.virtualKeyboard.mount({
                                                                                                    virtualKeyboardType: 'phonemic',
                                                                                                    mountPoint: $el.parentNode,
                                                                                                    inputFieldId: id
                                                                                                });
                                                                                            });
                                                                                        "
                                                                                    >
                                                                                        <x-tollerus::icons.keyboard/>
                                                                                        <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                                                    </x-tollerus::inputs.button>
                                                                                    <x-tollerus::inputs.button
                                                                                        x-cloak x-show="showKeyboard"
                                                                                        type="primary"
                                                                                        size="small"
                                                                                        class="align-middle"
                                                                                        title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                                                        @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                                                    >
                                                                                        <x-tollerus::icons.keyboard/>
                                                                                        <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                                                    </x-tollerus::inputs.button>
                                                                                </div>
                                                                            </x-slot:before>
                                                                        </x-tollerus::inputs.text-saveable>
                                                                    </div>
                                                                    <div data-keyboard-elem="territory">
                                                                        <x-tollerus::inputs.text-saveable
                                                                            showLabel="true"
                                                                            idExpression="'glyph_{{ $glyphId }}_pronunciation_native'"
                                                                            model="groupsForm.{{ $groupId }}.glyphs.{{ $glyphId }}.pronunciationNative"
                                                                            fieldName="{{ __('tollerus::ui.native') }}"
                                                                            saveEvent="$wire.updateGlyph({{ $groupId }}, {{ $glyphId }}, 'pronunciationNative', prop, fieldKey, id);"
                                                                            class="tollerus_{{ $neography->machine_name }}"
                                                                        >
                                                                            <x-slot:before>
                                                                                @if ($neography->keyboards()->exists())
                                                                                    <div
                                                                                        x-data="{ showKeyboard: false }"
                                                                                        class="relative"
                                                                                        @close-virtual-keyboard.window="showKeyboard=false;"
                                                                                    >
                                                                                        <x-tollerus::inputs.button
                                                                                            x-cloak x-show="!showKeyboard"
                                                                                            type="secondary"
                                                                                            size="small"
                                                                                            class="align-middle"
                                                                                            title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                                                            @click="
                                                                                                editing=true;
                                                                                                $nextTick(()=>{
                                                                                                    showKeyboard=true;
                                                                                                    $store.virtualKeyboard.mount({
                                                                                                        virtualKeyboardType: 'native',
                                                                                                        neographySubset: ['{{ $neography->id }}'],
                                                                                                        mountPoint: $el.parentNode,
                                                                                                        inputFieldId: id
                                                                                                    });
                                                                                                });
                                                                                            "
                                                                                        >
                                                                                            <x-tollerus::icons.keyboard/>
                                                                                            <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                                                        </x-tollerus::inputs.button>
                                                                                        <x-tollerus::inputs.button
                                                                                            x-cloak x-show="showKeyboard"
                                                                                            type="primary"
                                                                                            size="small"
                                                                                            class="align-middle"
                                                                                            title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                                                            @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                                                        >
                                                                                            <x-tollerus::icons.keyboard/>
                                                                                            <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                                                        </x-tollerus::inputs.button>
                                                                                    </div>
                                                                                @endif
                                                                            </x-slot:before>
                                                                        </x-tollerus::inputs.text-saveable>
                                                                    </div>
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
                                                            @foreach ($allSects as $destSect)
                                                                <div
                                                                    wire:key="glyph-dest-sect-{{ $destSect['id'] }}"
                                                                    class="flex flex-col items-start"
                                                                >
                                                                    <span class="italic opacity-50">{{ $destSect['name'] }}</span>
                                                                    @foreach ($destSect['groups'] as $destGroup)
                                                                        @if (collect($destGroup['glyphs'])->contains($glyph['glyph']))
                                                                            <x-tollerus::inputs.button
                                                                                wire:key="glyph-dest-group-{{ $destGroup['id'] }}"
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4 line-through"
                                                                                disabled
                                                                            >{{ __('tollerus::ui.group_nameless') }} - {{ count($destGroup['glyphs']) }} {{ __('tollerus::ui.glyphs') }}</x-tollerus::inputs.button>
                                                                        @else
                                                                            <x-tollerus::inputs.button
                                                                                wire:key="glyph-dest-group-{{ $destGroup['id'] }}"
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4"
                                                                                @click="
                                                                                    open=false;
                                                                                    $wire.transferGlyph({{ $groupId }}, {{ $glyphId }}, {{ $destSect['id'] }}, {{ $destGroup['id'] }});
                                                                                    deleteItem('glyph_{{ $glyphId }}');
                                                                                "
                                                                            >{{ __('tollerus::ui.group_nameless') }} - {{ count($destGroup['glyphs']) }} {{ __('tollerus::ui.glyphs') }}</x-tollerus::inputs.button>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </x-tollerus::inputs.dropdown>
                                                    </x-tollerus::panel>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <x-tollerus::inputs.missing-data
                                        size="small"
                                        title="{{ __('tollerus::ui.add_glyph') }}"
                                        class="relative flex flex-row gap-2 justify-center items-center w-full"
                                        @click="$wire.createGlyph({{ $groupId }});"
                                        wire:loading.attr="disabled"
                                        wire:target="createGlyph"
                                    >
                                        <x-tollerus::icons.plus/>
                                        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_glyph') }}</span>
                                    </x-tollerus::inputs.missing-data>
                                </x-tollerus::pane>
                            </x-tollerus::panel>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="px-6 xl:px-0">
                <x-tollerus::inputs.missing-data
                    size="medium" floating="true"
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
    </div>
    <x-tollerus::modal/>
    @if (count($nativeKeyboards) > 0)
        <x-tollerus::keyboards.native :nativeKeyboards="$nativeKeyboards"/>
    @endif
    <x-tollerus::keyboards.phonemic :phonemicKeyboard="$ipaKeyboard" showCanonical="false"/>
</div>
<x-tollerus::reorder-script/>
