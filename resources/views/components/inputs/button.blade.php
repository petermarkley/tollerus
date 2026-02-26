@props([
    'type' => 'primary',
    'size' => 'medium',
    'htmlType' => 'button',
])
@switch($type)
    @case('primary')
        @switch($size)
            @case('medium')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 dark:saturate-50 font-bold cursor-pointer rounded-lg py-2 px-4 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600 disabled:saturate-100']) }}>{{ $slot }}</button>
            @break;
            @case('small')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 dark:saturate-50 font-bold cursor-pointer rounded-lg p-1 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600 disabled:saturate-100']) }}>{{ $slot }}</button>
            @break;
            @case('tiny')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-cyan-800 dark:bg-cyan-500 hover:bg-cyan-700 hover:dark:bg-cyan-400 text-white dark:text-zinc-950 dark:saturate-50 font-bold cursor-pointer rounded shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600 disabled:saturate-100']) }}>{{ $slot }}</button>
            @break;
        @endswitch
    @break;
    @case('secondary')
        @switch($size)
            @case('medium')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 font-bold cursor-pointer rounded-lg py-2 px-4 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600']) }}>{{ $slot }}</button>
            @break;
            @case('small')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 font-bold cursor-pointer rounded-lg p-1 shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600']) }}>{{ $slot }}</button>
            @break;
            @case('tiny')
                <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 font-bold cursor-pointer rounded shadow disabled:cursor-not-allowed disabled:font-normal disabled:bg-zinc-300 disabled:dark:bg-zinc-600']) }}>{{ $slot }}</button>
            @break;
        @endswitch
    @break;
    @case('inverse')
        <button type="{{ $htmlType }}" {{ $attributes->merge(['class' => 'relative text-zinc-600 dark:text-zinc-400 hover:text-zinc-500 hover:dark:text-zinc-300 font-bold cursor-pointer disabled:cursor-not-allowed disabled:font-normal disabled:text-zinc-300 disabled:dark:text-zinc-600']) }}>{{ $slot }}</button>
    @break;
@endswitch
