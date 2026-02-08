@props([
    'type' => 'primary',
    'size' => 'medium',
])
@switch($type)
    @case('primary')
        @switch($size)
            @case('medium')
                <a {{ $attributes->merge(['class' => 'relative bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 saturate-50 font-bold cursor-pointer rounded-lg py-2 px-4 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600 disabled:saturate-100']) }}>{{ $slot }}</a>
            @break;
            @case('small')
                <a {{ $attributes->merge(['class' => 'relative bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 saturate-50 font-bold cursor-pointer rounded-lg p-1 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600 disabled:saturate-100']) }}>{{ $slot }}</a>
            @break;
        @endswitch
    @break;
    @case('secondary')
        @switch($size)
            @case('medium')
                <a {{ $attributes->merge(['class' => 'relative bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 font-bold cursor-pointer rounded-lg py-2 px-4 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600']) }}>{{ $slot }}</a>
            @break;
            @case('small')
                <a {{ $attributes->merge(['class' => 'relative bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 font-bold cursor-pointer rounded-lg p-1 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600']) }}>{{ $slot }}</a>
            @break;
        @endswitch
    @break;
    @case('inverse')
        <a {{ $attributes->merge(['class' => 'relative text-zinc-600 dark:text-zinc-400 hover:text-zinc-500 hover:dark:text-zinc-300 font-bold cursor-pointer disabled:cursor-not-allowed disabled:font-normal disabled:text-zinc-300 disabled:dark:text-zinc-600']) }}>{{ $slot }}</a>
    @break;
@endswitch
