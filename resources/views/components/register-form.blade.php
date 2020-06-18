<form action="/register" method="POST" class="flex flex-col w-full">
    @csrf
    <div class="flex flex-col mb-3">
        <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Email Address') }}</label>
        <input
            name="email"
            type="email"
            class="bg-gray-500 @error('email') error-input @enderror"
            placeholder="Email"
            value="{{ old('email') }}"
            required
        >
        @error('email')
            <span class="error-message">
                {{ $message }}
            </span>
        @enderror
    </div>
    <div class="flex flex-col mb-3">
        <label for="password" class="mt-3 text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Password') }}</label>
        <input
            name="password"
            type="password"
            class="bg-gray-500 @error('password') error-input @enderror"
            placeholder="Password"
            required
        >
        @error('password')
            <span class="error-message">
                {{ $message }}
            </span>
        @enderror
    </div>
    <div class="flex flex-col mb-3">
        <label for="password_confirmation" class="mt-3 text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Confirm Password') }}</label>
        <input
            name="password_confirmation"
            type="password"
            class="bg-gray-500"
            placeholder="Confirm Password"
            required
        >
    </div>

    <button type="submit" class="btn btn-green my-3 py-4">{{ __('Create Account') }}</button>

</form>

<div class="w-full mt-5">
    <p class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Or create an account using') }}</p>
    <a href="login/facebook" class="flex justify-center items-center w-full rounded text-sm bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><i class="fab fa-facebook fa-2x text-white mr-4"></i>{{ __('Sign up with Facebook') }}</a>
</div>