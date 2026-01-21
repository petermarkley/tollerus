@props([
    'phonemicKeyboard',
    'showCanonical' => true,
])
<template id="phonemic_keyboard">
    <div class="flex flex-col gap-4 px-6" x-data="{ phonemicTab: '{{ (filter_var($showCanonical, FILTER_VALIDATE_BOOLEAN) ? 'canonical' : 'consonants') }}' }">
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
            <div
                id="tabpanel-canonical"
                role="tabpanel"
                x-cloak x-show="phonemicTab=='canonical'"
                class="w-full grid grid-cols-20 gap-1"
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
                            @click="$store.phonemicKeyboard.click"
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
                            $glyph = collect($tab['glyphs'])->firstWhere(fn ($g) => ($g->col==$x && $g->row==$y));
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
                                        'w-full flex flex-col justify-between items-center bg-white dark:bg-zinc-800 rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 cursor-pointer hover:dark:bg-zinc-700',
                                        'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]',
                                        'rounded-l-[30cqw] rounded-r-[10cqw] ml-1' => $glyphsArePaired && ($x%2 == 1),
                                        'rounded-l-[10cqw] rounded-r-[30cqw] mr-1' => $glyphsArePaired && ($x%2 == 0),
                                    ])
                                    data-glyph="{{ $glyph->glyph }}"
                                    title="{{ $glyph->labelTranslated }}"
                                    @click="$store.phonemicKeyboard.click"
                                >
                                    <span class="sr-only">{{ $glyph->labelTranslated }}</span>
                                    @if ($glyph->render_on_base)
                                        <span class="text-[60cqw]">&#x25CC;{{ $glyph->glyph }}</span>
                                    @else
                                        <span class="text-[60cqw]">{{ ($glyph->glyph==' '? '&nbsp;' : $glyph->glyph) }}</span>
                                    @endif
                                    <span class="text-[15cqw] font-mono text-zinc-500 dark:text-zinc-500 line-clamp-1">{{ $glyph->hex }}</span>
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
        mount(target) {
            const template = document.getElementById('phonemic_keyboard');
            if (template === null || target === null) {
                return;
            }
            const clone = template.content.cloneNode(true);
            target.appendChild(clone);
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
            console.log(key.dataset.glyph);
        },
    });
});
</script>
@endpush
@endonce
