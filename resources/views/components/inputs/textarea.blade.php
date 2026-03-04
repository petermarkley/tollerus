@props([
  'id' => '',
  'label' => '',
  'model' => '',
  'rows' => 10,
  'monospace' => false,
  'wysiwyg' => false,
  'nativeKeyboards' => null,
  'language' => null,
])
<div
    class="flex flex-col gap-1 items-start"
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        wire:ignore
        data-tollerus-wysiwyg
        x-data="tollerusWysiwyg({
            state: $wire.entangle('{{ $model }}'),
        })"
        @tollerus-wysiwyg-toolbar="handleToolbar($event.detail.action)"
        @tollerus-wysiwyg-link-apply.window="applyLink($event.detail)"
        @tollerus-wysiwyg-link-remove.window="removeLink()"
        @tollerus-wysiwyg-phonemic-apply.window="applyPhonemic($event.detail)"
        @tollerus-wysiwyg-native-apply.window="applyNative($event.detail)"
    @endif
>
    <label for="{{ $id }}">{{ $label }}</label>
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        <div class="w-full flex flex-col items-stretch">
            <div class="w-full p-2 flex flex-row gap-1 justify-between items-center rounded-t-lg border rounded-b border-zinc-400 dark:border-zinc-600">
                <div class="flex flex-row gap-1 items-center">
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('bold')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.bold') }}"
                            x-bind:disabled="rawMode || isExcluded('bold')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'bold' })"
                        >
                            <x-tollerus::icons.micro.bold class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bold') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('bold')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.bold') }}"
                            x-bind:disabled="rawMode || isExcluded('bold')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'bold' })"
                        >
                            <x-tollerus::icons.micro.bold class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bold') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('italic')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.italic') }}"
                            x-bind:disabled="rawMode || isExcluded('italic')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'italic' })"
                        >
                            <x-tollerus::icons.micro.italic class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.italic') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('italic')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.italic') }}"
                            x-bind:disabled="rawMode || isExcluded('italic')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'italic' })"
                        >
                            <x-tollerus::icons.micro.italic class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.italic') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('tollerusSmallcaps')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.smallcaps') }}"
                            x-bind:disabled="rawMode || isExcluded('tollerusSmallcaps')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'smallcaps' })"
                        >
                            <x-tollerus::icons.micro.smallcaps class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.smallcaps') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('tollerusSmallcaps')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.smallcaps') }}"
                            x-bind:disabled="rawMode || isExcluded('tollerusSmallcaps')"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'smallcaps' })"
                        >
                            <x-tollerus::icons.micro.smallcaps class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.smallcaps') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <x-tollerus::inputs.dropdown class="relative w-full">
                        <x-slot:button>
                            <x-tollerus::inputs.button
                                x-show="!isActive('link')"
                                type="inverse"
                                size="tiny"
                                title="{{ __('tollerus::ui.link') }}"
                                x-bind:disabled="rawMode || isExcluded('link')"
                                class="relative"
                                @click="open=true; $dispatch('tollerus-wysiwyg-toolbar', { action: 'link' });"
                            >
                                <x-tollerus::icons.micro.link class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.link') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                x-show="isActive('link')" x-cloak
                                type="inverse-highlight"
                                size="tiny"
                                title="{{ __('tollerus::ui.link') }}"
                                x-bind:disabled="rawMode || isExcluded('link')"
                                class="relative"
                                @click="open=true; $dispatch('tollerus-wysiwyg-toolbar', { action: 'link' });"
                            >
                                <x-tollerus::icons.micro.link class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.link') }}</span>
                            </x-tollerus::inputs.button>
                        </x-slot:button>
                        <div
                            x-data="{
                                linkUrl: '',
                                linkText: '',
                                existingLink: false,
                            }"
                            @tollerus-wysiwyg-link-dialog-open.window="
                                linkUrl = $event.detail.href;
                                linkText = $event.detail.text;
                                existingLink = $event.detail.active;
                            "
                            class="w-full flex flex-col gap-2 items-stretch"
                        >
                            <div class="w-full">
                                <x-tollerus::inputs.text
                                    label="{{ __('tollerus::ui.link_url') }}"
                                    id="{{ $id . '_link_url' }}"
                                    model="linkUrl"
                                    modelIsAlpine="true"
                                />
                            </div>
                            <div class="w-full">
                                <x-tollerus::inputs.text
                                    label="{{ __('tollerus::ui.link_text') }}"
                                    id="{{ $id . '_link_text' }}"
                                    model="linkText"
                                    modelIsAlpine="true"
                                />
                            </div>
                            <div class="w-full flex flex-row gap-2 justify-start">
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.remove') }}"
                                    x-bind:disabled="!existingLink"
                                    @click="open=false; $dispatch('tollerus-wysiwyg-link-remove');"
                                >
                                    <span>{{ __('tollerus::ui.remove') }}</span>
                                </x-tollerus::inputs.button>
                                <x-tollerus::inputs.button
                                    type="primary"
                                    size="small"
                                    title="{{ __('tollerus::ui.apply') }}"
                                    @click="open=false; $dispatch('tollerus-wysiwyg-link-apply', {
                                        href: linkUrl,
                                        text: linkText,
                                    });"
                                >
                                    <span>{{ __('tollerus::ui.apply') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </div>
                    </x-tollerus::inputs.dropdown>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('bullet_list')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.bullet_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'bullet_list' })"
                        >
                            <x-tollerus::icons.micro.list-bullet class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bullet_list') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('bullet_list')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.bullet_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'bullet_list' })"
                        >
                            <x-tollerus::icons.micro.list-bullet class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bullet_list') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('numbered_list')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.numbered_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'numbered_list' })"
                        >
                            <x-tollerus::icons.micro.list-numbered class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.numbered_list') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('numbered_list')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.numbered_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'numbered_list' })"
                        >
                            <x-tollerus::icons.micro.list-numbered class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.numbered_list') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('tollerusWord')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.conlang_word') }}"
                            x-bind:disabled="rawMode || isExcluded('tollerusWord')"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.language class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.conlang_word') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('tollerusWord')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.conlang_word') }}"
                            x-bind:disabled="rawMode || isExcluded('tollerusWord')"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.language class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.conlang_word') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <x-tollerus::inputs.dropdown class="relative w-full">
                        <x-slot:button>
                            <x-tollerus::inputs.button
                                x-show="!isActive('tollerusPhonemic')"
                                type="inverse"
                                size="tiny"
                                title="{{ __('tollerus::ui.phonemic') }}"
                                x-bind:disabled="rawMode || isExcluded('tollerusPhonemic')"
                                class="relative"
                                @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'phonemic' })"
                            >
                                <x-tollerus::icons.micro.speech class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.phonemic') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                x-show="isActive('tollerusPhonemic')" x-cloak
                                type="inverse-highlight"
                                size="tiny"
                                title="{{ __('tollerus::ui.phonemic') }}"
                                x-bind:disabled="rawMode || isExcluded('tollerusPhonemic')"
                                class="relative"
                                @click="$dispatch('tollerus-wysiwyg-toolbar', { action: 'phonemic' })"
                            >
                                <x-tollerus::icons.micro.speech class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.phonemic') }}</span>
                            </x-tollerus::inputs.button>
                        </x-slot:button>
                        <div
                            x-data="{ phonemicText: '' }"
                            @tollerus-wysiwyg-phonemic-dialog-open.window="open=true; phonemicText = '';"
                            class="w-full flex flex-col gap-2 items-stretch"
                        >
                            <div data-keyboard-elem="territory" class="w-full flex flex-col gap-1 items-start">
                                <label for="{{ $id . '_phonemic_text' }}">{{ __('tollerus::ui.text') }}</label>
                                <div class="w-full flex flex-row gap-1 items-center">
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
                                                $nextTick(()=>{
                                                    showKeyboard=true;
                                                    $store.virtualKeyboard.mount({
                                                        virtualKeyboardType: 'phonemic',
                                                        mountPoint: $el.parentNode,
                                                        inputFieldId: '{{ $id . '_phonemic_text' }}'
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
                                    <x-tollerus::inputs.text
                                        id="{{ $id . '_phonemic_text' }}"
                                        model="phonemicText"
                                        modelIsAlpine="true"
                                    />
                                </div>
                            </div>
                            <div class="w-full flex flex-row gap-2 justify-start">
                                <x-tollerus::inputs.button
                                    type="primary"
                                    size="small"
                                    title="{{ __('tollerus::ui.insert') }}"
                                    @click="open=false; $dispatch('tollerus-wysiwyg-phonemic-apply', { text: phonemicText });"
                                >
                                    <span>{{ __('tollerus::ui.insert') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </div>
                    </x-tollerus::inputs.dropdown>
                    <x-tollerus::inputs.dropdown class="relative w-full">
                        <x-slot:button>
                            <x-tollerus::inputs.button
                                x-show="!isActive('tollerusNative')"
                                type="inverse"
                                size="tiny"
                                title="{{ __('tollerus::ui.neography_letters') }}"
                                x-bind:disabled="rawMode || isExcluded('tollerusNative')"
                                class="relative"
                                @click="open=true; $dispatch('tollerus-wysiwyg-toolbar', { action: 'native' });"
                            >
                                <x-tollerus::icons.micro.neography class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.neography_letters') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                x-show="isActive('tollerusNative')" x-cloak
                                type="inverse-highlight"
                                size="tiny"
                                title="{{ __('tollerus::ui.neography_letters') }}"
                                x-bind:disabled="rawMode || isExcluded('tollerusNative')"
                                class="relative"
                                @click="open=true; $dispatch('tollerus-wysiwyg-toolbar', { action: 'native' });"
                            >
                                <x-tollerus::icons.micro.neography class="sm:h-6" />
                                <span class="sr-only">{{ __('tollerus::ui.neography_letters') }}</span>
                            </x-tollerus::inputs.button>
                        </x-slot:button>
                        <div
                            x-data="{
                                neographyId: '{{ $primaryNeographyId ?? array_keys($nativeKeyboards)[0] }}',
                                neographyMachineNames: @js(array_map(fn ($n) => $n['machineName'], $nativeKeyboards)),
                                nativeText: '',
                                get neographyMachineName() {
                                    return this.neographyMachineNames[this.neographyId];
                                },
                            }"
                            @tollerus-wysiwyg-native-dialog-open.window="
                                if ($event.detail.neographyId) {
                                    neographyId = $event.detail.neographyId;
                                }
                                nativeText = $event.detail.text;
                            "
                            @native-keyboard-tab-switch="neographyId = $event.detail.id;"
                            class="w-full flex flex-col gap-2 items-stretch"
                        >
                            <div class="w-full">
                                <x-tollerus::inputs.select
                                    idExpression="'{{ $id . '_native_neography' }}'"
                                    label="{{ __('tollerus::ui.neography') }}"
                                    model="neographyId"
                                >
                                    @foreach ($nativeKeyboards as $keyboardNeographyId => $keyboardNeography)
                                        <option value="{{ $keyboardNeographyId }}" class="cursor-pointer">{{ $keyboardNeography['name'] }}</option>
                                    @endforeach
                                </x-tollerus::inputs.select>
                            </div>
                            <div data-keyboard-elem="territory" class="w-full flex flex-col gap-1 items-start">
                                <label for="{{ $id . '_native_text' }}">{{ __('tollerus::ui.text') }}</label>
                                <div class="w-full flex flex-row gap-1 items-center">
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
                                                $nextTick(()=>{
                                                    showKeyboard=true;
                                                    $store.virtualKeyboard.mount({
                                                        virtualKeyboardType: 'native',
                                                        neographySubset: null,
                                                        activeNeography: neographyId,
                                                        mountPoint: $el.parentNode,
                                                        inputFieldId: '{{ $id . '_native_text' }}'
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
                                    <x-tollerus::inputs.text
                                        id="{{ $id . '_native_text' }}"
                                        x-bind:class="'tollerus_'+neographyMachineName"
                                        model="nativeText"
                                        modelIsAlpine="true"
                                    />
                                </div>
                            </div>
                            <div class="w-full flex flex-row gap-2 justify-start">
                                <x-tollerus::inputs.button
                                    type="primary"
                                    size="small"
                                    title="{{ __('tollerus::ui.apply') }}"
                                    @click="open=false; $dispatch('tollerus-wysiwyg-native-apply', {
                                        neographyId: neographyId,
                                        neography: neographyMachineName,
                                        text: nativeText,
                                    });"
                                >
                                    <span>{{ __('tollerus::ui.apply') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </div>
                    </x-tollerus::inputs.dropdown>
                </div>
                <div class="flex flex-row gap-1 items-center">
                    <x-tollerus::inputs.button
                        x-show="!rawMode"
                        type="inverse"
                        size="tiny"
                        title="{{ __('tollerus::ui.edit_as_raw_html') }}"
                        class="relative"
                        @click="rawMode = true"
                    >
                        <x-tollerus::icons.micro.code class="sm:h-6" />
                        <span class="sr-only">{{ __('tollerus::ui.edit_as_raw_html') }}</span>
                    </x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        x-show="rawMode" x-cloak
                        type="primary"
                        size="tiny"
                        title="{{ __('tollerus::ui.edit_as_rendered_html') }}"
                        class="relative"
                        @click="rawMode = false"
                    >
                        <x-tollerus::icons.micro.code class="sm:h-6" />
                        <span class="sr-only">{{ __('tollerus::ui.edit_as_rendered_html') }}</span>
                    </x-tollerus::inputs.button>
                </div>
            </div>
            <div
                data-tollerus-wysiwyg-mount
                x-show="!rawMode"
                class="w-full border p-2 w-full rounded-b-lg rounded-t inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600"
            ></div>
            <textarea
                x-show="rawMode" x-cloak
                id="{{ $id }}"
                wire:model.defer="{{ $model }}"
                rows="{{ $rows }}"
                {{ $attributes }}
                class="font-mono border p-2 w-full rounded-b-lg rounded-t inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"
            ></textarea>
        </div>
    @else
        <textarea id="{{ $id }}" wire:model.defer="{{ $model }}" rows="{{ $rows }}" {{ $attributes }} class="@if(filter_var($monospace, FILTER_VALIDATE_BOOLEAN)) font-mono @endif border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"></textarea>
        @error($model)
            <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
        @enderror
    @endif
    <livewire:tollerus.word-picker :language="$language" selectedWordId="AAR3" :softLimitToParticles="true" />
</div>
