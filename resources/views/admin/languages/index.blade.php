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
                <div class="flex flex-row justify-start gap-4">

                    {{-- Neography preview --}}
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
                        <div class="p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40">{{ __('tollerus::ui.no_neographies') }}</p>
                        </div>
                    @endif

                    {{-- Grammar preview --}}
                    @if (count($wordClassGroups[$language->machine_name]) > 0)
                        <ul class="p-4 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900 flex flex-row gap-2 flex-wrap justify-start items-start" role="img" aria-label="{{ __('tollerus::ui.grammar') }}">
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
                    @else
                        <div class="p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40">{{ __('tollerus::ui.no_grammar') }}</p>
                        </div>
                    @endif

                </div>
            </x-tollerus::panel>
        @endforeach
    </div>
</x-tollerus::layout>
