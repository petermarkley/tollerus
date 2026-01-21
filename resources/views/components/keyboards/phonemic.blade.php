@props([
    'phonemicKeyboard',
])
<template id="phonemic_keyboard">
    <div class="flex flex-col gap-4 px-6" x-data="{ phonemicTab: 'canonical' }">
        <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
            <x-tollerus::inputs.tab
                switcher="phonemicTab"
                tabName="canonical"
                aria-controls="tabpanel-canonical"
                title="{{ __('tollerus::ui.canonical') }}"
                @click="phonemicTab = 'canonical';"
                @keydown.enter.prevent="phonemicTab = 'canonical';"
                @keydown.space.prevent="phonemicTab = 'canonical';"
            >
                <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.canonical') }}</span>
            </x-tollerus::inputs.tab>
            @foreach ($phonemicKeyboard['tabs'] as $tab)
                <x-tollerus::inputs.tab
                    switcher="phonemicTab"
                    tabName="{{ $tab['key'] }}"
                    aria-controls="tabpanel-{{ $tab['key'] }}"
                    title="{{ $tab['label'] }}"
                    @click="phonemicTab = '{{ $tab['key'] }}';"
                    @keydown.enter.prevent="phonemicTab = '{{ $tab['key'] }}';"
                    @keydown.space.prevent="phonemicTab = '{{ $tab['key'] }}';"
                >
                    <span class="sr-only md:not-sr-only">{{ $tab['label'] }}</span>
                </x-tollerus::inputs.tab>
            @endforeach
        </ul>
        <div
            id="tabpanel-canonical"
            role="tabpanel"
            x-cloak x-show="phonemicTab=='canonical'"
            class="w-full grid grid-cols-20 gap-1"
        ></div>
        @foreach ($phonemicKeyboard['tabs'] as $tab)
            @php
                $width = 20;
            @endphp
            <div
                id="tabpanel-{{ $tab['key'] }}"
                role="tabpanel"
                x-cloak x-show="phonemicTab=='{{ $tab['key'] }}'"
                class="w-full grid gap-1"
                style="grid-template-columns: repeat({{ $width }}, minmax(0, 1fr));"
            >
                @foreach ($tab['glyphs'] as $i => $glyph)
                    @php
                        $rowCycle = floor($i / $width) % 3;
                    @endphp
                    <div class="w-full @container">
                        <button
                            @class([
                                'w-full flex flex-col justify-between items-center bg-white dark:bg-zinc-800 rounded-[20cqw] shadow/40 hover:shadow-lg/20 focus:shadow-lg/20 active:shadow-sm/80 p-1 border border-b-[10cqw] border-zinc-400 dark:border-zinc-600 hover:bg-zinc-100 cursor-pointer hover:dark:bg-zinc-700',
                                'hover:transform-[translateY(-6cqw)] focus:transform-[translateY(-6cqw)] active:transform-[translateY(6cqw)]' => $rowCycle==0,
                                'transform-[translateX(16%)] hover:transform-[translate(16%,-6cqw)] focus:transform-[translate(16%,-6cqw)] active:transform-[translate(16%,6cqw)]' => $rowCycle==1,
                                'transform-[translateX(-16%)] hover:transform-[translate(-16%,-6cqw)] focus:transform-[translate(-16%,-6cqw)] active:transform-[translate(-16%,6cqw)]' => $rowCycle==2,
                            ])
                            data-glyph="{{ $glyph->glyph }}"
                            @click="$store.phonemicKeyboard.click"
                        >
                            <span class="text-[20cqw]">{{ $glyph->label }}</span>
                            @if ($glyph->render_on_base)
                                <span class="text-[60cqw]">&#x25CC;{{ $glyph->glyph }}</span>
                            @else
                                <span class="text-[60cqw]">{{ ($glyph->glyph==' '? '&nbsp;' : $glyph->glyph) }}</span>
                            @endif
                            <span class="text-[15cqw] font-mono text-zinc-500 dark:text-zinc-500">{{ $glyph->hex }}</span>
                        </button>
                    </div>
                @endforeach
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
