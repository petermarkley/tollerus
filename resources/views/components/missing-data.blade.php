@props([
    'floating' => false,
])
@php
    $style = 'flex justify-center items-center p-4 rounded-lg inset-shadow-sm border-dashed border-2 cursor-pointer ';
    $textStyle = 'text-sm italic text-center max-w-40 ';
    if (filter_var($floating, FILTER_VALIDATE_BOOLEAN)) {
        $style .= 'border-zinc-500 dark:border-zinc-500 hover:bg-zinc-300 hover:dark:bg-zinc-700';
        $textStyle .= 'text-zinc-700 dark:text-zinc-400 hover:text-zinc-900 hover:dark:text-zinc-400';
    } else {
        $style .= 'border-zinc-400 dark:border-zinc-500 hover:bg-zinc-100 hover:dark:bg-zinc-700';
        $textStyle .= 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-600 hover:dark:text-zinc-400';
    }
    $style .= ' disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:dark:bg-transparent';
@endphp
<a {{ $attributes->merge(['class' => $style]) }}>
    <p class="{{ $textStyle }}">{{ $slot }}</p>
</a>
