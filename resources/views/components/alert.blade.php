@props([
    'type' => 'info',
])
<div data-component="alert" @class([
    'p-4 rounded-lg flex flex-row justify-start items-center gap-4 shadow-sm border-1 border-dashed',
    'bg-cyan-100/50 dark:bg-cyan-950/20 saturate-50 text-cyan-900 dark:text-cyan-100 border-cyan-500/50 dark:border-cyan-500/50' => ($type == 'info'),
    'bg-yellow-100/50 dark:bg-yellow-800/20 dark:saturate-50 text-yellow-950 dark:text-yellow-200 border-yellow-600/50 dark:border-yellow-300/50' => ($type == 'warning'),
    'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400 border-red-500 dark:border-red-500/50' => ($type == 'error'),
]) {{ $attributes }}>
    @switch ($type)
        @case('info')
            {{-- This icon courtesy of https://heroicons.com/ --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        @break
        @case('warning')
        @case('error')
            {{-- This icon courtesy of https://heroicons.com/ --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        @break
    @endswitch
    <div>
        {{ $slot }}
    </div>
</div>
