@props([
    'phonemicKeyboard',
    'showCanonical' => true,
])
<template id="phonemic_keyboard">
    <div
        x-data="{ phonemicTab: '{{ (filter_var($showCanonical, FILTER_VALIDATE_BOOLEAN) ? 'canonical' : 'consonants') }}' }"
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
        <ul class="px-4 flex flex-row flex-wrap md:flex-nowrap gap-2 md:gap-4 justify-start items-end border-b-2 border-zinc-500 dark:border-zinc-400 pb-2 md:pb-0" role="tablist">
            @if (filter_var($showCanonical, FILTER_VALIDATE_BOOLEAN))
                <li
                    role="tab"
                    x-bind:aria-selected="phonemicTab == 'canonical'"
                    tabindex="0"
                    aria-controls="tabpanel-canonical"
                    title="{{ __('tollerus::ui.canonical') }}"
                    @click="phonemicTab = 'canonical';"
                    @keydown.enter.prevent="phonemicTab = 'canonical';"
                    @keydown.space.prevent="phonemicTab = 'canonical';"
                    x-bind:class="{
                        'rounded-t-lg rounded-b-lg md:rounded-b-none flex flex-row justify-start items-center gap-2 cursor-pointer py-1 px-2 flex focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white': true,
                        'text-white dark:text-zinc-900 font-bold border-2 mb-[-2px] border-zinc-500 dark:border-zinc-400 hover:border-zinc-600 hover:dark:border-white bg-zinc-500 dark:bg-zinc-400 hover:bg-zinc-600 hover:dark:bg-white': phonemicTab!='canonical',
                        'text-zinc-900 dark:text-zinc-300 border-2 mb-[-2px] border-t-zinc-500 dark:border-t-zinc-400 border-x-zinc-500 dark:border-x-zinc-400 border-b-zinc-500 dark:border-b-zinc-400 md:border-b-white md:dark:border-b-zinc-800 bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': phonemicTab=='canonical'
                    }"
                >
                    <span>{{ __('tollerus::ui.canonical') }}</span>
                </li>
            @endif
            @foreach ($phonemicKeyboard['tabs'] as $tab)
                <li
                    role="tab"
                    x-bind:aria-selected="phonemicTab == '{{ $tab['key'] }}'"
                    tabindex="0"
                    aria-controls="tabpanel-{{ $tab['key'] }}"
                    title="{{ $tab['label'] }}"
                    @click="phonemicTab = '{{ $tab['key'] }}';"
                    @keydown.enter.prevent="phonemicTab = '{{ $tab['key'] }}';"
                    @keydown.space.prevent="phonemicTab = '{{ $tab['key'] }}';"
                    x-bind:class="{
                        'rounded-t-lg rounded-b-lg md:rounded-b-none flex flex-row justify-start items-center gap-2 cursor-pointer py-1 px-2 flex focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white': true,
                        'text-white dark:text-zinc-900 font-bold border-2 mb-[-2px] border-zinc-500 dark:border-zinc-400 hover:border-zinc-600 hover:dark:border-white bg-zinc-500 dark:bg-zinc-400 hover:bg-zinc-600 hover:dark:bg-white': phonemicTab!='{{ $tab['key'] }}',
                        'text-zinc-900 dark:text-zinc-300 border-2 mb-[-2px] border-t-zinc-500 dark:border-t-zinc-400 border-x-zinc-500 dark:border-x-zinc-400 border-b-zinc-500 dark:border-b-zinc-400 md:border-b-white md:dark:border-b-zinc-800 bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': phonemicTab=='{{ $tab['key'] }}'
                    }"
                >
                    <span>{{ $tab['label'] }}</span>
                </li>
            @endforeach
        </ul>
        @if (filter_var($showCanonical, FILTER_VALIDATE_BOOLEAN))
            @php
                $width = max(10, ceil(sqrt(count($phonemicKeyboard['canonical']))));
            @endphp
            <div
                id="tabpanel-canonical"
                role="tabpanel"
                x-cloak x-show="phonemicTab=='canonical'"
                class="w-full grid gap-1"
                style="
                    grid-template-columns: repeat({{ $width }}, minmax(0, 1fr));
                    max-width: min(1200px, {{ (80*$width) }}px);
                "
            >
                @foreach ($phonemicKeyboard['canonical'] as $glyph)
                    <div class="w-full @container">
                        <button
                            @class([
                                'w-full flex flex-col justify-between items-center bg-white dark:bg-zinc-800 rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 cursor-pointer hover:dark:bg-zinc-700',
                                'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]',
                            ])
                            data-glyph="{{ $glyph->glyph }}"
                            title="{{ $glyph->labelTranslated }}"
                            @click="$store.phonemicKeyboard.click($event);"
                        >
                            <span class="sr-only" @if(!$glyph->recognized) lang="en" @endif>{{ $glyph->labelTranslated }}</span>
                            @if ($glyph->render_on_base)
                                <span class="text-[60cqw]">&#x25CC;{{ $glyph->glyph }}</span>
                            @else
                                <span class="text-[60cqw]">{{ ($glyph->glyph==' '? '&nbsp;' : $glyph->glyph) }}</span>
                            @endif
                            <span class="text-[15cqw] font-mono text-zinc-500 dark:text-zinc-500 line-clamp-1">{{ $glyph->hex }}</span>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
        @foreach ($phonemicKeyboard['tabs'] as $tab)
            @php
                $height = collect($tab['glyphs'])->max('row');
                $width = collect($tab['glyphs'])->max('col');
                $glyphsArePaired = in_array($tab['key'], ['consonants', 'vowels', 'diacritics', 'tones']);
            @endphp
            <div
                id="tabpanel-{{ $tab['key'] }}"
                role="tabpanel"
                x-cloak x-show="phonemicTab=='{{ $tab['key'] }}'"
                class="w-full grid gap-1"
                style="
                    grid-template-columns: repeat({{ $width }}, minmax(0, 1fr));
                    max-width: min(1200px, {{ (80*$width) }}px);
                "
            >
                @for ($y=1; $y <= $height; $y++)
                    @for ($x=1; $x <= $width; $x++)
                        @php
                            $glyph = collect($tab['glyphs'])->first(fn ($g) => ($g->col==$x && $g->row==$y));
                        @endphp
                        <div
                            @class([
                                '@container',
                                'w-full' => !$glyphsArePaired,
                                'w-[90%]' => $glyphsArePaired,
                                'bg-zinc-50/70 dark:bg-zinc-900/20 rounded-[10%] m-[1px]' => $glyph === null,
                                'rounded-l-[30%] ml-1' => $glyphsArePaired && ($x%2 == 1),
                                'rounded-r-[30%] mr-1' => $glyphsArePaired && ($x%2 == 0),
                            ])
                        >
                            @if ($glyph !== null)
                                <button
                                    @class([
                                        'w-full flex flex-col justify-between items-center rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] cursor-pointer',
                                        'text-zinc-900 dark:text-zinc-300 bg-white dark:bg-zinc-800 border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 hover:dark:bg-zinc-700' => !$glyph->isCanonical,
                                        'text-cyan-900 dark:text-cyan-300 bg-cyan-100 dark:bg-cyan-950 saturate-50 dark:saturate-30 border-cyan-400 dark:border-cyan-600 hover:bg-cyan-100 hover:dark:bg-cyan-700' => $glyph->isCanonical,
                                        'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]',
                                        'rounded-l-[30cqw] rounded-r-[10cqw] ml-1' => $glyphsArePaired && ($x%2 == 1),
                                        'rounded-l-[10cqw] rounded-r-[30cqw] mr-1' => $glyphsArePaired && ($x%2 == 0),
                                    ])
                                    data-glyph="{{ $glyph->glyph }}"
                                    title="{{ $glyph->labelTranslated }}"
                                    @click="$store.phonemicKeyboard.click($event);"
                                >
                                    <span class="sr-only">{{ $glyph->labelTranslated }}</span>
                                    @if ($glyph->render_on_base)
                                        <span class="text-[60cqw]">&#x25CC;{{ $glyph->glyph }}</span>
                                    @else
                                        <span class="text-[60cqw]">{{ ($glyph->glyph==' '? '&nbsp;' : $glyph->glyph) }}</span>
                                    @endif
                                    <span @class([
                                        'text-[15cqw] font-mono line-clamp-1',
                                        'text-zinc-500 dark:text-zinc-500' => !$glyph->isCanonical,
                                        'text-cyan-500 dark:text-cyan-500' => $glyph->isCanonical,
                                    ])>{{ $glyph->hex }}</span>
                                </button>
                            @endif
                        </div>
                    @endfor
                @endfor
            </div>
        @endforeach
    </div>
</template>

@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('phonemicKeyboard', {
        mountPoint: null,
        mountTerritory: null,
        mountElem: null,
        inputField: null,
        mount(mountPoint, inputFieldId) {
            if (this.mountElem !== null) {
                this.unmount();
            }
            const template = document.getElementById('phonemic_keyboard');
            if (template === null || mountPoint === null) {
                return;
            }
            // Store values
            this.mountPoint = mountPoint;
            this.mountTerritory = mountPoint.closest('[data-keyboard-elem="territory"]');
            this.inputField = document.getElementById(inputFieldId);
            if (this.mountTerritory === null || this.inputField === null) {
                return;
            }
            // Retrieve info
            const clone = template.content.cloneNode(true);
            this.mountElem = clone.querySelector('*');
            // Mount keyboard
            this.mountPoint.appendChild(clone);
            this.calculatePosition();
            this.mountElem.focus();

            /**
             * Set event listeners
             * ===================
             */

            /**
             * If the user tabs out of the relevant area, close the keyboard.
             */
            const onFocusin = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                if (event.target !== null && !(this.mountTerritory.contains(event.target) || event.target.contains(this.mountTerritory))) {
                    window.removeEventListener('focusin', onFocusin);
                    this.unmount();
                }
            };
            window.addEventListener('focusin', onFocusin);

            /**
             * If the user clicks a non-keyboard button, or clicks outside the
             * relevant area, then close the keyboard.
             */
            const onClick = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                // We need to check all the buttons inside the mount territory
                let clickedNonKeyboardButton = false;
                const buttonList = this.mountTerritory.querySelectorAll('button');
                for (let i=0; buttonList!==null && i < buttonList.length; i++) {
                    if (this.mountElem.contains(buttonList[i])) {
                        // Skip actual keyboard buttons
                        continue;
                    }
                    if (event.target === buttonList[i] || buttonList[i].contains(event.target)) {
                        clickedNonKeyboardButton = true;
                    }
                }
                if (clickedNonKeyboardButton || !this.mountTerritory.contains(event.target)) {
                    window.removeEventListener('click', onClick);
                    this.unmount();
                }
            };
            window.addEventListener('click', onClick, {capture: true}); // If we let this bubble, we get redundant events due to Alpine DOM updates

            /**
             * If the user presses escape, close the keyboard.
             */
            const onKeydown = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                if (event.key === 'Escape' || event.key === 'Esc') {
                    window.removeEventListener('keydown', onKeydown);
                    this.unmount();
                }
            };
            window.addEventListener('keydown', onKeydown);
        },
        unmount() {
            if (this.mountElem === null) {
                return;
            }
            window.dispatchEvent(new CustomEvent('close-phonemic-keyboard'));
            this.mountElem.remove();
            this.mountElem = null;
            this.mountPoint = null;
            this.mountTerritory = null;
            this.inputField = null;
        },
        calculatePosition() {
            const targetRect = this.mountPoint.getBoundingClientRect();
            this.mountElem.style.left = "-"+targetRect.x+"px";
            const tail = this.mountElem.querySelector('[data-keyboard-elem="paneltail"]');
            if (tail !== null) {
                const offset = targetRect.x + (targetRect.width/2.0);
                tail.style.left = offset.toString() + "px";
            }
        },
        click(e) {
            if (typeof e.target.dataset.glyph === "undefined") {
                var key = e.target.closest('[data-glyph]');
            } else {
                var key = e.target;
            }
            if (key === null) {
                return;
            }
            if (this.inputField !== null) {
                this.inputField.value = this.inputField.value + key.dataset.glyph;
            }
        },
    });
});
</script>
@endpush
@endonce
