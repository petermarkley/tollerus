@props([
    'currentPage',
    'langCount',
])
@php
    $opts = [
        'word_lookup' => [
            'href' => route('tollerus.public.index'),
            'text' => __('tollerus::ui.word_lookup'),
            'icon' => 'entries',
        ],
        'language_info' => [
            'href' => route('tollerus.public.languages.index'),
            'text' => trans_choice('tollerus::ui.language_info', $langCount),
            'icon' => 'info',
        ],
    ];
@endphp
<div class="w-full flex flex-row gap-4 items-center justify-center">
    @foreach ($opts as $key => $opt)
        @if ($currentPage == $key)
            <div title="{{ $opt['text'] }}" class="flex flex-row gap-2 items-center bg-tollerus-surface cursor-default rounded-lg px-6 py-3 text-tollerus-text font-bold opacity-50">
                <x-dynamic-component :component="'tollerus::icons.' . $opt['icon']" />
                <span class="sr-only md:not-sr-only">{{ $opt['text'] }}</span>
            </div>
        @else
            <a href="{{ $opt['href'] }}" title="{{ $opt['text'] }}" class="flex flex-row gap-2 items-center bg-tollerus-surface hover:bg-tollerus-surface-hover cursor-pointer rounded-lg shadow-lg px-6 py-3 text-tollerus-text font-bold">
                <x-dynamic-component :component="'tollerus::icons.' . $opt['icon']" />
                <span class="sr-only md:not-sr-only">{{ $opt['text'] }}</span>
            </a>
        @endif
    @endforeach
</div>
