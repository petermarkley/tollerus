<!DOCTYPE html>
<html
    id="tollerus_root"
    data-layout="admin"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    <head>
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
        @if (class_exists(\Livewire\Livewire::class) && config('livewire.inject_assets') === false)
            @livewireStyles
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title"        content="Tollerus - {{ $title }}"/>
        <meta property="og:description"  content="The conlang dictionary Laravel package - the luxurious way to build, track, and browse your conlang's lexical data"/>
        <meta property="og:image"        content="{{ asset('/vendor/tollerus/share_preview.jpg') }}"/>
        <meta property="og:image:width"  content="3840"/>
        <meta property="og:image:height" content="2160"/>
        <link rel="icon" type="image/png" href="{{ asset('/vendor/tollerus/favicon/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('/vendor/tollerus/favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('/vendor/tollerus/favicon/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/vendor/tollerus/favicon/apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="Tollerus" />
        <link rel="manifest" href="{{ asset('/vendor/tollerus/favicon/site.webmanifest') }}" />
        @if(!empty($tollerusNeographyFontCss))
            <style>{!! $tollerusNeographyFontCss !!}</style>
        @endif
    </head>
    <body class="bg-gradient-to-b from-zinc-300 to-zinc-300 bg-repeat-x dark:from-zinc-800 dark:to-zinc-900 relative -z-20 w-full">
        <div class="w-full h-full absolute -z-10 pointer-events-none bg-gradient-to-b from-zinc-300 to-zinc-400 bg-repeat-x mask-[url(/vendor/tollerus/bg.svg)] mask-size-[400px] mask-alpha mask-repeat dark:from-zinc-800 dark:to-zinc-800 opacity-50 dark:opacity-100" role="none" aria-hidden="true"></div>
        <div class="w-full h-full absolute -z-5 pointer-events-none bg-white dark:bg-zinc-950 opacity-20 dark:opacity-10" role="none" aria-hidden="true"></div>
        <div class="flex flex-col gap-4 w-full items-stretch h-full min-h-screen">
            <header class="w-full py-2 bg-white dark:bg-zinc-800 shadow">
                <div class="md:max-w-[1200px] mx-auto px-6 xl:px-0">
                    <a href="{{ route('tollerus.admin.index') }}" class="text-zinc-900 dark:text-zinc-300 hover:text-zinc-900 hover:dark:text-zinc-300">
                        <x-tollerus::logo.mono class="h-6 block dark:hidden text-zinc-700"/>
                        <x-tollerus::logo.mono light class="h-6 hidden dark:block"/>
                    </a>
                </div>
            </header>
            @if (isset($breadcrumbs))
                <x-tollerus::breadcrumbs :breadcrumbs="$breadcrumbs"/>
            @endif
            <main
                @class([
                    'w-full mx-auto flex-grow',
                    'md:max-w-[1200px]' => !isset($isPublic),
                ])
            >
                {{ $slot }}
            </main>
            <footer class="w-full md:max-w-[1200px] mx-auto px-6 xl:px-0 text-center text-zinc-800 dark:text-zinc-500 pb-8">
                {!! Str::markdown(__('tollerus::ui.copyright_footer', [
                    'year' => date('Y'),
                    'github_url' => 'https://github.com/petermarkley/tollerus',
                    'lgpl_url' => 'https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html'
                ])) !!}
            </footer>
        </div>
        @if (class_exists(\Livewire\Livewire::class) && config('livewire.inject_assets') === false)
            @livewireScripts
        @endif
        @stack('tollerus-scripts')
        @if (request()->routeIs('tollerus.admin.*'))
            @if (isset($isLivewirePage))
                <script>window.tollerusIsLivewirePage = true;</script>
            @endif
            <script type="module" src="{{ asset('vendor/tollerus/tollerus-admin.js') }}"></script>
        @else
            <script type="module" src="{{ asset('vendor/tollerus/tollerus-public.js') }}"></script>
        @endif
    </body>
</html>