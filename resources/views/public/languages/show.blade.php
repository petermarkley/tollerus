<x-tollerus::layouts.public :title="$title">
    <div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$langCount"/>
        @if (isset($breadcrumbs))
            <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs" isPublic="true"/>
        @endif
        <div class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            <div>
                <h2 class="text-2xl font-bold">{{ $language->name }}</h2>
                <div class="prose">{!! $language->intro !!}</div>
            </div>
        </div>
    </div>
</x-tollerus::layouts.public>
