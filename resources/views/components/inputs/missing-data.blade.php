@props([
    'size' => 'normal',
])
@switch($size)
    @case('normal')
        <button {{ $attributes->merge(['class' => 'p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500 text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40 lg:max-w-80 cursor-pointer hover:bg-zinc-100 hover:dark:bg-zinc-700 hover:text-zinc-500 hover:dark:text-zinc-400']) }}>
            {{ $slot }}
        </button>
    @break
    @case('small')
        <button {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500 text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40 lg:max-w-80 cursor-pointer hover:bg-zinc-100 hover:dark:bg-zinc-700 hover:text-zinc-500 hover:dark:text-zinc-400']) }}>
            {{ $slot }}
        </button>
    @break
@endswitch
