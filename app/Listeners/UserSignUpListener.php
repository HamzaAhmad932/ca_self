<?php

namespace App\Listeners;

use App\Mail\GenericEmail;
use App\Events\UserSignUpEvent;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserSignUpNotification;


class UserSignUpListener {
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Handle the event.
     *
     * @param  UserSignUpEvent  $event
     * @return void
     */
    public function handle(UserSignUpEvent $event)
    {
        try {

            // Notifications sending to the admins on new user Register on Charge Automation
            if (config('app.debug') == false && config('app.env')  == 'production' && $event->is_resend == false) {
                $admins = Role::findByName("superAdmin", 'admin');
                $admins = $admins->users()->get();
                Notification::send($admins, new UserSignUpNotification($event->user));
            }

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), ['File'=> __FILE__, 'StackTrace'  => $exception->getTraceAsString()]);

        }

    }
}
