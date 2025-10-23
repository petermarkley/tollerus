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
                <p>Lorem ipsum dolor sit amet.</p>
            </x-tollerus::panel>
        @endforeach
    </div>
</x-tollerus::layout>
