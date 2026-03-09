<div
    class="relative w-full"
    x-data="{ open: false }"
    @focusout="if (open && $event.relatedTarget !== null && !($el.contains($event.relatedTarget) || $event.relatedTarget.contains($el))) {open=false; $dispatch('drawer-override-disable');}"
    @click.window="if (open && !$el.contains($event.target)) {open=false; $dispatch('drawer-override-disable');}"
    @keydown.escape="open=false; $dispatch('drawer-override-disable');"
>
    {{ $button }}
    <div x-show="open" x-cloak class="max-w-40 lg:max-w-80 w-[100vw] absolute left-0 top-11 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow p-2 flex flex-col gap-2 items-start">
        {{ $slot }}
    </div>
</div>
