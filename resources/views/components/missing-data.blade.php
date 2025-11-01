<div {{ $attributes->merge(['class' => 'p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500']) }}>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 italic text-center max-w-40">{{ $slot }}</p>
</div>
