<!DOCTYPE html>
<html class="w-full h-full min-h-screen bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-300 font-[Baskerville]">
{{--

    This is an example template which the host app can use as a custom layout for Tollerus pages.

    The styles on this page are controlled through two concurrent mechanisms:
        - one for Tollerus-owned page elements, and
        - another for those owned by the host app.

    In this example, the host app defines its own colors using standard Tailwind classes.
    Regardless of how it defines them, it must *also* tell Tollerus about the color scheme using
    the `--tollerus-*` CSS variables. This results in some colors being defined twice, which is
    normal and expected in this situation.

    The CSS color variables below will be fed into an `rgb(R G B / A)` statement so the format
    must be compatible with that function.
    https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/color_value/rgb

--}}
    <head>
        <title>{{ $title }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite('resources/css/app.css')
        @stack('styles')
        <style>
            :root {
                --tollerus-bg: 233 242 237;               /* emerald-100, saturate-20 */
                --tollerus-surface: 255 255 255;          /* white */
                --tollerus-surface-inactive: 246 250 248; /* emerald-50, saturate-20 */
                --tollerus-surface-hover: 246 250 248;    /* emerald-50, saturate-20 */
                --tollerus-text: 55 63 61;                /* emerald-900, saturate-10 */
                --tollerus-text-inverse: 255 255 255;     /* white */
                --tollerus-muted: 246 250 248;            /* emerald-50, saturate-20 */
                --tollerus-border: 146 167 161;           /* emerald-400, saturate-10 */
                --tollerus-primary: 203 82 31;            /* orange-600, saturate-70 */
                --tollerus-primary-hover: 217 112 39;     /* orange-500, saturate-70 */
                --tollerus-secondary: 105 120 115;        /* emerald-600, saturate-10 */
                --tollerus-secondary-hover: 129 148 142;  /* emerald-500, saturate-10 */
                --tollerus-ring: 20 71 230;               /* blue-700 */
                --tollerus-font-main: "Baskerville";
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --tollerus-bg: 31 35 34;                 /* emerald-950, saturate-10 */
                    --tollerus-surface: 55 63 61;            /* emerald-900, saturate-10 */
                    --tollerus-surface-inactive: 22 25 24;   /* emerald-950, saturate-10, 70% opaque over black */
                    --tollerus-surface-hover: 66 76 73;      /* emerald-700, saturate-10 */
                    --tollerus-text: 233 242 237;            /* emerald-100, saturate-20 */
                    --tollerus-text-inverse: 15 17 17;       /* emerald-950, saturate-10, 50% opaque over black */
                    --tollerus-muted: 48 54 53;              /* emerald-950, saturate-10, 30% opaque over emerald-900 saturate-10 */
                    --tollerus-border: 129 148 142;          /* emerald-500, saturate-10 */
                    --tollerus-primary: 224 142 49;          /* orange-400, saturate-70 */
                    --tollerus-primary-hover: 255 184 106;   /* orange-300 */
                    --tollerus-secondary: 189 203 198;       /* emerald-300, saturate-10 */
                    --tollerus-secondary-hover: 218 226 223; /* emerald-200, saturate-10 */
                    --tollerus-ring: 255 255 255;            /* white */
                }
            }
        </style>
    </head>
    <body class="relative">
        <div class="w-full h-full min-h-[100vh] absolute -z-10 pointer-events-none backdrop-saturate-20 dark:backdrop-saturate-10" role="none" aria-hidden="true"></div>
        <main class="pt-4">
            {{-- This section name must match the config value `public_layout_section`. --}}
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>
