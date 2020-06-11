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

    <!-- Scripts -->
    <script src="{{ asset('js/welcome.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div id="app">

        <div class="flex flex-col xl:flex-row h-screen w-full">
            <div class="hidden xl:block xl:w-3/5 h-1/2 bg-cover bg-right" style="background-image: url('images/background.jpg')">
            </div>
            <div class="h-screen w-full xl:w-2/5 h-1/2 flex flex-col justify-center items-center bg-gray-200 xl:bg-black bg-cover bg-right background-image">
                <div class="flex flex-col items-center bg-gray-100 rounded shadow border border-black w-3/4 p-2">
                    <h1 class="text-black text-4xl w-full text-center font-bold mb-3 border-b">{{ config('app.name', 'Poker') }}</h1>
                    @yield('form')
                </div>
            </div>
        </div>

        <main class="py-4">
            Main content down here.
        </main>
    </div>
</body>
</html>
