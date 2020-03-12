@extends('layouts.front')

@section('form')
    @auth
        <a href="{{ route('dashboard') }}" class="w-full bg-green-600 px-4 py-3 text-white font-bold text-center">Go To Dashboard</a>
    @endauth
    @guest
        <login-form></login-form>
    @endguest
@endsection