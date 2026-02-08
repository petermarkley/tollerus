<x-tollerus::layouts.public :title="$title">
    <div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
        <x-tollerus::public.nav-main currentPage="language_info" :langCount="$languages->count()"/>
        <ul class="w-full flex flex-col gap-4 items-start bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            @foreach ($languages as $language)
                <li class="flex">
                    <a href="{{ route('tollerus.public.languages.show', ['language' => $language]) }}" class="cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse">{{ $language->name }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</x-tollerus::layouts.public>
