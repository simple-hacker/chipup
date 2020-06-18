<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Poker') }}</title>

    <!-- favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="alternate icon" href="{{ asset('favico.ico') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/js/fontawesome.min.js" integrity="sha256-bP9MBDJ4xkv81w/A2cgwRonMI+eelvZwm7e8rP5JIxA=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/js/brands.min.js" integrity="sha256-OjapPU7ZR5ESNNSe21vgHeQCya5MZT4Y3X94x0JrP90=" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-900">
    <div id="app">

        <div class="flex flex-col xl:flex-row h-screen w-full">
            <div class="hidden xl:block xl:w-3/5 h-1/2 bg-cover bg-right" style="background-image: url(' {{ asset('images/background.jpg') }}')"></div>
            <div class="h-screen w-full xl:w-2/5 h-1/2 flex flex-col justify-center items-center bg-gray-800">

                <div class="flex flex-col items-center bg-gray-600 rounded-t w-5/6 p-3 pb-6">
                    <img src="{{ asset('images/logo.svg') }}" width="75px" alt="Poker Logo">
                    <h1 class="text-4xl w-full text-center font-bold mb-3 text-white">{{ config('app.name', 'Poker') }}</h1>
                    @yield('form')
                </div>
                @guest
                    @yield('link')
                @endguest
            </div>
        </div>

        <main class="py-4">
            Main content down here.
        </main>
    </div>
</body>
</html>
