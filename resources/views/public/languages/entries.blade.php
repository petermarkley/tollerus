<x-tollerus::layouts.public :title="$title">
    <div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$langCount"/>
        @if (isset($breadcrumbs))
            <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs" isPublic="true"/>
        @endif
        <div class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            <div class="w-full flex flex-col md:flex-row gap-4 items-stretch md:items-center">
                <h2 class="text-2xl font-bold">{{ __('tollerus::ui.all_entries_for_language', ['lang' => $language->name]) }}</h2>
                <div class="flex flex-row gap-4 items-center justify-center">
                    <a
                        href="#"
                        class="relative flex flex-row gap-2 items-center cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse"
                    >
                        <x-tollerus::icons.bars-arrow-down/>
                        <span>{{ __('tollerus::ui.sort_by_transliterated', ['transliterated' => config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))]) }}</span>
                    </a>
                    <a
                        href="#"
                        class="relative flex flex-row gap-2 items-center cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse"
                    >
                        <x-tollerus::icons.bars-arrow-down/>
                        <span>{{ __('tollerus::ui.sort_by_native') }}</span>
                    </a>
                </div>
            </div>
            <div class="w-full p-4 h-auto md:h-200 lg:h-104 flex flex-col justify-start items-start flex-nowrap md:flex-wrap gap-2 border-y-2 border-tollerus-border">
                @if (count($paginator->items()) > 0)
                    @foreach ($paginator->items() as $entry)
                        <a
                            href="#"
                            class="text-tollerus-secondary hover:text-tollerus-secondary-hover"
                        >
                            @if ($entry['transliterated'])
                                <div class="flex flex-row gap-4 justify-start items-center">
                                    <span>{{ $entry['transliterated'] }}</span>
                                    @if ($language->primaryNeography)
                                        <span class="tollerus_{{ $language->primaryNeography->machine_name }}">{{ $entry['native'] }}</span>
                                    @else
                                        <span>{{ $entry['native'] }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="italic font-normal">{{ __('tollerus::ui.entry_nameless') }}</span>
                            @endif
                        </a>
                    @endforeach
                @else
                    <span class="italic text-zinc-700 dark:text-zinc-400">{{ __('tollerus::ui.no_results') }}</span>
                @endif
            </div>
            <div class="w-full">
                {{ $paginator->links('tollerus::public.languages.pagination-links', data: ['scrollTo' => false]) }}
            </div>
        </div>
    </div>
</x-tollerus::layouts.public>
