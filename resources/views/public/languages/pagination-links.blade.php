<div class="w-full flex flex-row gap-4 justify-center items-center">
    @if ($paginator->hasPages())
        <div class="hidden md:block">
            <p class="italic opacity-80">{{ __('tollerus::ui.pagination_showing_numbers', [
                'start' => ($paginator->currentPage()-1)*$paginator->perPage() + 1,
                'end' => ($paginator->currentPage() == $paginator->lastPage() ? $paginator->total() : $paginator->currentPage()*$paginator->perPage()),
                'total' => $paginator->total(),
            ]) }}</p>
        </div>
        <nav role="navigation" aria-label="{{ __('tollerus::ui.pagination_navigation') }}" class="flex flex-row gap-2 justify-center items-center">
            @if ($paginator->onFirstPage())
                <a title="{{ __('tollerus::ui.first') }}" class="relative text-tollerus-secondary opacity-50 cursor-not-allowed">
                    <x-tollerus::icons.chevron-double-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.first') }}</span>
                </a>
                <a title="{{ __('tollerus::ui.previous') }}" class="relative text-tollerus-secondary opacity-50 cursor-not-allowed">
                    <x-tollerus::icons.chevron-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.previous') }}</span>
                </a>
            @else
                <a
                    rel="prev"
                    title="{{ __('tollerus::ui.first') }}"
                    class="relative text-tollerus-secondary hover:text-tollerus-secondary-hover cursor-pointer"
                >
                    <x-tollerus::icons.chevron-double-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.first') }}</span>
                </a>
                <a
                    rel="prev"
                    title="{{ __('tollerus::ui.previous') }}"
                    class="relative text-tollerus-secondary hover:text-tollerus-secondary-hover cursor-pointer"
                >
                    <x-tollerus::icons.chevron-left class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.previous') }}</span>
                </a>
            @endif

            @if ($paginator->onLastPage())
                <a title="{{ __('tollerus::ui.next') }}" class="relative text-tollerus-secondary opacity-50 cursor-not-allowed">
                    <x-tollerus::icons.chevron-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.next') }}</span>
                </a>
                <a title="{{ __('tollerus::ui.last') }}" class="relative text-tollerus-secondary opacity-50 cursor-not-allowed">
                    <x-tollerus::icons.chevron-double-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.last') }}</span>
                </a>
            @else
                <a
                    rel="prev"
                    title="{{ __('tollerus::ui.next') }}"
                    class="relative text-tollerus-secondary hover:text-tollerus-secondary-hover cursor-pointer"
                >
                    <x-tollerus::icons.chevron-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.next') }}</span>
                </a>
                <a
                    rel="prev"
                    title="{{ __('tollerus::ui.last') }}"
                    class="relative text-tollerus-secondary hover:text-tollerus-secondary-hover cursor-pointer"
                >
                    <x-tollerus::icons.chevron-double-right class="w-8 h-8"/>
                    <span class="sr-only">{{ __('tollerus::ui.last') }}</span>
                </a>
            @endif
        </nav>
    @endif
</div>