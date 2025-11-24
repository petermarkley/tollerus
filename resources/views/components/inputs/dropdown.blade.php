<div
    class="relative w-full"
    x-data="{ open: false }"
    @focusout="if (open && $event.relatedTarget !== null && !($el.contains($event.relatedTarget) || $event.relatedTarget.contains($el))) {open=false;}"
    @click.window="if (open && !$el.contains($event.target)) {open=false;}"
    @keydown.escape="open=false"
>
    {{ $button }}
    <div x-show="open" class="max-w-40 lg:max-w-80 w-full absolute left-0 top-11 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow p-2 flex flex-col gap-2 items-start">
        {{ $slot }}
    </div>
</div>
