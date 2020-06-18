@extends('layouts.front')

@section('form')
    @include('components.register-form')
@endsection

@section('link')
    <a href="/login" class="w-5/6 p-4 bg-green-500 hover:bg-green-400 rounded-b cursor-pointer text-white uppercase text-sm font-bold tracking-wide text-center">Already have an account?</a>
@endsection