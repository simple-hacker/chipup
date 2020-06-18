@extends('layouts.front')

@section('form')

<div class="text-center">
    <h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">{{ __('Verify Your Email Address') }}</h2>

    <div class="card-body">
        @if (session('resent'))
            <div class="alert alert-success" role="alert">
                {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
        @endif

        <p class="text-white mb-2">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
        <p class="text-white mb-2">{{ __('If you did not receive the email') }}</p>

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-green my-3 py-4 w-3/4">{{ __('click here to request another') }}</button>.
        </form>
    </div>
@endsection
