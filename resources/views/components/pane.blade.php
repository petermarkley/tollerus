@props([
    'withPadding' => true,
    'href' => '',
])
@php
    $pStyle = '';
    if (filter_var($withPadding, FILTER_VALIDATE_BOOLEAN)) {
        $pStyle = 'p-4 ';
    }
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $pStyle.'block relative group text-zinc-900 dark:text-zinc-300 rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 cursor-pointer hover:opacity-70']) }}>
        {{ $slot }}
        <div class="absolute inset-0 w-full h-full rounded-lg border border-cyan-400 dark:border-cyan-700 z-10 pointer-events-none bg-cyan-200/10 dark:bg-cyan-700/10 opacity-0 group-hover:opacity-100"></div>
    </a>
@else
    <div {{ $attributes->merge(['class' => $pStyle.'rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30']) }}>
        {{ $slot }}
    </div>
@endif
