<?php

namespace App\Listeners;

use App\TransactionInit;
use App\Mail\GenericEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Events\NewGatewaySelectEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewGatewaySelectListener implements ShouldQueue
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
     * @param  NewGatewaySelectEvent  $event
     * @return void
     */
    public function handle(NewGatewaySelectEvent $event)
    {

        //SMS sending
        //GenericTwilioSmsJob::dispatch($event->user->phone, "You are registered successfully on Charge Automation");


        $data = app()->make('Bookings', ['user_account_id' => $event->userAccount->id]);
        $effected_bookings = $data->voidPGEffectedBookings($event->userAccount, $event->property_info_id, $event->userPaymentGateway);

        Log::info(json_encode($effected_bookings));

        //$fromArr = [ (!is_null($event->userAccount->email) ? $event->userAccount->email : $event->userAccount->users->first()->email),  $event->userAccount->name ];
        $user = $event->userAccount->users->first();

        $check_company_logo = checkImageExists( $event->userAccount->company_logo, $event->userAccount->name, config('db_const.logos_directory.company.value') );
        $company_image = asset('storage/uploads/companylogos/'.$check_company_logo['company_image']);

        $name = (($event->userAccount->name != null) ? $event->userAccount->name : $user->name);

       //if (!is_null($effected_bookings)) {
            $email = array(
                'subject' => 'Payment Gateway Changed.',
                'markdown' => 'emails.new_gateway_selected',
                'name' => $name,
                //'from'=>$fromArr,
                'noReply' => true,
                'companyName' => $event->userAccount->name,
                'companyImage' => $company_image,
                'companyInitials' => $check_company_logo['company_initial'],
                'textData' => $effected_bookings,
                'url' =>  url('client/'.$event->property_info_id.'/effected-bookings'),
            );


            Mail::to($event->userAccount->users->first()->email)->send(new GenericEmail($email));

        //}


    }
}





