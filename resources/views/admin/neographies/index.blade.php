@use('PeterMarkley\Tollerus\Enums\WritingDirection')
<x-tollerus::layout>
    <x-slot name="title">{{ __('tollerus::ui.neographies') }}</x-slot>
    <h1 class="font-bold text-2xl mb-4">{{ __('tollerus::ui.neographies') }}</h1>
    <div class="flex flex-col gap-4 items-stretch">
        @foreach ($neographies as $neography)
            <x-tollerus::panel class="flex flex-col gap-2">
                <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                    <x-tollerus::icons.neography class="h-8"/>
                    <span>{{ $neography->name }}</span>
                </h2>
                <div class="flex flex-row justify-start gap-4">
                    @php
                        switch ($neography->direction_primary) {
                            case WritingDirection::LeftToRight:
                                $flexStr = 'flex-row';
                                $flexStr .= match ($neography->direction_secondary) {
                                    WritingDirection::TopToBottom => ' flex-wrap',
                                    WritingDirection::BottomToTop => ' flex-wrap-reverse',
                                };
                            break;
                            case WritingDirection::RightToLeft:
                                $flexStr = 'flex-row-reverse';
                                $flexStr .= match ($neography->direction_secondary) {
                                    WritingDirection::TopToBottom => ' flex-wrap',
                                    WritingDirection::BottomToTop => ' flex-wrap-reverse',
                                };
                            break;
                            case WritingDirection::TopToBottom:
                                $flexStr = 'flex-col';
                                $flexStr .= match ($neography->direction_secondary) {
                                    WritingDirection::LeftToRight => ' flex-wrap',
                                    WritingDirection::RightToLeft => ' flex-wrap-reverse',
                                };
                            break;
                            case WritingDirection::BottomToTop:
                                $flexStr = 'flex-col-reverse';
                                $flexStr .= match ($neography->direction_secondary) {
                                    WritingDirection::LeftToRight => ' flex-wrap',
                                    WritingDirection::RightToLeft => ' flex-wrap-reverse',
                                };
                            break;
                        }
                    @endphp
                    @if (count($glyphPreview[$neography->machine_name]) > 0)
                        <div class="p-4 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 w-full max-h-28 overflow-hidden" role="img" aria-label="{{ __('tollerus::ui.glyphs') }}">
                            <div class="w-full max-h-28 flex {{ $flexStr }} justify-start items-baseline gap-2 mask-b-to-85%">
                                @foreach ($glyphPreview[$neography->machine_name] as $glyph)
                                    {{-- Controller generates these with classes: 'h-12 w-auto' --}}
                                    {!! $glyph['svg'] !!}
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40">{{ __('tollerus::ui.no_glyphs') }}</p>
                        </div>
                    @endif
                </div>
            </x-tollerus::panel>
        @endforeach
    </div>
</x-tollerus::layout>
