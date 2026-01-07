<x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
    @if (count($paginator->items()) > 0)
        <x-tollerus::pane withPadding="false" class="flex flex-col">
            <div class="p-4 flex flex-row gap-2 justify-center items-center">
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
