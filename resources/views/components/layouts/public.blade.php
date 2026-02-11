@php
    $hostLayout = config('tollerus.public_layout');
    $section = config('tollerus.public_layout_section', 'content');
    $title = $title ?? 'Tollerus Dictionary App';
@endphp

@if ($hostLayout)
    @include('tollerus::components.layouts.public-host', [
        'hostLayout' => $hostLayout,
        'section' => $section,
        'title' => $title,
    ])
@else
    <x-tollerus::layouts.admin :title="$title" isPublic="true">
        <div data-route="public" class="w-full">
            {{ $slot }}
        </div>
    </x-tollerus::layouts.admin>
@endif
