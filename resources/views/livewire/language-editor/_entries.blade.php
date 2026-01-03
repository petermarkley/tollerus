<x-tollerus::panel id="tabpanel-entries" role="tabpanel" x-cloak x-show="tab=='entries'" class="flex flex-col gap-6">
    <p>Lorem ipsum dolor sit amet.</p>
    <x-tollerus::pane class="flex flex-col gap-4">
        <div>&hellip;</div>
        <div class="h-120 flex flex-col justify-start items-start flex-wrap gap-2">
            @foreach ($entriesPaginator->items() as $form)
                <x-tollerus::button
                    type="inverse"
                    href="#"
                >
                    <div class="flex flex-col gap-1 items-start">
                        <span>{{ $form->transliterated }}</span>
                    </div>
                </x-tollerus::button>
            @endforeach
        </div>
        {{ $entriesPaginator->links('tollerus::components.pagination-links', data: ['scrollTo' => false]) }}
    </x-tollerus::pane>
</x-tollerus::panel>
