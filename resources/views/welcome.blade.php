@extends('layouts.front')

@section('form')
    @if (session('registered'))
        <div class="text-green-500 font-medium tracking-wide mb-5">
            {{ session('registered') }}
        </div>
        <a href="{{ route('setup.index') }}" class="w-5/6 p-4 bg-green-500 hover:bg-green-400 rounded cursor-pointer text-white uppercase text-sm font-bold tracking-wide text-center">Continue With Account Set Up</a>
    @else
        @auth
            <a href="{{ route('dashboard') }}" class="w-5/6 p-4 bg-green-500 hover:bg-green-400 rounded cursor-pointer text-white uppercase text-sm font-bold tracking-wide text-center">Go To Dashboard</a>
        @endauth
        @guest
            @include('components.login-form')
        @endguest
    @endif
@endsection

@section('link')
    <a href="/register" class="w-5/6 p-4 bg-green-500 hover:bg-green-400 rounded-b cursor-pointer text-white uppercase text-sm font-bold tracking-wide text-center">Click here to sign up for an account</a>
@endsection
