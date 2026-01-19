<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;  
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;


class OAuthController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function handleProviderCallback()
    {
        $oauthUser = Socialite::driver('microsoft')->user();

        $user = User::firstOrCreate(
            ['email' => $oauthUser->getEmail()],
            [
                'name' => $oauthUser->getName() ?? $oauthUser->getNickname(),
                'password' => bcrypt(str()->random(32)),
            ]
        );

        Auth::login($user, remember: true);

        return redirect()->intended('/admin');
    }
}
