<form action="/login" method="POST" class="w-full flex flex-col mb-5">
    @csrf
    <div class="flex flex-col mb-3">
        <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Email Address') }}</label>
        <input
            name="email"
            type="email"
            class="bg-gray-500 @error('email') error-input @enderror"
            placeholder="Email"
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

    <button type="submit" class="btn btn-green my-3 py-4">{{ __('Login') }}</button>

    <div class="flex justify-around mt-3">
        <div class="flex justify-start items-center w-64">
            <input type="checkbox" name="remember" class="mr-2">
            <label for="remember" class="w-56 text-sm uppercase tracking-wider font-bold text-gray-200">{{ __('Remember me') }}</label>
        </div>
        <a href="/password/reset" class="text-green-500 hover:text-green-400 uppercase text-sm font-bold tracking-wide">{{ __('Forgot your password?') }}</a>
    </div>
</form>




<div class="w-full mt-5">
    <p class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">{{ __('Or login with') }}</p>
    <a href="login/facebook" class="flex justify-center items-center w-full rounded text-sm bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><i class="fab fa-facebook fa-2x text-white mr-4"></i>{{ __('Continue with Facebook') }}</a>
</div>