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
        <a
            href="{{ $opt['href'] }}"
            title="{{ $opt['text'] }}"
            @class([
                'flex flex-row gap-2 items-center bg-tollerus-surface rounded-lg px-6 py-3 text-tollerus-text font-bold cursor-pointer',
                'opacity-50' => $currentPage == $key,
                'hover:bg-tollerus-surface-hover shadow-lg' => $currentPage != $key,
            ])
        >
            <x-dynamic-component :component="'tollerus::icons.' . $opt['icon']" />
            <span class="sr-only md:not-sr-only">{{ $opt['text'] }}</span>
        </a>
    @endforeach
</div>
