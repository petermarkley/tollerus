@extends($hostLayout)
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    @if (class_exists(\Livewire\Livewire::class) && config('livewire.inject_assets') === false)
        @livewireStyles
    @endif
    @if(!empty($tollerusNeographyFontCss))
        <style>{!! $tollerusNeographyFontCss !!}</style>
    @endif
@endpush
@push('scripts')
    <script type="module" src="{{ asset('vendor/tollerus/tollerus-public.js') }}"></script>
    @if (class_exists(\Livewire\Livewire::class) && config('livewire.inject_assets') === false)
        @livewireScripts
    @endif
@endpush
@php($__env->startSection($section))
    <div id="tollerus_root" data-layout="custom" data-route="public">
        {{ $slot }}
    </div>
@php($__env->stopSection())
