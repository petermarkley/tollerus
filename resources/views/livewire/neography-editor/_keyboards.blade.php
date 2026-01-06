<div id="tabpanel-keyboards" role="tabpanel" x-cloak x-show="tab=='keyboards'" class="flex flex-col gap-6 border-t-4 border-white dark:border-zinc-800 pt-4">
    <div class="flex flex-col gap-2 italic text-zinc-700 dark:text-zinc-400 px-6 xl:px-0">
        {!! Str::markdown(__('tollerus::ui.keyboard_tab_description')) !!}
    </div>
    <template x-if="Object.keys(keysForm).length == 0">
        <div class="flex flex-col gap-4 items-start w-full px-6 xl:px-0" x-data="{ btn1: 'extract_from_svg', btn2: 'import_from_glyphs' }">
            <x-tollerus::alert>
                <p class="m-0">{{ __('tollerus::ui.no_keyboard_notice') }}</p>
            </x-tollerus::alert>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2 items-start">
                    <x-tollerus::inputs.button
                        x-text="msgs[btn1]"
                        @click="btn1 = 'extracting'; $wire.extractSvgToKeyboard();"
                        @svgtoglyphs-failure.window="btn1 = 'extract_from_svg';"
                        @svgtoglyphs-success.window="btn1 = 'extract_from_svg';"
                        x-bind:disabled="!fontForm.{{ \PeterMarkley\Tollerus\Enums\FontFormat::Svg->value }}.blobExists"
                        wire:loading.attr="disabled"
                        wire:target="extractSvgToKeyboard"
                    />
                    <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-400">{!! Str::markdown(__('tollerus::ui.no_keyboard_notice_from_svg', [
                        'font_url' => route('tollerus.admin.neographies.edit.tab', ['neography' => $neography, 'tab' => 'font'])
                    ])) !!}</legend></div>
                </div>
                <div class="flex flex-col gap-2 items-start">
                    <x-tollerus::inputs.button
                        x-text="msgs[btn2]"
                        @click="btn2 = 'extracting'; $wire.importGlyphsToKeyboard();"
                        @svgtoglyphs-failure.window="btn2 = 'import_from_glyphs';"
                        @svgtoglyphs-success.window="btn2 = 'import_from_glyphs';"
                        x-bind:disabled="Object.keys(glyphsForm).length == 0"
                        wire:loading.attr="disabled"
                        wire:target="importGlyphsToKeyboard"
                    />
                    <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-400">{!! Str::markdown(__('tollerus::ui.no_keyboard_notice_from_glyphs', [
                        'glyphs_url' => route('tollerus.admin.neographies.edit.tab', ['neography' => $neography, 'tab' => 'glyphs'])
                    ])) !!}</legend></div>
                </div>
            </div>
        </div>
    </template>
    <template x-if="Object.keys(keysForm).length > 0">
        <x-tollerus::drawer open="true" rootClass="w-full" class="flex flex-col gap-4 w-full">
            <x-slot:heading-button>
                <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-zinc-500 dark:bg-zinc-400 group-has-hover:bg-zinc-400 group-has-hover:dark:bg-zinc-300 text-white dark:text-zinc-800">
                    <x-tollerus::icons.eye-slash x-show="!drawerOpen" />
                    <x-tollerus::icons.eye x-show="drawerOpen" />
                    <span>{{ __('tollerus::ui.preview_of_keyboard') }}</span>
                </div>
            </x-slot:heading-button>
            <x-slot:heading>
                <div class="flex-grow border-b-2 border-zinc-500 dark:border-zinc-400"></div>
            </x-slot:heading>
            <div class="flex flex-col gap-1 items-start">
                <textarea
                    id="keyboard_preview_output"
                    x-ref="keyboard_preview_output"
                    rows="3"
                    class="border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600 text-2xl tollerus_{{ $neography->machine_name }}"
                ></textarea>
            </div>
            <div class="flex flex-col gap-4 px-6">
                <template x-for="([keyboardId, keyboard], i) in $store.reorderFunctions.sortItems(keysForm)" :key="keyboardId">
                    <div class="w-full grid gap-1" x-bind:style="
                        'grid-template-columns: repeat(' + keyboard.width + ', minmax(0, 1fr));' +
                        'max-width: min(1200px, ' + 80*keyboard.width + 'px);'
                    ">
                        <template x-for="([keyId, key], i) in $store.reorderFunctions.sortItems(keyboard.keys)" :key="keyId">
                            <div
                                x-data="{ rowCycle: 0 }"
                                x-effect="rowCycle = Math.trunc(i / keyboard.width) % 3;"
                                class="w-full @container"
                            >
                                <button
                                    class="w-full flex flex-col justify-between items-center bg-white dark:bg-zinc-800 rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 cursor-pointer hover:dark:bg-zinc-700"
                                    x-bind:class="{
                                        'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]': rowCycle == 0,
                                        'transform-[translateX(16%)] hover:transform-[translate(16%,-6cqw)] focus:transform-[translate(16%,-6cqw)] active:transform-[translate(16%,6cqw)]': rowCycle == 1,
                                        'transform-[translateX(-16%)] hover:transform-[translate(-16%,-6cqw)] focus:transform-[translate(-16%,-6cqw)] active:transform-[translate(-16%,6cqw)]': rowCycle == 2,
                                    }"
                                    x-bind:data-glyph="key.glyph"
                                    @click="let e = $refs.keyboard_preview_output; e.value = e.value + $el.dataset.glyph;"
                                >
                                    <span x-text="key.label" class="text-[20cqw]"></span>
                                    <template x-if="key.renderBase">
                                        <span x-text="'\u25CC' + key.glyph" class="text-[60cqw] tollerus_{{ $neography->machine_name }}"></span>
                                    </template>
                                    <template x-if="!key.renderBase">
                                        <span x-text="(key.glyph == ' ' ? '&nbsp;' : key.glyph)" class="text-[60cqw] tollerus_{{ $neography->machine_name }}"></span>
                                    </template>
                                    <span x-text="key.glyphHex" class="text-[15cqw] font-mono text-zinc-500 dark:text-zinc-500"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </x-tollerus::drawer>
    </template>
    <template x-if="Object.keys(keysForm).length > 0">
        <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
            <template x-for="([keyboardId, keyboard], i) in $store.reorderFunctions.sortItems(keysForm)" :key="keyboardId">
                <div
                    x-bind:id="'keyboard_' + keyboardId"
                    data-obj="keyboard"
                    class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                    x-bind:style="'order: '+i"
                    @transitionend="$nextTick(() => {animating=false});"
                >
                    <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                        <x-tollerus::inputs.button
                            type="inverse"
                            title="{{ __('tollerus::ui.move_keyboard_up') }}"
                            x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(keysForm, keyboardId)"
                            @click="animating=true; moveKeyboard($el.closest('[data-obj=&quot;keyboard&quot;]'), keyboardId, -1);"
                        >
                            <x-tollerus::icons.chevron-up class="h-8 w-8" />
                            <span class="sr-only">{{ __('tollerus::ui.move_keyboard_up') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            type="inverse"
                            title="{{ __('tollerus::ui.move_keyboard_down') }}"
                            x-bind:disabled="animating || $store.reorderFunctions.isLastItem(keysForm, keyboardId)"
                            @click="animating=true; moveKeyboard($el.closest('[data-obj=&quot;keyboard&quot;]'), keyboardId, +1);"
                        >
                            <x-tollerus::icons.chevron-down class="h-8 w-8" />
                            <span class="sr-only">{{ __('tollerus::ui.move_keyboard_down') }}</span>
                        </x-tollerus::inputs.button>
                    </x-tollerus::panel>
                    <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                        <div class="flex flex-row gap-2 items-center justify-between">
                            <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                                <span class="font-normal italic">{{ __('tollerus::ui.keyboard') }}</span>
                            </h2>
                            <div class="flex flex-row gap-2 items-center">
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.delete_keyboard') }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_keyboard_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'keyboard-delete', payload: {keyboardId: keyboardId} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_keyboard') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </div>
                        <div>
                            <x-tollerus::inputs.text-saveable
                                type="number" min="1" max="40"
                                showLabel="true"
                                idExpression="'keyboard_' + keyboardId + '_width'"
                                model="keyboard.width"
                                fieldName="{{ __('tollerus::ui.width') }}"
                                saveEvent="$wire.updateKeyboard(keyboardId, 'width', document.getElementById(id).value, id);"
                            />
                        </div>
                        <x-tollerus::pane class="flex flex-col gap-4 items-start">
                            <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                <span>{{ __('tollerus::ui.keys') }}</span>
                            </h3>
                            <template x-if="Object.keys(keyboard.keys).length > 0">
                                <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                    <template x-for="([keyId, key], i) in $store.reorderFunctions.sortItems(keyboard.keys)" :key="keyId">
                                        <div
                                            x-bind:id="'key_' + keyId"
                                            data-obj="key"
                                            class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                            x-bind:style="'order: '+i"
                                            @transitionend="$nextTick(() => {animating=false});"
                                        >
                                            <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                <x-tollerus::inputs.button
                                                    type="inverse"
                                                    title="{{ __('tollerus::ui.move_key_earlier') }}"
                                                    x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(keyboard.keys, keyId)"
                                                    @click="animating=true; moveKey(keyboardId, $el.closest('[data-obj=&quot;key&quot;]'), keyId, -1);"
                                                >
                                                    <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                    <span class="sr-only">{{ __('tollerus::ui.move_key_earlier') }}</span>
                                                </x-tollerus::inputs.button>
                                                <x-tollerus::inputs.button
                                                    type="inverse"
                                                    title="{{ __('tollerus::ui.move_key_later') }}"
                                                    x-bind:disabled="animating || $store.reorderFunctions.isLastItem(keyboard.keys, keyId)"
                                                    @click="animating=true; moveKey(keyboardId, $el.closest('[data-obj=&quot;key&quot;]'), keyId, +1);"
                                                >
                                                    <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                    <span class="sr-only">{{ __('tollerus::ui.move_key_later') }}</span>
                                                </x-tollerus::inputs.button>
                                            </x-tollerus::panel>
                                            <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none flex-grow">
                                                <div class="flex flex-row gap-4 justify-between items-start lg:items-center w-full">
                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 flex-grow border-2 rounded-lg p-2 border-zinc-200 dark:border-zinc-600">
                                                        <x-tollerus::inputs.text-saveable
                                                            showLabel="true"
                                                            idExpression="'key_' + keyId + '_unicode'"
                                                            model="key.glyph"
                                                            fieldName="{{ __('tollerus::ui.unicode') }}"
                                                            saveEvent="$wire.updateKey(keyboardId, keyId, 'glyph', document.getElementById(id).value, id);"
                                                            height="67px"
                                                            class="text-6xl tollerus_{{ $neography->machine_name }}"
                                                        />
                                                        <x-tollerus::inputs.text-saveable
                                                            showLabel="true"
                                                            idExpression="'key_' + keyId + '_hex'"
                                                            model="key.glyphHex"
                                                            fieldName="{{ __('tollerus::ui.hexadecimal') }}"
                                                            saveEvent="$wire.updateKey(keyboardId, keyId, 'glyphHex', document.getElementById(id).value, id);"
                                                        />
                                                    </div>
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        size="small"
                                                        class="align-middle"
                                                        title="{{ __('tollerus::ui.delete_key') }}"
                                                        @click="$dispatch('open-modal', {
                                                            message: msgs['delete_key_confirmation'],
                                                            buttons: [
                                                                { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                { text: msgs.yes_delete, type: 'primary', clickEvent: 'key-delete', payload: {keyId: keyId} }
                                                            ]
                                                        });"
                                                    >
                                                        <x-tollerus::icons.delete/>
                                                        <label class="sr-only">{{ __('tollerus::ui.delete_key') }}</label>
                                                    </x-tollerus::inputs.button>
                                                </div>
                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 w-full">
                                                    <x-tollerus::inputs.text-saveable
                                                        showLabel="true"
                                                        idExpression="'key_' + keyId + '_label'"
                                                        model="key.label"
                                                        fieldName="{{ __('tollerus::ui.label') }}"
                                                        saveEvent="$wire.updateKey(keyboardId, keyId, 'label', document.getElementById(id).value, id);"
                                                    />
                                                    <div class="flex flex-row justify-start items-center">
                                                        <x-tollerus::inputs.checkbox
                                                            idExpression="'key_' + keyId + '_render_base'"
                                                            model="key.renderBase"
                                                            modelIsAlpine="true"
                                                            label="{{ __('tollerus::ui.render_on_base') }}"
                                                            @change="$wire.updateKey(keyboardId, keyId, 'renderBase', $el.checked, id);"
                                                        />
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
                                                    <template x-for="([destKeyboardId, destKeyboard], j) in $store.reorderFunctions.sortItems(keysForm)">
                                                        <div class="flex flex-col items-start">
                                                            <x-tollerus::inputs.button
                                                                type="inverse"
                                                                size="small"
                                                                x-bind:class="{'ml-4': true, 'line-through': destKeyboardId == keyboardId}"
                                                                x-bind:disabled="destKeyboardId == keyboardId"
                                                                x-text="msgs['keyboard'] + ' - ' + Object.keys(destKeyboard.keys).length + ' ' + msgs['keys']"
                                                                @click="open=false; $wire.transferKey(keyboardId, keyId, destKeyboardId);"
                                                            />
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
                                title="{{ __('tollerus::ui.add_key') }}"
                                class="relative flex flex-row gap-2 justify-center items-center w-full"
                                @click="$wire.createKey(keyboardId);"
                                wire:loading.attr="disabled"
                                wire:target="createKey"
                            >
                                <x-tollerus::icons.plus/>
                                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_key') }}</span>
                            </x-tollerus::inputs.missing-data>
                        </x-tollerus::pane>
                    </x-tollerus::panel>
                </div>
            </template>
        </div>
    </template>
    <div class="px-6 xl:px-0">
        <x-tollerus::inputs.missing-data
            size="medium" floating="true"
            title="{{ __('tollerus::ui.add_keyboard') }}"
            class="relative flex flex-row gap-2 justify-center items-center w-full"
            @click="$wire.createKeyboard();"
            wire:loading.attr="disabled"
            wire:target="createKeyboard"
        >
            <x-tollerus::icons.plus/>
            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_keyboard') }}</span>
        </x-tollerus::inputs.missing-data>
    </div>
</div>
