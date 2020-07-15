<?php

namespace App\Listeners;

use App\Events\PaymentAttemptEvent;
use App\Mail\BookingPaymentAttempt;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\BookingSources\BookingSources;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\PropertyInfo;

class PaymentAttemptListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentAttemptEvent  $event
     * @return void
     */
    public function handle(PaymentAttemptEvent $event) {

        try {

            $userAccount = $event->booking_info->user_account;

            // $clientNotifySettings = new ClientNotifySettings($userAccount->id);
            // $guestCorrespondence = $clientNotifySettings->isActiveMail(config('db_const.user_notify_Settings.guestCorrespondence'));

            /*
             * If booking is not from supported channels then we use others channel setting for email sending
             */
            //$booking_source_form_id = BookingSources::getBookingSourceFormIdForGuestExperience($event->booking_info->channel_code);

            $notifySettings = new ClientGeneralPreferencesSettings($userAccount->id);
            $guest_notify = $notifySettings->isActiveStatus(config('db_const.general_preferences_form.emailToGuest'),
                $event->booking_info->bookingSourceForm);

            if($event->booking_info->guest_email != null && $guest_notify != 0) {

                $url = URL::signedRoute('guest_booking_details', ['id' => $event->booking_info->id]);

                //$propertyInfo = $userAccount->properties_info->where('pms_property_id', $event->booking_info->property_id)->first();
                //$fromArr = [(!is_null($userAccount->email) ? $userAccount->email : $userAccount->users->first()->email), $propertyInfo->name];

                $data = array('name' => $event->booking_info->guest_name, 'email' => $event->booking_info->guest_email,
                    'body' => 'Welcome to our website. Hope you will enjoy',
                    'status' => $event->booking_info->transactions_init->first()->status,
                    'amount' => $event->booking_info->transactions_init->first()->price,
                    //'from' => $fromArr,
                    'noReply' => true,
                    'url' => $url,
                );

                Mail::to($event->booking_info->guest_email)->send(new BookingPaymentAttempt($data));
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>PaymentAttemptListener::class, 'Line'=>$e->getLine(), 'Stack'=>$e->getTraceAsString()]);
        }
    }
}
