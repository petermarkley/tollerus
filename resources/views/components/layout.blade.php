<!DOCTYPE html>
<html class="bg-gradient-to-b from-zinc-300 to-zinc-300 text-zinc-900 bg-repeat-x dark:from-zinc-800 dark:to-zinc-900 dark:text-zinc-300 w-full h-full">
    <head>
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    </head>
    <body>
        <div class="w-full h-full absolute -z-10 pointer-events-none bg-gradient-to-b from-zinc-300 to-zinc-400 bg-repeat-x mask-[url(/vendor/tollerus/bg.svg)] mask-size-[400px] mask-alpha mask-repeat dark:from-zinc-800 dark:to-zinc-800 opacity-50 dark:opacity-100" role="none" aria-hidden="true"></div>
        <header class="py-4 mb-4 border-b-2 border-zinc-400 dark:border-zinc-700">
            <x-tollerus::logo.color class="h-18 block dark:hidden mx-auto"/>
            <x-tollerus::logo.color light class="h-18 hidden dark:block mx-auto"/>
        </header>
        <main class="mx-10">
            {{ $slot }}
        </main>
    </body>
</html>