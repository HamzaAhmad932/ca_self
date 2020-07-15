<?php

namespace App\Listeners\Auth\BA;

use App\CreditCardAuthorization;
use App\Events\Emails\EmailEvent;
use App\Events\PMSPreferencesEvent;
use App\Repositories\Bookings\Bookings;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Auth\BA\AuthResponseEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\Settings\PaymentTypeMeta;

class AuthResponseListener
{
    public $payment_type = null;

    public $SDAutoAuthType;

    public $CCAutoAuthType;

    public $SDManualAuthType;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->payment_type = new PaymentTypeMeta();
        $this->SDAutoAuthType = $this->payment_type->getSecurityDepositAutoAuthorize();
        $this->CCAutoAuthType = $this->payment_type->getCreditCardAutoAuthorize();
        $this->SDManualAuthType = $this->payment_type->getSecurityDepositManualAuthorize();
        $this->CCValidationType = $this->payment_type->getAuthTypeCCValidation();
        $this->SDValidationType = $this->payment_type->getAuthTypeSecurityDamageValidation();
    }

    /**
     * Handle the event.
     *
     * @param  AuthResponseEvent  $event
     * @return void
     */
    public function handle(AuthResponseEvent $event)
    {
        switch ($event->type) {
            case AuthResponseEvent::AUTH_SUCCESS_CASE:
                $this->authSuccessCase($event);
                break;
            case AuthResponseEvent::AUTH_FAILED_CASE:
                $this->authFailedCase($event);
                break;
            case AuthResponseEvent::AUTH_NETWORK_FAILURE_CASE:
                $this->authNetworkFailureCase($event);
                break;
            case AuthResponseEvent::AUTH_3DS_REQUIRED_CASE:
                $this->auth3dsRequireCase($event);
                break;
        }
    }

    public function authSuccessCase(AuthResponseEvent $event){

        if ($this->isSDAuth($event->credit_card_authorization)){

            event(
                new PMSPreferencesEvent(
                    $event->credit_card_authorization->userAccount,
                    $event->credit_card_authorization->booking_info,
                    0,
                    config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_SUCCESS'),
                    $event->credit_card_authorization->id)
                );

            //send email to client for successful SD
            event(
                new EmailEvent(
                    config('db_const.emails.heads.sd_authorization_successful.type'),
                    $event->authorization_details->id )
                );

        } else {

            event(
                new PMSPreferencesEvent(
                    $event->credit_card_authorization->userAccount,
                    $event->credit_card_authorization->booking_info,
                    0,
                    config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_SUCCESS'),
                    $event->credit_card_authorization->id)
                );
            //send email to client for successful AUTH
            event(
                new EmailEvent(
                    config('db_const.emails.heads.credit_card_authorization_successful.type'),
                    $event->authorization_details->id )
                );
        }
    }

    public function isSDAuth(CreditCardAuthorization $credit_card_authorization){

        return ($credit_card_authorization->type == $this->SDAutoAuthType)
                || ($credit_card_authorization->type == $this->SDManualAuthType);
    }

    public function authFailedCase(AuthResponseEvent $event){

        if($event->credit_card_authorization->decline_email_sent == 0){

            $reason = !empty($event->response->exceptionMessage)
                ? $event->response->exceptionMessage
                : $event->response->message;

            if($event->credit_card_authorization->type == $this->CCValidationType){

                /* Send Email on Auth Failed */
                event(
                    new EmailEvent(
                        config('db_const.emails.heads.credit_card_authorization_failed.type'),
                        $event->authorization_details->id,
                        ['error_msg' => $reason ]
                    )
                );

            } elseif ($event->credit_card_authorization->type == $this->SDValidationType){

                // To Both Client and Guest
                event(
                    new EmailEvent(
                        config('db_const.emails.heads.sd_auth_failed.type'),
                        $event->credit_card_authorization->id,
                        ['error_msg' => $reason ]
                    )
                );
            }
        }

        Bookings::BA_reportInvalidCardForBDCChannel($event->credit_card_authorization->booking_info);

        //SDD Auth | CC Auth Failed
        if (($event->credit_card_authorization->type == $this->SDAutoAuthType) || ($event->credit_card_authorization->type == $this->SDManualAuthType)) {
            event(
                new PMSPreferencesEvent(
                    $event->credit_card_authorization->userAccount,
                    $event->credit_card_authorization->booking_info,
                    0,
                    config('db_const.user_preferences.preferences.SECURITY_DEPOSIT_AUTH_CAPTURE_FAILED'),
                    $event->credit_card_authorization->id
                )
            );
        }else {
            event(
                new PMSPreferencesEvent(
                    $event->credit_card_authorization->userAccount,
                    $event->credit_card_authorization->booking_info,
                    0,
                    config('db_const.user_preferences.preferences.CREDIT_CARD_VALIDATION_AUTH_FAILED'),
                    $event->credit_card_authorization->id
                )
            );
        }
    }

    public function authNetworkFailureCase(AuthResponseEvent $event){

        if ($event->credit_card_authorization->attempts_for_500 >= 4) {
            sendMailToAppDevelopers(
                'Network Failure',
                'Network Failure',
                json_encode($event->credit_card_authorization, JSON_PRETTY_PRINT)
            );
        }
    }

    public function auth3dsRequireCase(AuthResponseEvent $event){

        /* Inform 3DS Charge Authentication Required */

        //Get email type config for SD and CC Auth by checking the type of auth
        $email_type = $this->isSDAuth($event->credit_card_authorization)
            ? config('db_const.emails.heads.sd_3ds_required.type')
            : config('db_const.emails.heads.auth_3ds_required.type');

        event(
            new EmailEvent(
                $email_type,
                $event->credit_card_authorization->id
            )
        );
    }
}
