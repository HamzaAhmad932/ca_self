<?php

namespace App\Http\Controllers;


use App\Events\Emails\EmailEvent;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Auth;


class EmailVerification extends Controller
{
    public function index (Request $request) {
        if (!$request->hasValidSignature()) {
            abort(401, 'This link is not valid.');
        }

        $id = $request->user;
        $email = $request->email;


        $verify = User::where('id', $id)->where('email', $email)->first();

        if (empty($verify)) {
            abort(401, 'The link has expired. Please contact admin.');
        }

        $user_account = User::find($id)->user_account;

        if($verify->email_verified_at == null){
            $verify->email_verified_at = now();
            $user_account->account_verified_at = now();

            if($user_account->status != config('db_const.user_account.status.active.value')
                && $user_account->status != config('db_const.user_account.status.suspendedbyadmin.value')
                && $verify->parent_user_id == 0)
                $user_account->status = config('db_const.user_account.status.deactive.value');
            
            $verify->save();
            $user_account->save();
            $alerts = array('message' => 'Verified Successfully', 'cls' => 'success' );
            return redirect('/login')->with('alerts',$alerts);
        }else{

            $alerts = array('message' => 'Already verified', 'cls' => 'info' );
            return redirect('/login')->with('alerts',$alerts);
        }
    }


    public function activeuser(Request $request){

        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $user = User::find($request->user);

        Auth::logout(); // logs out the user
        \Session::flush(); // removes all session data

        return view('client.verify_email.email_verification_template', ['name' => $user->name, 'id' => $user->id]);

    }

    public function ResendEmail($id){
        try{

            $user = User::find($id);
            /*
             * Regenerate password for resending verification email
            */
            if (!empty($user->email_verified_at))
                return json_encode(['status'=> false, 'msg'=> 'Already Verified!',]);

            // send email to new user for email verification
            event(new EmailEvent(config('db_const.emails.heads.email_verification_new_user.type'), $user->id ));

            Auth::logout();
            \Session::flush();

            return json_encode(['status'=> true, 'msg' => 'Email Sent on your email address, kindly Check your email to activate your account']);

        }catch (\Exception $e){
            Log::error('Error', ['exception_message'=> $e->getMessage(), 'File'=> 'EmailVerification.php']);
            return json_encode(['status'=> false, 'msg'=> 'Error, Sending Email Failed. Please Try Again!']);
        }
    }

    public function activateUser(Request $request, $id){

        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $user = User::findOrFail($id);
        if (!empty($user->email_verified_at))
            return redirect('/login');

        return view('v2.client.verify_email.email_verification', ['name' => $user->name, 'id' => $user->id]);
    }

}
