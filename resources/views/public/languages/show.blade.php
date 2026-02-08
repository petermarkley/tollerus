<x-tollerus::layouts.public :title="$title">
    <div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$langCount"/>
        @if (isset($breadcrumbs))
            <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs" isPublic="true"/>
        @endif
        <div class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            Lorem ipsum dolor sit amet.
        </div>
    </div>
</x-tollerus::layouts.public>
