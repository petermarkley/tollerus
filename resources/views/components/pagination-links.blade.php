<div class="w-full flex flex-row gap-4 justify-center items-center">
    @if ($paginator->hasPages())
        <div class="hidden md:block">
            <p class="italic text-zinc-500 dark:text-zinc-500">{{ __('tollerus::ui.pagination_showing_numbers', [
                'start' => ($paginator->currentPage()-1)*$paginator->perPage() + 1,
                'end' => ($paginator->currentPage() == $paginator->lastPage() ? $paginator->total() : $paginator->currentPage()*$paginator->perPage()),
                'total' => $paginator->total(),
            ]) }}</p>
        </div>
        <nav role="navigation" aria-label="{{ __('tollerus::ui.pagination_navigation') }}" class="flex flex-row gap-2 justify-center items-center">
            @if ($paginator->onFirstPage())
                <x-tollerus::inputs.button type="inverse" title="{{ __('tollerus::ui.first') }}" disabled>
                    <x-tollerus::icons.chevron-double-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.first') }}</span>
                </x-tollerus::inputs.button>
                <x-tollerus::inputs.button type="inverse" title="{{ __('tollerus::ui.previous') }}" disabled>
                    <x-tollerus::icons.chevron-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.previous') }}</span>
                </x-tollerus::inputs.button>
            @else
                <x-tollerus::inputs.button
                    type="inverse"
                    wire:click="setPage(1)"
                    wire:loading.attr="disabled"
                    rel="prev"
                    title="{{ __('tollerus::ui.first') }}"
                >
                    <x-tollerus::icons.chevron-double-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.first') }}</span>
                </x-tollerus::inputs.button>
                <x-tollerus::inputs.button
                    type="inverse"
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    rel="prev"
                    title="{{ __('tollerus::ui.previous') }}"
                >
                    <x-tollerus::icons.chevron-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.previous') }}</span>
                </x-tollerus::inputs.button>
            @endif

            @if ($paginator->onLastPage())
                <x-tollerus::inputs.button type="inverse" title="{{ __('tollerus::ui.next') }}" disabled>
                    <x-tollerus::icons.chevron-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.next') }}</span>
                </x-tollerus::inputs.button>
                <x-tollerus::inputs.button type="inverse" title="{{ __('tollerus::ui.last') }}" disabled>
                    <x-tollerus::icons.chevron-double-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.last') }}</span>
                </x-tollerus::inputs.button>
            @else
                <x-tollerus::inputs.button
                    type="inverse"
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    rel="prev"
                    title="{{ __('tollerus::ui.next') }}"
                >
                    <x-tollerus::icons.chevron-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.next') }}</span>
                </x-tollerus::inputs.button>
                <x-tollerus::inputs.button
                    type="inverse"
                    wire:click="setPage({{ $paginator->lastPage() }})"
                    wire:loading.attr="disabled"
                    rel="prev"
                    title="{{ __('tollerus::ui.last') }}"
                >
                    <x-tollerus::icons.chevron-double-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.last') }}</span>
                </x-tollerus::inputs.button>
            @endif
        </nav>
    @endif
</div>