<div
    class="relative w-full"
    x-data="{ open: false }"
    @focusout="if (open && $event.relatedTarget !== null && !($el.contains($event.relatedTarget) || $event.relatedTarget.contains($el))) {open=false;}"
    @click.window="if (open && !$el.contains($event.target)) {open=false;}"
    @keydown.escape="open=false"
>
    <div class="flex flex-row gap-2 items-center">
        <span>Lorem ipsum</span>
        <x-tollerus::inputs.button
            type="inverse"
            size="small"
            class="relative"
            @click="open=true"
        >
            <x-tollerus::icons.edit />
            <span class="sr-only">{{ __('tollerus::ui.edit') }}</span>
        </x-tollerus::inputs.button>
    </div>
    <div x-show="open" class="max-w-60 lg:max-w-120 w-[100vw] min-h-30 h-[70vh] absolute left-0 top-11 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow p-2 flex flex-col gap-2 items-start">
        Lorem ipsum dolor sit amet
    </div>
</div>
