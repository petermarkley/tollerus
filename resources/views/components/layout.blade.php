<!DOCTYPE html>
<html class="w-full h-full min-h-screen bg-zinc-300 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-300">
    <head>
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    </head>
    <body class="bg-gradient-to-b from-zinc-300 to-zinc-300 bg-repeat-x dark:from-zinc-800 dark:to-zinc-900 relative -z-20 w-full">
        <div class="w-full h-full absolute -z-10 pointer-events-none bg-gradient-to-b from-zinc-300 to-zinc-400 bg-repeat-x mask-[url(/vendor/tollerus/bg.svg)] mask-size-[400px] mask-alpha mask-repeat dark:from-zinc-800 dark:to-zinc-800 opacity-50 dark:opacity-100" role="none" aria-hidden="true"></div>
        <div class="w-full h-full absolute -z-5 pointer-events-none bg-white dark:bg-zinc-950 opacity-20 dark:opacity-10" role="none" aria-hidden="true"></div>
        <div class="flex flex-col gap-4 w-full items-stretch h-full min-h-screen">
            <header class="w-full py-2 bg-white dark:bg-zinc-800 shadow">
                <div class="md:max-w-[1200px] mx-auto px-6 xl:px-0">
                    <x-tollerus::logo.mono class="h-6 block dark:hidden text-zinc-700"/>
                    <x-tollerus::logo.mono light class="h-6 hidden dark:block"/>
                </div>
            </header>
            @if (isset($breadcrumbs))
                <nav class="w-full md:max-w-[1200px] mx-auto">
                    <ul class="flex flex-row gap-2 justify-start items-center">
                        @foreach ($breadcrumbs as $breadcrumb)
                            @if (isset($breadcrumb['href']))
                                <li class="before:content-['→'] before:mr-2 first:before:content-none"><a href="{{ $breadcrumb['href'] }}">{{ $breadcrumb['text'] }}</a></li>
                            @else
                                <li class="before:content-['→'] before:mr-2 first:before:content-none">{{ $breadcrumb['text'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            @endif
            <main class="w-full md:max-w-[1200px] mx-auto flex-grow">
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
        @stack('tollerus-scripts')
    </body>
</html>