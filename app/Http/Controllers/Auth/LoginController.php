<?php

namespace App\Http\Controllers\Auth;

use App\Rules\Recaptcha;
use App\Rules\CheckUserActiveOrNot;
use \Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Zttp\Zttp;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
 
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    
    protected $redirectTo = '';//admin/dashboard

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }


    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => ['required','email', new CheckUserActiveOrNot],
            'password' => 'required|string',
            //'status' =>  ['required', new CheckUserActiveOrNot]
            //'recaptchaToken' => ['required', new Recaptcha], //TODO UNCOMMENT TO RE-Activate Recapcha
        ]);

    }

    public function showLoginForm()
    {
        return view('v2.auth.login');
    }


}