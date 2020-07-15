<?php

namespace App\Http\Controllers\Auth;

use App\Events\Emails\EmailEvent;
use App\Events\UserSignUpEvent;
use App\RoleAndPermissions;
use App\User;
use App\UserAccount;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use \Exception;
//use App\Rules\Recaptcha;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = 'client/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('v2.auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $messages = [
            'companyname.string' => 'Only alpha-numeric characters allowed',
            'companyname.regex' => 'Only alpha-numeric characters allowed',
            'agree.required' => 'Please accept Terms and Conditions'
        ];
        return Validator::make($data, [
            'name' => 'required|string|max:255|regex:/(^([a-zA-Z0-9&\'_.’\- ]+)(\d+)?$)/u',
            'companyname' => 'required|string|max:255|regex:/(^([a-zA-Z0-9&\'_.’\- ]+)(\d+)?$)/u',
            'phone' => 'required|max:25|min:5',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation'=> 'required',
            'agree' => 'required|boolean|min:1',
            //'recaptchaToken' => ['required', new Recaptcha], //TODO UNCOMMENT TO RE-Activate Recapcha
            'current_pms'=> 'required|string|max:190|regex:/(^([a-zA-Z0-9&\'_.’\- ]+)(\d+)?$)/u'
        ], $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        try {
            /**
             * @var User $user
             * @var UserAccount $userAccount
             */
            //$random_password = str_random(10);

            $userAccount = UserAccount::create([
                'name' => $data['companyname'],
                'company_logo' => 'no_image.png',
                'current_pms' => $data['current_pms'],
                'status' => config('db_const.user_account.status.pending.value')]);

            if ($userAccount) {
                $user = User::create([
                'name' => $data['name'],
                //'phone' => $data['code'].$data['phone'], //country code double appending is removed
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),//Hash::make($random_password),
                'agree' => $data['agree'],
                'is_activated' => false,
                'user_account_id' => $userAccount->id]);

                if ($user) {
                    $user->assignRole('Administrator');// Adding roles to a Super Cient by default
                    // Assign  All User permissions to a Super Client by default
                    foreach (RoleAndPermissions::$userPermission as $permission) {
                        try {
                            $user->givePermissionTo($permission);
                        } catch (Exception $exception) {
                            Log::error($exception->getMessage(), ['File' => __FILE__, 'UserId' => $user->id, 'StackTrace' => $exception->getTraceAsString() ]);
                        }
                    }

                    // send email to new user for email verification
                    event(new EmailEvent(config('db_const.emails.heads.email_verification_new_user.type'), $user->id ));

                    //create notification for super admin for new user
                    event(new UserSignUpEvent($user, ''));

                } else {
                    return redirect('/login');
                }
            }
            $url = URL::signedRoute('activate-user', ['user' => $user]);

            //for intercom
            $user_hash = hash_hmac('sha256', $user->id, 'zGJ7O7dVhSViWAEwgvz5Yog6WqOPyqAU4yWxKfGM');

            echo json_encode(['registered' => 1, 'user' => $user, 'user_account' => $userAccount, 'user_hash' => $user_hash, 'url' => $url]);
            exit();
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), ['StackTrace'=> $exception->getTraceAsString(), 'File' => __FILE__]);
        }
    }
}
