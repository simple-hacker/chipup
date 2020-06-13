@extends('layouts.front')

@section('form')
    @auth
        <a href="{{ route('dashboard') }}" class="w-full bg-green-600 px-4 py-3 text-white font-bold text-center">Go To Dashboard</a>
    @endauth
    @guest
        <login-form></login-form>
    @endguest
@endsection

@section('link')
    <a href="/register" class="w-5/6 p-4 bg-green-500 hover:bg-green-400 rounded-b cursor-pointer text-white uppercase text-sm font-bold tracking-wide text-center">Click here to sign up for an account</a>
@endsection
