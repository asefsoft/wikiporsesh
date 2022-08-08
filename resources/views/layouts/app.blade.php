<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
{{--        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">--}}

        <!-- Scripts -->
        @wireUiScripts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>

    <body dir='rtl' class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-sky-500">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-sky-300 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif


            <!-- Page Content -->
            <main>
{{--                <x-button rounded negative label="Negative" />--}}
{{--                <x-button icon="exclamation" warning label="Warning" />--}}

{{--                <div class="flex w-full max-w-sm mx-auto overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">--}}
{{--                    <div class="flex items-center justify-center w-12 bg-blue-500">--}}
{{--                        <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">--}}
{{--                            <path d="M20 3.33331C10.8 3.33331 3.33337 10.8 3.33337 20C3.33337 29.2 10.8 36.6666 20 36.6666C29.2 36.6666 36.6667 29.2 36.6667 20C36.6667 10.8 29.2 3.33331 20 3.33331ZM21.6667 28.3333H18.3334V25H21.6667V28.3333ZM21.6667 21.6666H18.3334V11.6666H21.6667V21.6666Z"/>--}}
{{--                        </svg>--}}
{{--                    </div>--}}

{{--                    <div class="px-4 py-2 -mx-3">--}}
{{--                        <div class="mx-3">--}}
{{--                            <span class="font-semibold text-blue-500 dark:text-blue-400">Info</span>--}}
{{--                            <p class="text-sm text-gray-600 dark:text-gray-200">This channel archived by the owner!</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
