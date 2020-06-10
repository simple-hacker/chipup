<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Poker') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app" class="bg-background">
        <app
            :user="{{ $user }}"
            :stakes="{{ $stakes }}"
            :limits="{{ $limits }}"
            :variants="{{ $variants }}"
            :table_sizes="{{ $table_sizes }}"
        ></app>
    </div>
</body>
</html>