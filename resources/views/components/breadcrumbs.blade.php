@props([
    'breadcrumbs',
    'isPublic' => false,
])
<nav class="w-full md:max-w-[1200px] mx-auto px-6 xl:px-0">
    <ul class="flex flex-row gap-2 justify-start items-center">
        @foreach ($breadcrumbs as $breadcrumb)
            @if (isset($breadcrumb['href']))
                <li class="before:content-['→'] before:mr-2 first:before:content-none">
                    <a
                        href="{{ $breadcrumb['href'] }}"
                        @class(['text-tollerus-primary hover:text-tollerus-primary-hover'=>filter_var($isPublic, FILTER_VALIDATE_BOOLEAN)])
                    >{{ $breadcrumb['text'] }}</a>
                </li>
            @else
                <li class="before:content-['→'] before:mr-2 first:before:content-none">{{ $breadcrumb['text'] }}</li>
            @endif
        @endforeach
    </ul>
</nav>
