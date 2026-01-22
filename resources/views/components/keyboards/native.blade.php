@props([
    'nativeKeyboards',
])
@foreach ($nativeKeyboards as $keyboardNeographyId => $keyboardNeography)
    <template id="{{ 'keyboard_for_'.$keyboardNeographyId }}">
        <div
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
<x-tollerus::keyboards.script/>
