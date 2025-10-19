<!DOCTYPE html>
<html class="bg-zinc-300 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-300">
    <head>
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/tollerus/tollerus.css') }}">
    </head>
    <body>
        <header class="py-4 mb-4 border-b-2 border-zinc-400 dark:border-zinc-700">
            <x-tollerus::logo.color class="h-18 block dark:hidden mx-auto"/>
            <x-tollerus::logo.color light class="h-18 hidden dark:block mx-auto"/>
        </header>
        <main class="mx-10">
            {{ $slot }}
        </main>
    </body>
</html>