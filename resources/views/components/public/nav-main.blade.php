@props([
    'currentPage',
    'langCount',
])
@php
    $opts = [
        'word_lookup' => ['href' => route('tollerus.public.index'), 'text' => __('tollerus::ui.word_lookup')],
        'language_info' => ['href' => route('tollerus.public.languages.index'), 'text' => trans_choice('tollerus::ui.language_info', $langCount)],
    ];
@endphp
<div class="flex flex-row gap-4 items-center justify-center">
    @foreach ($opts as $key => $opt)
        @if ($currentPage == $key)
            <div class="inline-block bg-tollerus-surface cursor-default rounded-lg p-4 text-tollerus-text font-bold opacity-50">{{ $opt['text'] }}</div>
        @else
            <a href="{{ $opt['href'] }}" class="inline-block bg-tollerus-surface hover:bg-tollerus-surface-hover cursor-pointer rounded-lg shadow-lg p-4 text-tollerus-text font-bold">{{ $opt['text'] }}</a>
        @endif
    @endforeach
</div>
