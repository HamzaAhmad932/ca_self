<?php

namespace App\Listeners;

use App\Events\GuestMailFor3DSecureEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class GuestMailFor3DSecureAuthListener extends GuestMailFor3DSecureAbstractListener {

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * @param  GuestMailFor3DSecureEvent  $event
     * @return void
     */
    public function handle(GuestMailFor3DSecureEvent $event) {

        if($event->mailFor3DS !== GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH)
            return;

        $amount = $event->transaction->amount;
        $currency = $event->transaction->currency_code;
        $buttonText = 'Complete Now';

        $subject = '3D Secure Authenticate Required for ' . $event->bookingInfo->pms_booking_id . ' ' . $event->bookingInfo->guest_name . ' ' . $event->bookingInfo->guest_last_name;
        $message = 'Please complete 3D Secure authentication for authorization of ' . $currency . $amount . ' which is required to validate your card against booking ' . $event->bookingInfo->pms_booking_id . ' ' . date('M d', strtotime($event->bookingInfo->check_in_date)). ' - ' .date('M d, Y', strtotime($event->bookingInfo->check_out_date)) .'. You can proceed by clicking on below button. ';

        if(empty($event->transaction->authenticationUrl)) {
            $buttonText = 'Authorize ' . $currency . $amount;
            $event->transaction->authenticationUrl = URL::signedRoute('checkout', ['id'=>$event->creditCardAuthorization->id, 'type'=>GuestMailFor3DSecureEvent::MAIL_FOR_3DS_AUTH]);
        }

        $this->sendMail($event, $message, $subject, $buttonText);

    }
}
