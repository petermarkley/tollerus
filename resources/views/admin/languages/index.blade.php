<x-tollerus::layout>
    <x-slot name="title">{{ __('tollerus::ui.languages') }}</x-slot>
    <h1 class="font-bold text-2xl mb-4">{{ __('tollerus::ui.languages') }}</h1>
    <div class="flex flex-col gap-4 items-stretch">
        @foreach ($languages as $language)
            <x-tollerus::panel class="flex flex-col gap-2">
                <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                    <x-tollerus::icons.language class="h-8"/>
                    <span>{{ $language->name }}</span>
                </h2>
                <div class="flex flex-row justify-start">
                    @if ($primaryGlyphs[$language->machine_name] !== null)
                        @if ($primaryGlyphs[$language->machine_name]['allSvgFound'])
                            <div class="p-4 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900 flex flex-row" role="img" aria-label="{{ __('tollerus::ui.primary_neography', ['name' => $language->primaryNeography->name]) }}">
                                @foreach ($primaryGlyphs[$language->machine_name]['svg'] as $svg)
                                    {{-- Controller generates these with classes: 'h-12 w-auto' --}}
                                    {!! $svg !!}
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900" role="img" aria-label="{{ __('tollerus::ui.primary_neography', ['name' => $language->primaryNeography->name]) }}">
                                <p class="text-5xl" style="font-family:{{ $language->primaryNeography->machine_name }};">{{ $primaryGlyphs[$language->machine_name]['models']->pluck('glyph')->implode('') }}</p>
                            </div>
                        @endif
                    @else
                        <div class="p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500" role="img" aria-label="{{ __('tollerus::ui.primary_neography', ['name' => __('tollerus::ui.none')]) }}">
                            <p class="text-5xl text-zinc-300 dark:text-zinc-500">+</p>
                        </div>
                    @endif
                </div>
            </x-tollerus::panel>
        @endforeach
    </div>
</x-tollerus::layout>
