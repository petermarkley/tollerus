<x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
    @if (count($paginator->items()) > 0)
        <x-tollerus::pane withPadding="false" class="flex flex-col">
            <div class="p-2 flex flex-col md:flex-row gap-2 justify-center items-center">
                <form
                    wire:submit="search"
                    class="w-full h-14 bg-white dark:bg-zinc-800 border-2 border-zinc-200 dark:border-zinc-700 rounded-full p-1 flex flex-row gap-1 items-stretch shadow-md"
                >
                    <div class="relative flex justify-center items-center">
                        <label for="search_type" class="sr-only">{{ __('tollerus::ui.search_type') }}</label>
                        <select
                            id="search_type"
                            wire:model="searchType"
                            class="bg-white dark:bg-zinc-800 hover:bg-zinc-100 hover:dark:bg-zinc-700 cursor-pointer py-2 px-4 h-11 flex justify-center items-center appearance-none rounded-l-[22px] rounded-r-lg pr-6 font-bold border-2 border-zinc-500 dark:border-zinc-400"
                        >
                            @foreach (\PeterMarkley\Tollerus\Enums\SearchType::cases() as $thisSearchType)
                                <option value="{{ $thisSearchType->value }}">{{ mb_ucfirst($thisSearchType->localize()) }}</option>
                            @endforeach
                        </select>
                        <x-tollerus::icons.triangle class="absolute pointer-events-none right-2 top-1/2 scale-[80%] rotate-90 -translate-y-1/2" />
                    </div>
                    <div class="flex-grow flex justify-center items-center">
                        <x-tollerus::inputs.text
                            id="search_string"
                            model="searchStr"
                            modelIsAlpine="false"
                            class="appearance-none w-full border-x-none border-y-none"
                        >
                            <label for="search_string" class="sr-only">{{ __('tollerus::ui.search_term') }}</label>
                        </x-tollerus::inputs.text>
                    </div>
                    <div class="shrink-0 flex justify-center items-center">
                        <x-tollerus::inputs.button
                            type="secondary"
                            size="small"
                            htmlType="submit"
                            title="{{ __('tollerus::ui.submit_search') }}"
                            class="w-10 h-10 my-1 mr-1 rounded-l-full rounded-r-full flex justify-center items-center"
                        >
                            <x-tollerus::icons.magnifying-glass class="w-7 h-7"/>
                            <span class="sr-only">{{ __('tollerus::ui.submit_search') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                </form>
                <div class="flex flex-row gap-2 justify-center items-center shrink-0 whitespace-nowrap">
                    <x-tollerus::inputs.button
                        type="secondary"
                        title="{{ __('tollerus::ui.sort_by_transliterated', ['transliterated' => config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))]) }}"
                        x-bind:disabled="{{ ($sortBy=='transliterated' ? 'true':'false') }}"
                        class="flex flex-row gap-2 items-center"
                        wire:click="setSortBy('transliterated')"
                        wire:loading.attr="disabled"
                    >
                        <x-tollerus::icons.bars-arrow-down/>
                        <span>{{ __('tollerus::ui.sort_by_transliterated', ['transliterated' => config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))]) }}</span>
                    </x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        type="secondary"
                        title="{{ __('tollerus::ui.sort_by_native') }}"
                        x-bind:disabled="{{ ($sortBy=='native' ? 'true':'false') }}"
                        class="flex flex-row gap-2 items-center"
                        wire:click="setSortBy('native')"
                        wire:loading.attr="disabled"
                    >
                        <x-tollerus::icons.bars-arrow-down/>
                        <span>{{ __('tollerus::ui.sort_by_native') }}</span>
                    </x-tollerus::inputs.button>
                </div>
            </div>
            <div class="p-4 h-auto md:h-200 lg:h-104 flex flex-col justify-start items-start flex-nowrap md:flex-wrap gap-2 border-y-2 border-zinc-200 dark:border-zinc-700">
                @foreach ($paginator->items() as $entry)
                    <x-tollerus::button
                        type="inverse"
                        href="{{ route('tollerus.admin.languages.entries.edit', ['language' => $language, 'entry' => $entry]) }}"
                    >
                        @if ($entry['transliterated'])
                            <div class="flex flex-row gap-4 justify-start items-center">
                                <span>{{ $entry['transliterated'] }}</span>
                                @if ($language->primaryNeography)
                                    <span class="tollerus_{{ $language->primaryNeography->machine_name }}">{{ $entry['native'] }}</span>
                                @else
                                    <span>{{ $entry['native'] }}</span>
                                @endif
                            </div>
                        @else
                            <span class="italic font-normal">{{ __('tollerus::ui.entry_nameless') }}</span>
                        @endif
                    </x-tollerus::button>
                @endforeach
            </div>
            <div class="p-4">
                {{ $paginator->links('tollerus::components.pagination-links', data: ['scrollTo' => false]) }}
            </div>
        </x-tollerus::pane>
    @endif
    <div class="flex flex-col gap-6 items-center w-full">
        <x-tollerus::inputs.missing-data
            title="{{ __('tollerus::ui.add_entry') }}"
            class="relative flex flex-row gap-2 justify-center items-center w-full"
            @click="$store.entries.create();"
            wire:loading.attr="disabled"
            wire:target="createEntry"
        >
            <x-tollerus::icons.plus/>
            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_entry') }}</span>
        </x-tollerus::inputs.missing-data>
    </div>
</x-tollerus::panel>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('entries', {
        create() {
            fetch('{{ route('tollerus.admin.languages.entries.store', ['language' => $language->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(response => response.json())
            .then(data => {
                if (data.id) {
                    window.location.href = '{{ route('tollerus.admin.languages.entries.edit', ['language' => $language->id, 'entry' => '#']) }}'.replaceAll('#', data.id);
                }
            }).catch(error => console.error('Network error:', error));
        },
    });
});
</script>
@endpush
@endonce
