<x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
    <p>Lorem ipsum dolor sit amet.</p>
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
            @foreach ($paginator->items() as $form)
                @php
                    $nativeSpelling = $form->primaryNativeSpelling();
                @endphp
                <x-tollerus::button
                    type="inverse"
                    href="#"
                >
                    <div class="flex flex-row gap-4 justify-start items-center">
                        <span>{{ $form->transliterated }}</span>
                        <span class="tollerus_{{ $language->primaryNeography->machine_name }}">{{ $nativeSpelling->spelling }}</span>
                    </div>
                </x-tollerus::button>
            @endforeach
        </div>
        <div class="p-4">
            {{ $paginator->links('tollerus::components.pagination-links', data: ['scrollTo' => false]) }}
        </div>
    </x-tollerus::pane>
</x-tollerus::panel>
