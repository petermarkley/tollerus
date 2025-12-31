@props([
    'size' => 'medium',
    'floating' => false,
])
@php
    $style = 'rounded-lg inset-shadow-sm border-dashed border-2 text-sm italic text-center max-w-40 lg:max-w-80 cursor-pointer ';
    if (filter_var($floating, FILTER_VALIDATE_BOOLEAN)) {
        $style .= 'border-zinc-500 dark:border-zinc-500 text-zinc-700 dark:text-zinc-400 hover:bg-zinc-300 hover:dark:bg-zinc-700 hover:text-zinc-900 hover:dark:text-zinc-400';
    } else {
        $style .= 'border-zinc-400 dark:border-zinc-500 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 hover:dark:bg-zinc-700 hover:text-zinc-600 hover:dark:text-zinc-400';
    }
    $style .= ' disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:dark:bg-transparent';
@endphp
@switch($size)
    @case('medium')
        <button {{ $attributes->merge(['class' => 'p-4 ' . $style]) }}>
            {{ $slot }}
        </button>
    @break
    @case('small')
        <button {{ $attributes->merge(['class' => 'px-4 py-2 ' . $style]) }}>
            {{ $slot }}
        </button>
    @break
@endswitch
