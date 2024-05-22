<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use App\Utilities\Overrider;
use Auth;
use Socialite;

class SocialController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        Overrider::load("SocialSettings");
    }

    public function redirect($provider) {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider) {
        $userSocial = Socialite::driver($provider)->stateless()->user();
        $users      = User::where(['email' => $userSocial->getEmail()])->first();

        if ($users) {
            Auth::login($users);
            return redirect(RouteServiceProvider::HOME);
        }
		
		return redirect()->to('/login')->with('error',_lang('Sorry, We did not find any account associated with your email !'));	
    }
}
