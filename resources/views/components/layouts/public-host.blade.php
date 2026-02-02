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
@section($section)
    {{ $slot }}
@endsection
