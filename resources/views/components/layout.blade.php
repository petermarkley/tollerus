<!DOCTYPE html>
<html class="w-full h-full min-h-screen bg-zinc-300 dark:bg-800 text-zinc-900 dark:text-zinc-300">
    <head>
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    </head>
    <body class="bg-gradient-to-b from-zinc-300 to-zinc-300 bg-repeat-x dark:from-zinc-800 dark:to-zinc-900 relative -z-20">
        <div class="w-full h-full absolute -z-10 pointer-events-none bg-gradient-to-b from-zinc-300 to-zinc-400 bg-repeat-x mask-[url(/vendor/tollerus/bg.svg)] mask-size-[400px] mask-alpha mask-repeat dark:from-zinc-800 dark:to-zinc-800 opacity-50 dark:opacity-100" role="none" aria-hidden="true"></div>
        <div class="flex flex-col gap-4 w-full items-stretch h-full min-h-screen">
            <header class="w-full py-2 bg-zinc-300 dark:bg-zinc-800 shadow">
                <div class="md:max-w-[1200px] mx-auto px-6 md:px-0">
                    <x-tollerus::logo.mono class="h-6 block dark:hidden text-zinc-700"/>
                    <x-tollerus::logo.mono light class="h-6 hidden dark:block"/>
                </div>
            </header>
            <main class="w-full md:max-w-[1200px] mx-auto px-6 md:px-0 flex-grow">
                {{ $slot }}
            </main>
            <footer class="w-full md:max-w-[1200px] mx-auto px-6 md:px-0 text-center text-zinc-800 dark:text-zinc-500 pb-8">
                <p>The <a href="https://github.com/petermarkley/tollerus" class="text-cyan-800 dark:text-cyan-500 opacity-75 saturate-80 dark:saturate-20">Tollerus software</a> is copyright &copy; {{ date('Y') }} by Peter Markley.<br>Licensed via <a href="https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html" class="text-cyan-800 dark:text-cyan-500 opacity-75 saturate-80 dark:saturate-20">LGPL v2.1</a></p>
            </footer>
        </div>
    </body>
</html>