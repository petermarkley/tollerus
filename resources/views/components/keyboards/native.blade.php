@props([
    'nativeKeyboards',
    'primaryNeographyId' => null,
])
<template id="native_keyboard">
    <div
        x-data="{
            nativeTab: '{{ $primaryNeographyId ?? array_keys($nativeKeyboards)[0] }}',
            get nativeKeyboardsActive() {
                if (typeof $el.dataset.keyboardActivelist === 'undefined') {
                    return [];
                }
                return JSON.parse($el.dataset.keyboardActivelist);
            },
        }"
        tabindex="-1"
        class="w-[100vw] mt-4 absolute flex flex-col gap-4 items-center p-6 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-xl shadow"
    >
        <div data-keyboard-elem="paneltail" class="absolute -top-4 left-[50%] transform-[translateX(-50%)]">
            <svg viewBox="0 0 24 12" class="block dark:hidden w-8 h-4 text-white">
                <path d="M 2,12 L 12,2 L 22,12" fill="currentColor" stroke-width="1.5" stroke-linejoin="miter" stroke-miterlimit="8" stroke="var(--color-zinc-400)" />
            </svg>
            <svg viewBox="0 0 24 12" class="hidden dark:block w-8 h-4 text-zinc-800">
                <path d="M 2,12 L 12,2 L 22,12" fill="currentColor" stroke-width="1.5" stroke-linejoin="miter" stroke-miterlimit="8" stroke="var(--color-zinc-500)" />
            </svg>
        </div>
        @if (count($nativeKeyboards) > 1)
            <template x-if="nativeKeyboardsActive.length != 1">
                <ul class="px-4 flex flex-row flex-wrap md:flex-nowrap gap-2 md:gap-4 justify-start items-end border-b-2 border-zinc-500 dark:border-zinc-400 pb-2 md:pb-0" role="tablist">
                    @foreach ($nativeKeyboards as $keyboardNeographyId => $keyboardNeography)
                        <template x-if="nativeKeyboardsActive.length==0 || nativeKeyboardsActive.includes('{{ $keyboardNeographyId }}')">
                            <li
                                role="tab"
                                x-bind:aria-selected="nativeTab == '{{ $keyboardNeographyId }}'"
                                tabindex="0"
                                aria-controls="tabpanel-{{ $keyboardNeographyId }}"
                                title="{{ $keyboardNeography['name'] }}"
                                @click="nativeTab = '{{ $keyboardNeographyId }}'; $dispatch('native-keyboard-tab-switch', {id: '{{ $keyboardNeographyId }}'});"
                                @keydown.enter.prevent="nativeTab = '{{ $keyboardNeographyId }}'; $dispatch('native-keyboard-tab-switch', {id: '{{ $keyboardNeographyId }}'});"
                                @keydown.space.prevent="nativeTab = '{{ $keyboardNeographyId }}'; $dispatch('native-keyboard-tab-switch', {id: '{{ $keyboardNeographyId }}'});"
                                x-bind:class="{
                                    'rounded-t-lg rounded-b-lg md:rounded-b-none flex flex-row justify-start items-center gap-2 cursor-pointer py-1 px-2 flex focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white': true,
                                    'text-white dark:text-zinc-900 font-bold border-2 mb-[-2px] border-zinc-500 dark:border-zinc-400 hover:border-zinc-600 hover:dark:border-white bg-zinc-500 dark:bg-zinc-400 hover:bg-zinc-600 hover:dark:bg-white': nativeTab!='{{ $keyboardNeographyId }}',
                                    'text-zinc-900 dark:text-zinc-300 border-2 mb-[-2px] border-t-zinc-500 dark:border-t-zinc-400 border-x-zinc-500 dark:border-x-zinc-400 border-b-zinc-500 dark:border-b-zinc-400 md:border-b-white md:dark:border-b-zinc-800 bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': nativeTab=='{{ $keyboardNeographyId }}'
                                }"
                            >
                                <span>{{ $keyboardNeography['name'] }}</span>
                            </li>
                        </template>
                    @endforeach
                </ul>
            </template>
        @endif
        @foreach ($nativeKeyboards as $keyboardNeographyId => $keyboardNeography)
            <template
                x-init="if (nativeKeyboardsActive.length==1) {
                    nativeTab = nativeKeyboardsActive[0];
                } else if (typeof $el.parentElement.dataset.keyboardActive !== 'undefined') {
                    nativeTab = $el.parentElement.dataset.keyboardActive;
                }"
                x-if="nativeKeyboardsActive.length==0 || nativeKeyboardsActive.includes('{{ $keyboardNeographyId }}')"
            >
                <div
                    id="tabpanel-{{ $keyboardNeographyId }}"
                    role="tabpanel"
                    x-cloak x-show="nativeTab=='{{ $keyboardNeographyId }}'"
                    class="w-full"
                >
                    @foreach ($keyboardNeography['keyboards'] as $keyboard)
                        <div class="w-full grid gap-1" style="
                            grid-template-columns: repeat({{ $keyboard['width'] }}, minmax(0, 1fr));
                            max-width: min(1200px, {{ (80*$keyboard['width']) }}px);
                        ">
                            @foreach ($keyboard['keys'] as $i => $key)
                                @php
                                    $rowCycle = floor($i / $keyboard['width']) % 3;
                                @endphp
                                <div class="w-full @container">
                                    <button
                                        @class([
                                            'w-full flex flex-col justify-between items-center bg-white dark:bg-zinc-800 rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 cursor-pointer hover:dark:bg-zinc-700',
                                            'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]' => $rowCycle==0,
                                            'transform-[translateX(16%)] hover:transform-[translate(16%,-6cqw)] focus:transform-[translate(16%,-6cqw)] active:transform-[translate(16%,6cqw)]' => $rowCycle==1,
                                            'transform-[translateX(-16%)] hover:transform-[translate(-16%,-6cqw)] focus:transform-[translate(-16%,-6cqw)] active:transform-[translate(-16%,6cqw)]' => $rowCycle==2,
                                        ])
                                        data-glyph="{{ $key['glyph'] }}"
                                        @click="$store.virtualKeyboard.click($event);"
                                    >
                                        <span class="text-[20cqw]">{{ $key['label'] }}</span>
                                        @if ($key['render_on_base'])
                                            <span class="text-[60cqw] tollerus_{{ $keyboardNeography['machineName'] }}">&#x25CC;{{ $key['glyph'] }}</span>
                                        @else
                                            <span class="text-[60cqw] tollerus_{{ $keyboardNeography['machineName'] }}">{{ ($key['glyph']==' '? '&nbsp;' : $key['glyph']) }}</span>
                                        @endif
                                        <span class="text-[15cqw] font-mono text-zinc-500 dark:text-zinc-500">{{ $key['glyphHex'] }}</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </template>
        @endforeach
    </div>
</template>
<x-tollerus::keyboards.script/>
