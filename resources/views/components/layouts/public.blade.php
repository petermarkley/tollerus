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
    <x-tollerus::layouts.admin :title="$title">
        <div data-route="public">
            {{ $slot }}
        </div>
    </x-tollerus::layouts.admin>
@endif
