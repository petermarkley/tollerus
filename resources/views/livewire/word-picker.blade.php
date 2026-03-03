<div
    class="relative w-full flex flex-col items-start"
    x-data="{ open: false }"
    @focusout="if (open && $event.relatedTarget !== null && !($el.contains($event.relatedTarget) || $event.relatedTarget.contains($el))) {open=false;}"
    @click.window="if (open && !$el.contains($event.target)) {open=false;}"
    @keydown.escape="open=false"
>
    <div class="p-2 flex flex-row gap-2 items-center border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm">
        @if ($selectedWord === null)
            <p class="italic text-zinc-500 dark:text-zinc-500">{{ __('tollerus::ui.none') }}</p>
            <x-tollerus::inputs.button
                type="inverse"
                size="small"
                title="{{ __('tollerus::ui.edit') }}"
                class="relative"
                @click="open=true"
            >
                <x-tollerus::icons.edit />
                <span class="sr-only">{{ __('tollerus::ui.edit') }}</span>
            </x-tollerus::inputs.button>
        @else
            <p class="flex flex-row gap-2 items-center">
                <span>{{ $selectedWordTransliterated }}</span>
                <span class="tollerus_custom_{{ $selectedWordNativeNeography->machine_name }}">{{ $selectedWordNative }}</span>
            </p>
            <x-tollerus::inputs.button
                type="inverse"
                size="small"
                title="{{ __('tollerus::ui.remove') }}"
                class="relative"
                wire:click="deselectWord"
            >
                <x-tollerus::icons.x />
                <span class="sr-only">{{ __('tollerus::ui.remove') }}</span>
            </x-tollerus::inputs.button>
        @endif
    </div>
    <div x-show="open" class="max-w-60 lg:max-w-120 w-[100vw] min-h-30 h-[70vh] absolute left-0 top-11 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow p-2 flex flex-col gap-2 items-start">
        Lorem ipsum dolor sit amet
    </div>
</div>
