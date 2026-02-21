<x-tollerus::layouts.public :title="$title">
    <div
        x-data="{ highlight: new URLSearchParams(window.location.search).get('hl') }"
        class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start"
        @popstate.window="highlight = new URLSearchParams(window.location.search).get('hl')"
    >
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$langCount"/>
        @if (isset($breadcrumbs))
            <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs" isPublic="true"/>
        @endif
        <div class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            <div class="w-full">
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 items-start sm:items-center">
                    <h2 class="text-2xl font-bold">{{ $language->name }}</h2>
                    <a
                        href="{{ route('tollerus.public.languages.entries', ['language' => $language]) }}"
                        class="relative flex flex-row gap-2 items-center cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse"
                    >
                        <x-tollerus::icons.entries />
                        <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.browse_entries') }}</span>
                    </a>
                </div>
                <div class="prose">{!! $language->intro !!}</div>
            </div>
            @if ($neographies->count() > 0)
                <div class="w-full p-4 rounded-lg inset-shadow-sm bg-tollerus-muted flex flex-col gap-4">
                    @if ($neographies->count() > 1)
                        <h3 class="text-lg font-bold flex flex-row gap-2 items-center">
                            <x-tollerus::icons.neography />
                            <span>{{ __('tollerus::ui.writing_systems') }}</span>
                        </h3>
                    @endif
                    <div class="w-full flex flex-col gap-6" x-data="{ currentNeography: {{ $startingNeography }} }">
                        @if ($neographies->count() > 1)
                            <ul role="tablist" class="w-full flex flex-row flex-wrap gap-4 justify-start items-center border-b-4 border-tollerus-surface">
                                @foreach ($neographies as $neography)
                                    <li
                                        role="tab"
                                        x-bind:aria-selected="currentNeography == {{ $neography->id }}"
                                        tabindex="0"
                                        aria-controls="tabcontent-{{ $neography->id }}"
                                        title="{{ $neography->name }}"
                                        @click="currentNeography = {{ $neography->id }};"
                                        @keydown.enter.prevent="currentNeography = {{ $neography->id }};"
                                        @keydown.space.prevent="currentNeography = {{ $neography->id }};"
                                        x-bind:class="{
                                            'relative rounded-t-lg flex flex-row justify-start items-center gap-2 cursor-pointer py-2 px-4 focus:outline-2 outline-offset-2 outline-tollerus-ring': true,
                                            'bg-tollerus-surface-inactive hover:bg-tollerus-surface': currentNeography!={{ $neography->id }},
                                            'bg-tollerus-surface hover:bg-tollerus-surface-hover': currentNeography=={{ $neography->id }},
                                        }"
                                    >{{ $neography->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @foreach ($neographies as $neography)
                            <div
                                x-cloak x-show="currentNeography == {{ $neography->id }}"
                                id="tabcontent-{{ $neography->id }}"
                                class="w-full flex flex-col gap-4"
                            >
                                @foreach ($neography->sections->sortBy('position') as $section)
                                    <div class="w-full flex flex-col">
                                        <h4 class="text-lg font-bold">{{ $section->name }}</h4>
                                        <div class="prose">{!! $section->intro !!}</div>
                                        <div class="w-full flex flex-col gap-6">
                                            @foreach ($section->glyphGroups->sortBy('position') as $group)
                                                <ol class="mx-4 w-full columns-1 sm:columns-2 md:columns-3 lg:columns-4 [column-fill:balance]">
                                                    @foreach ($group->glyphs->sortBy('position') as $glyph)
                                                        <li class="py-2 flex flex-row justify-start items-center">
                                                            <a
                                                                id="{{ $glyph->global_id }}"
                                                                class="relative flex flex-row gap-3 text-tollerus-text"
                                                                @click="highlight = $el.id; $store.highlightFunctions.updateParam($el.id)"
                                                            >
                                                                <span
                                                                    @class([
                                                                        "min-w-12 tollerus_{$neography->machine_name}",
                                                                        'text-4xl' => empty($glyph->pronunciation_transliterated),
                                                                        'text-6xl' => !empty($glyph->pronunciation_transliterated),
                                                                    ])
                                                                >@if($glyph->render_base)&#x25CC;@endif{{ $glyph->glyph }}</span>
                                                                <div class="flex flex-col items-start justify-center">
                                                                    @if (!empty($glyph->transliterated))
                                                                        <div class="flex flex-row gap-1 justify-start items-baseline">
                                                                            <span class="text-sm min-w-15">{{ $glyph->transliterated }}</span>
                                                                            <span class="text-sm min-w-15">/{{ $glyph->phonemic }}/</span>
                                                                        </div>
                                                                    @endif
                                                                    @if (!empty($glyph->pronunciation_transliterated))
                                                                        <div class="flex flex-row gap-1 justify-start items-baseline">
                                                                            <span class="text-sm min-w-10">{{ $glyph->pronunciation_transliterated }}</span>
                                                                            <span class="text-sm min-w-10">/{{ $glyph->pronunciation_phonemic }}/</span>
                                                                            <span class="text-sm ml-2 tollerus_{{ $neography->machine_name }}">{{ $glyph->pronunciation_native }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if (!empty($glyph->note))
                                                                        <span class="text-sm">{{ $glyph->note }}</span>
                                                                    @endif
                                                                </div>
                                                                <x-tollerus::public.highlight :globalId="$glyph->global_id"/>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-tollerus::layouts.public>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('highlightFunctions', {
        updateParam(newHl) {
            const url = new URL(window.location.href);
            url.searchParams.set('hl', newHl);
            window.history.pushState({}, '', url);
        },
    });
});
</script>
