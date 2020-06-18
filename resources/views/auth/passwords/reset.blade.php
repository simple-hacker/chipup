@extends('layouts.front')

@section('form')
    <h1 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">{{ __('Reset Password') }}</h1>

    @if (session('status'))
        <div class="text-green-500 font-medium text-base my-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="w-full flex flex-col mb-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="flex flex-col mb-3">
            <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('E-Mail Address') }}</label>
            <input
                id="email"
                name="email"
                type="email"
                placeholder="Email"
                class="@error('email') error-input @enderror"
                value="{{ $email ?? old('email') }}"
                autocomplete="email"
                autofocus
                required
            >
            @error('email')
                <span class="error-message">
                    {{ $message }}
                </span>
            @enderror
        </div>
        
        <div class="flex flex-col mb-3">
            <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Password') }}</label>
            <input
                id="password"
                type="password"
                class="@error('password') error-input @enderror"
                name="password"
                required
                autocomplete="new-password"
            >
            @error('password')
                <span class="error-message">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="flex flex-col mb-3">
            <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Confirm Password') }}</label>
            <input
                id="password-confirm"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-green my-3 py-4">
            {{ __('Reset Password') }}
        </button>
    </form>
@endsection
