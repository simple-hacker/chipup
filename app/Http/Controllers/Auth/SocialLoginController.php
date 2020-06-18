<?php

namespace App\Http\Controllers\Auth;
use App\User;

use App\SocialLogin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect('/login');
        }

        $user = $this->findOrCreateUser($socialUser, $provider);
        Auth::login($user, true);

        return redirect('/dashboard');
    }


    public function findOrCreateUser($socialUser, $provider)
    {
        $account = SocialLogin::whereProviderName($provider)
                                ->whereProviderId($socialUser->getId())
                                ->first();

        if ($account) {
            return $account->user;
        } else {
            $user = User::whereEmail($socialUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'email' => $socialUser->getEmail(),
                    'email_verified_at' => now() // Email is verified if they log in through a social site.
                ]);
            }

            $user->socialLogins()->create([
                'provider_id'   => $socialUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }
}
