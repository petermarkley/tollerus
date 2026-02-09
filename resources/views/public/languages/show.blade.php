<x-tollerus::layouts.public :title="$title">
    <div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$langCount"/>
        @if (isset($breadcrumbs))
            <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs" isPublic="true"/>
        @endif
        <div class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            <h2 class="text-2xl font-bold">{{ $language->name }}</h2>
            <div class="prose">{!! $language->intro !!}</div>
            <div class="w-full p-4 rounded-lg inset-shadow-sm bg-tollerus-muted flex flex-col gap-4">
                <h3 class="text-lg font-bold flex flex-row gap-2 items-center">
                    <x-tollerus::icons.neography />
                    <span>{{ __('tollerus::ui.writing_systems') }}</span>
                </h3>
                @if ($neographies->count() == 0)
                    <p class="italic opacity-50">{{ __('tollerus::ui.no_writing_systems_notice') }}</p>
                @else
                    <div class="w-full flex flex-col gap-4" x-data="{ currentNeography: {{ $language->primary_neography ?? $neographies->first()->id }} }">
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
                        @foreach ($neographies as $neography)
                            <div
                                x-cloak x-show="currentNeography == {{ $neography->id }}"
                                id="tabcontent-{{ $neography->id }}"
                                class="w-full flex flex-col gap-4"
                            >
                                @foreach ($neography->sections as $section)
                                    <div class="w-full flex flex-col gap-4">
                                        <h4 class="text-lg font-bold">{{ $section->name }}</h4>
                                        <div class="prose">{!! $section->intro !!}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-tollerus::layouts.public>
