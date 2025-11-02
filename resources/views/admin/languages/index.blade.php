<x-tollerus::layout>
    <x-slot name="title">{{ __('tollerus::ui.languages') }}</x-slot>
    <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">{{ __('tollerus::ui.languages') }}</h1>
    <div class="flex flex-col gap-4 items-stretch">
        @foreach ($languages as $language)
            <x-tollerus::panel class="flex flex-col gap-2">
                <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                    <x-tollerus::icons.language class="h-8"/>
                    <span>{{ $language->name }}</span>
                </h2>
                <div class="flex flex-row justify-start gap-4">

                    {{-- Neography preview --}}
                    @if ($primaryGlyphs[$language->machine_name] !== null)
                        @if ($primaryGlyphs[$language->machine_name]['allSvgFound'])
                            <x-tollerus::pane class="flex flex-row" role="img" aria-label="{{ __('tollerus::ui.primary_neography_name', ['name' => $language->primaryNeography->name]) }}">
                                @foreach ($primaryGlyphs[$language->machine_name]['svg'] as $svg)
                                    {{-- Controller generates these with classes: 'h-12 w-auto' --}}
                                    {!! $svg !!}
                                @endforeach
                            </x-tollerus::pane>
                        @else
                            <x-tollerus::pane role="img" aria-label="{{ __('tollerus::ui.primary_neography_name', ['name' => $language->primaryNeography->name]) }}">
                                <p class="text-5xl" style="font-family:{{ $language->primaryNeography->machine_name }};">{{ $primaryGlyphs[$language->machine_name]['models']->pluck('glyph')->implode('') }}</p>
                            </x-tollerus::pane>
                        @endif
                    @else
                        <x-tollerus::missing-data>{{ __('tollerus::ui.no_neographies') }}</x-tollerus::missing-data>
                    @endif

                    {{-- Grammar preview --}}
                    @if (count($wordClassGroups[$language->machine_name]) > 0)
                        <x-tollerus::pane>
                            <ul class="flex flex-row gap-2 flex-wrap justify-start items-start" role="img" aria-label="{{ __('tollerus::ui.grammar') }}">
                                @foreach ($wordClassGroups[$language->machine_name] as $wordClassGroup)
                                    @if ($wordClassGroup['class'] !== null)
                                        @if ($wordClassGroup['featureCount'] == 0)
                                            <li class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1">
                                                <span><abbr class="no-underline" title="{{ $wordClassGroup['class']->name }}">{{ $wordClassGroup['nameBrief'] }}</abbr></span>
                                            </li>
                                        @else
                                            <li class="border-cyan-400 text-cyan-700 dark:border-cyan-700 dark:text-cyan-300 bg-cyan-100 dark:bg-cyan-950 rounded-lg shadow-sm flex flex-row gap-1 items-center p-1 border-2 font-bold">
                                                <span><abbr class="no-underline" title="{{ $wordClassGroup['class']->name }}">{{ $wordClassGroup['nameBrief'] }}</abbr></span>
                                                <span class="block text-white dark:text-cyan-950 bg-cyan-700 dark:bg-cyan-300 rounded-full w-6 h-6 flex justify-center items-center text-center text-sm">{{ $wordClassGroup['featureCount'] }}</span>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </x-tollerus::pane>
                    @else
                        <x-tollerus::missing-data>{{ __('tollerus::ui.no_grammar') }}</x-tollerus::missing-data>
                    @endif

                    {{-- Entries preview --}}
                    @if (count($entriesPreview[$language->machine_name]) > 0)
                        <x-tollerus::pane class="w-full max-h-28 overflow-hidden">
                            <ul class="flex flex-col gap-x-4 flex-wrap justify-start items-start w-full h-32 mask-b-to-85% mask-r-from-60%" role="img" aria-label="{{ __('tollerus::ui.entries') }}">
                                @foreach ($entriesPreview[$language->machine_name] as $entry)
                                    <li class="font-bold">{{ $entry->transliterated }}</li>
                                @endforeach
                            </ul>
                        </x-tollerus::pane>
                    @else
                        <x-tollerus::missing-data>{{ __('tollerus::ui.no_entries') }}</x-tollerus::missing-data>
                    @endif

                </div>
            </x-tollerus::panel>
        @endforeach
    </div>
</x-tollerus::layout>
