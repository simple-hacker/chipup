@extends('layouts.front')

@section('form')

    <h1 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">{{ __('Reset Password') }}</h1>

    @if (session('status'))
        <div class="text-green-500 font-medium text-base my-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="w-full flex flex-col mb-5">
        @csrf

        <div class="flex flex-col mb-3">
            <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('E-Mail Address') }}</label>
            <input
                name="email"
                type="email"
                placeholder="Email"
                class="@error('email') error-input @enderror"
                required
            >
            @error('email')
                <span class="error-message">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-green my-3 py-4">
            {{ __('Send Password Reset Link') }}
        </button>
    </form>

@endsection
