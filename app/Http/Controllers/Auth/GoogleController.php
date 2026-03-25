<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class GoogleController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');
        return $provider->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');
        $googleUser = $provider->stateless()->user();

        $email = $googleUser->getEmail();
        if (!$email) {
            abort(400, 'Google n’a pas retourné d’adresse email.');
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser->getName() ?: $email,
                'avatar' => $googleUser->getAvatar(),
                // Le champ password est obligatoire dans notre table `users`.
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user, true);

        return redirect()->route('chat');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

