@props([
    'type' => 'info',
])
<div data-component="alert" @class([
    'p-4 rounded-lg flex flex-row justify-start items-center gap-4 shadow-sm border-1 border-dashed',
    'bg-cyan-100/50 dark:bg-cyan-950/20 saturate-50 text-cyan-900 dark:text-cyan-100 border-cyan-500/50 dark:border-cyan-500/50' => ($type == 'info'),
    'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400 border-red-500 dark:border-red-500/50' => ($type == 'error'),
]) {{ $attributes }}>
    {{-- This icon courtesy of https://heroicons.com/ --}}
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
    </svg>
    <div>
        {{ $slot }}
    </div>
</div>
