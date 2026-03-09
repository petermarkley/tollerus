<div
    class="relative w-full flex flex-row gap-2 items-center"
    x-data="{ open: false }"
    @focusout="if (open && $event.relatedTarget !== null && !($el.contains($event.relatedTarget) || $event.relatedTarget.contains($el))) {open=false;}"
    @click.window="if (open && !$el.contains($event.target)) {open=false;}"
    @keydown.escape="open=false"
    @word-picker-select-id-external.window="$wire.selectWord($event.detail.wordId, false);"
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
                        <x-tollerus::icons.micro.neography class="shrink-0" title="{{ __('tollerus::ui.glyph') }}" />
                    @break
                    @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry)
                        <x-tollerus::icons.micro.entries class="shrink-0" title="{{ __('tollerus::ui.entry') }}" />
                    @break
                    @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Form)
                        <x-tollerus::icons.micro.fingerprint class="shrink-0" title="{{ __('tollerus::ui.form') }}" />
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
            <x-tollerus::icons.external-link class="m-1" />
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
        @if (!$langIsStrict)
            <x-tollerus::inputs.select
                idExpression="'language'"
                label="{{ __('tollerus::ui.language') }}"
                model="languageId"
                modelIsAlpine="false"
                wire:change="search"
            >
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}">{{ $language->name }}</option>
                @endforeach
            </x-tollerus::inputs.select>
        @endif
        @if ($showParticleToggle && count($particleClassIds) > 0)
            <x-tollerus::inputs.checkbox
                id="limit_to_particles"
                model="softLimitToParticles"
                label="{{ __('tollerus::ui.limit_to_particles') }}"
                wire:change="search"
            />
        @endif
        <div class="w-full flex-grow overflow-y-scroll overflow-x-hidden flex flex-col gap-2 items-stretch">
            @if (!empty($searchKey) && count($results) == 0 && count($globalIdResults) == 0)
                <span class="italic text-zinc-700 dark:text-zinc-400">{{ __('tollerus::ui.no_results') }}</span>
            @endif
            @if (count($globalIdResults) > 0)
                <div class="mx-1 mt-4 p-2 pt-4 relative flex flex-col gap-2 items-stretch rounded-lg border border-zinc-100 dark:border-zinc-600">
                    <span class="absolute left-4 -top-3 text-sm italic px-1 bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-500">{{ __('tollerus::ui.results_for_global_id') }}</span>
                    @php($theseResults = $globalIdResults)
                    @include('tollerus::livewire.word-picker._search-results')
                </div>
            @endif
            @php($theseResults = $results)
            @include('tollerus::livewire.word-picker._search-results')
        </div>
    </div>
</div>
