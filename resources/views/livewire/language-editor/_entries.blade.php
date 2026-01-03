<x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
    <p>Lorem ipsum dolor sit amet.</p>
    <x-tollerus::pane class="flex flex-col gap-4">
        <div>&hellip;</div>
        <div class="h-auto md:h-240 lg:h-120 flex flex-col justify-start items-start flex-nowrap md:flex-wrap gap-2">
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
        {{ $paginator->links('tollerus::components.pagination-links', data: ['scrollTo' => false]) }}
    </x-tollerus::pane>
</x-tollerus::panel>
