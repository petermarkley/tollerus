@props([
    'nativeKeyboards',
])
@foreach ($nativeKeyboards as $keyboardNeographyId => $keyboardNeography)
    <template id="{{ 'keyboard_for_'.$keyboardNeographyId }}">
        <div
            tabindex="-1"
            class="w-[100vw] mt-4 absolute flex flex-col gap-4 items-center p-6 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-xl shadow"
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
                                @click="$store.nativeKeyboard.click($event);"
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

@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('nativeKeyboard', {
        mount(neographyId, target) {
            const template = document.getElementById('keyboard_for_'+neographyId);
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
