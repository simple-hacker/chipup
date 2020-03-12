<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Poker') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/setup.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div id="app">
        <div class="border-none w-full md:w-1/2 m-3 p-2 md:p-5 mx-auto">
            <div class="flex flex-col justify-center items-center">
                <h1 class="text-xl md:text-2xl mb-3 font-medium">Welcome to {{ config('app.name', 'Poker') }}!</h1>
                <p class="text-md md:text-lg text-center mb-2 text-gray-800">Let's quickly get you set up.</p>
                <p class="text-xs md:text-sm text-center text-gray-700">You can change these in your settings control panel at a later date.</p>

                <poker-setup
                    class="w-full"
                    v-bind:stakes="{{ $stakes }}"
                    v-bind:limits="{{ $limits }}"
                    v-bind:variants="{{ $variants }}"
                    v-bind:table_sizes="{{ $table_sizes }}"
                
                ></poker-setup>

            </div>
        </div>
    </div>
</body>
</html>