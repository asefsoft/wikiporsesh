<!DOCTYPE html>
<html dir='rtl' lang="fa">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#324cc3"/>
        <meta property="og:site_name" content="ویکی پرسش. محلی برای کسب دانش"/>
        <link rel="shortcut icon" type="image/png" href="{{asset("static/stuff/logo1-cr.png")}}"/>

        {{$head ?? ''}}

        <title>{{ config('app.name', 'no title!') }}</title>

        @vite(['resources/css/app.css','resources/css/manual.css', 'resources/js/app.js'])

        <!-- Styles -->
        @if (needLivewireScripts())
            @livewireStyles
        @endif
    </head>

    <body dir='rtl' class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-gradient-to-r from-cyan-500 to-blue-500"
{{--             style="background-image: linear-gradient(135deg, rgb(159, 191, 241) 0%, rgb(50, 76, 195) 100%);"--}}
        >
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif


            <!-- Page Content -->
            <main>
                <div class="pb-8 pt-4 sm:pt-8">
                    <div class="max-w-[1366px] mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-sm">
                            <div class="md:grid md:grid-cols-10 md:gap-4">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>

{{--        @stack('modals')--}}

        @if (needLivewireScripts())
            @livewireScripts
        @endif

        @include('partials.go-top')

        <?php loadTime() ?>

    </body>
</html>
