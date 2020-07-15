<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function showLinkRequestForm()
    {
        return view('v2.auth.password-reset');
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        $res = array('status'=> trans($response));
        return json_encode($res);
    }
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $res = array('email'=> trans($response));
        return json_encode($res);

    }
}
