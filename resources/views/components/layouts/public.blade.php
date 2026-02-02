@php
    $hostLayout = config('tollerus.public_layout');
    $section = config('tollerus.public_layout_section', 'content');
@endphp

@if ($hostLayout)
    @include('tollerus::components.layouts.public-host', ['hostLayout' => $hostLayout])
@else
    <x-tollerus::layouts.admin :title="$title">
        {{ $slot }}
    </x-tollerus::layouts.admin>
@endif
