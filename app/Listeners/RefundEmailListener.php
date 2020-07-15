<?php

namespace App\Listeners;

use App\Events\RefundEmailEvent;
use App\Mail\GenericEmail;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\SentEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RefundEmailListener implements ShouldQueue
{

    private $event;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  RefundEmailEvent  $event
     * @return void
     */
    public function handle(RefundEmailEvent $event)
    {
        $event->bookingInfo->property_info = PropertyInfo::where('pms_property_id', $event->bookingInfo->property_id)
            ->where('user_account_id', $event->bookingInfo->user_account_id)->first();
        $this->event = $event;
        $this->sendEmailToClient();
        $this->sendEmailToGuest();
    }

    private function sendEmailToGuest(){

        if (empty($this->event->bookingInfo->guest_email)) {
            Log::notice('Guest Email not Found to Send Refund Email', ['booking_info_id' => $this->event->bookingInfo->id]);
            return;
        }

        $b = new Bookings($this->event->userAccount->id);
        $currency_code = $b->getCurrencyCode($this->event->bookingInfo, $this->event->bookingInfo->property_info);
        $symbol = ((($currency_code != null ) && ($currency_code != '' )) ? $b->getCurrencySymbolByCurrencyCode($currency_code) : '');
        $subject = 'Refund issued | '.Carbon::parse()->format('d M').' | '.$this->event->bookingInfo->guest_name.' | '.$this->event->bookingInfo->pms_booking_id;

        $redirect_url = URL::signedRoute('guest_booking_details', ['id' => $this->event->bookingInfo->id]);

        $check_company_logo = checkImageExists( $this->event->userAccount->company_logo, $this->event->userAccount->name, config('db_const.logos_directory.company.value') );
        $company_image = asset('storage/uploads/companylogos/'.$check_company_logo['company_image']);

        $email = array(
            'subject' => $subject,
            'markdown' => 'emails.RefundEmail',
            'name' => $this->event->bookingInfo->guest_name,
            'guest_name' => $this->event->bookingInfo->guest_name.' '.$this->event->bookingInfo->guest_last_name,
            'noReply' => true,
            'companyName' => $this->event->userAccount->name,
            'companyImage' => $company_image,
            'companyInitials' => $check_company_logo['company_initial'],
            'property_name'=> $this->event->bookingInfo->property_info->name,
            'booking_no'=> $this->event->bookingInfo->pms_booking_id,
            'checkin'=> Carbon::parse($this->event->bookingInfo->check_in_date, 'GMT')->setTimezone($this->event->bookingInfo->property_info->time_zone)->format('M d, Y'),
            'checkout'=> Carbon::parse($this->event->bookingInfo->check_out_date, 'GMT')->setTimezone($this->event->bookingInfo->property_info->time_zone)->format('M d, Y'),
            'amount'=> $symbol.$this->event->amount,
            'url2' =>  $redirect_url,
        );

        Mail::to($this->event->bookingInfo->guest_email)->send(new GenericEmail($email));

        /* In our new email component we will handle this*/
        // $data_encode = json_encode(array('BookingId' => $this->event->bookingInfo->id, 'Amount' => $this->event->amount));

        // $sent_email = new SentEmail([
        //     'booking_info_id' => $this->event->bookingInfo->id,
        //     'email_subject' => $subject,
        //     'email_type' => 'refund_amount',
        //     'sent_to' =>  config('db_const.sent_email.sent_to.guest'),
        //     'encoded_data' => $data_encode,
        // ]);
        // $sent_email->save();
    }

    private function sendEmailToClient(){

        $b = new Bookings($this->event->userAccount->id);
        $currency_code = $b->getCurrencyCode($this->event->bookingInfo, $this->event->bookingInfo->property_info);
        $symbol = ((($currency_code != null ) && ($currency_code != '' )) ? $b->getCurrencySymbolByCurrencyCode($currency_code) : '');
        $subject = 'Refund issued | '.Carbon::parse()->format('d M').' | '.$this->event->bookingInfo->guest_name.' | '.$this->event->bookingInfo->pms_booking_id;
        $client = $this->event->userAccount->users->first();
        $client_email = !empty($client->email) ? $client->email : $this->event->userAccount->email;
        $redirect_url = URL::to('/client/v2/bookings?booking-id='.$this->event->bookingInfo->id);

        $user = $this->event->bookingInfo->user;

        $check_company_logo = checkImageExists( $this->event->userAccount->company_logo, $this->event->userAccount->name, config('db_const.logos_directory.company.value') );
        $company_image = asset('storage/uploads/companylogos/'.$check_company_logo['company_image']);

        $email = array(
            'subject' => $subject,
            'markdown' => 'emails.RefundEmail',
            'name' => $client->name,
            'guest_name' => $this->event->bookingInfo->guest_name.' '.$this->event->bookingInfo->guest_last_name,
            'noReply' => true,
            'companyName' => $this->event->userAccount->name,
            'companyImage' => $company_image,
            'companyInitials' => $check_company_logo['company_initial'],
            'property_name'=> $this->event->bookingInfo->property_info->name,
            'booking_no'=> $this->event->bookingInfo->pms_booking_id,
            'checkin'=> Carbon::parse($this->event->bookingInfo->check_in_date, 'GMT')->setTimezone($this->event->bookingInfo->property_info->time_zone)->format('M d, Y'),
            'checkout'=> Carbon::parse($this->event->bookingInfo->check_out_date, 'GMT')->setTimezone($this->event->bookingInfo->property_info->time_zone)->format('M d, Y'),
            'amount'=> $symbol.$this->event->amount,
            'url2' =>  $redirect_url,
        );

        if(!empty($client_email)){
            Mail::to($client_email)->send(new GenericEmail($email));
        }
    }
}
