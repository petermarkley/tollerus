<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @stack('styles')
    </head>
    <body>
        <main>
            {{-- This section name must match the config value `public_layout_section`. --}}
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>
