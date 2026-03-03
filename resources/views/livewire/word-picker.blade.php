<div
    class="relative w-full flex flex-row gap-2 items-center"
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
                title="{{ __('tollerus::ui.choose') }}"
                class="relative"
                @click="$wire.refreshForm(); open=true;"
            >
                <x-tollerus::icons.edit />
                <span class="sr-only">{{ __('tollerus::ui.choose') }}</span>
            </x-tollerus::inputs.button>
        @else
            <p class="flex flex-row gap-2 items-center">
                @switch($selectedWordKind)
                    @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Glyph)
                        <x-tollerus::icons.micro.neography class="shrink-0" />
                    @break
                    @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry)
                        <x-tollerus::icons.micro.entries class="shrink-0" />
                    @break
                    @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Form)
                        <x-tollerus::icons.micro.fingerprint class="shrink-0" />
                    @break
                @endswitch
                <span class="font-bold whitespace-nowrap shrink-0">{{ $selectedWordTransliterated }}</span>
                <span class="whitespace-nowrap shrink-1 tollerus_custom_{{ $selectedWordNativeNeography->machine_name }}">{{ $selectedWordNative }}</span>
                <span class="font-mono shrink-0">{{ $selectedWordId }}</span>
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
    @if ($selectedWord !== null)
        <x-tollerus::button
            type="secondary"
            size="small"
            title="{{ __('tollerus::ui.edit_word') }}"
            href="{{ $selectedWordEditUrl }}"
        >
            <x-tollerus::icons.edit class="m-2" />
            <span class="sr-only">{{ __('tollerus::ui.edit_word') }}</span>
        </x-tollerus::button>
    @endif
    <div x-show="open" class="max-w-60 lg:max-w-100 w-[100vw] min-h-30 max-h-[70vh] absolute left-0 top-11 z-10 border-2 border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow p-2 flex flex-col gap-2 items-start">
        <form
            wire:submit="search"
            class="w-full flex flex-row gap-1 items-center"
        >
            <div class="flex-grow flex justify-center items-center">
                <x-tollerus::inputs.text
                    id="search_key"
                    model="searchKey"
                    modelIsAlpine="false"
                    placeholder="{{ __('tollerus::ui.search_for_word') }}"
                >
                    <label for="search_key" class="sr-only">{{ __('tollerus::ui.search_term') }}</label>
                </x-tollerus::inputs.text>
            </div>
            <div class="shrink-0 flex justify-center items-center">
                <x-tollerus::inputs.button
                    type="secondary"
                    size="small"
                    htmlType="submit"
                    title="{{ __('tollerus::ui.submit_search') }}"
                    class="rounded-l-full rounded-r-full flex justify-center items-center"
                >
                    <x-tollerus::icons.magnifying-glass/>
                    <span class="sr-only">{{ __('tollerus::ui.submit_search') }}</span>
                </x-tollerus::inputs.button>
            </div>
        </form>
        <div class="w-full flex-grow overflow-y-scroll overflow-x-hidden flex flex-col gap-2 items-stretch">
            @foreach ($results as $result)
                <button
                    @class([
                        'py-1 pr-4 flex flex-row gap-2 justify-start items-center font-bold cursor-pointer bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700',
                        'pl-4' => $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Glyph || $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry,
                        'pl-12' => $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Form,
                    ])
                    @click="open=false; $wire.selectWord('{{ $result['globalId'] }}');"
                >
                    @switch($result['kind'])
                        @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Glyph)
                            <x-tollerus::icons.micro.neography class="shrink-0" />
                        @break
                        @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry)
                            <x-tollerus::icons.micro.entries class="shrink-0" />
                        @break
                        @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Form)
                            <x-tollerus::icons.micro.fingerprint class="shrink-0" />
                        @break
                    @endswitch
                    <span class="font-bold whitespace-nowrap shrink-0">{{ $result['transliterated'] }}</span>
                    <span class="whitespace-nowrap shrink-1 tollerus_custom_{{ $result['neographyMachineName'] }}">{{ $result['native'] }}</span>
                    <span class="font-mono font-normal shrink-0">{{ $result['globalId'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
