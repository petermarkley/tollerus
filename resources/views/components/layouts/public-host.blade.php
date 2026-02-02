@extends($hostLayout)
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    @if(!empty($tollerusNeographyFontCss))
        <style>{!! $tollerusNeographyFontCss !!}</style>
    @endif
@endpush
@push('scripts')
    <script src="{{ asset('vendor/tollerus/tollerus.js') }}" defer></script>
@endpush
@php($__env->startSection($section))
    {{ $slot }}
@php($__env->stopSection())
