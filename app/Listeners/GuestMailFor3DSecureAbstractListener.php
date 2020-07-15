<?php


namespace App\Listeners;


use App\Events\GuestMailFor3DSecureEvent;
use App\Mail\GenericEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

abstract class GuestMailFor3DSecureAbstractListener implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param GuestMailFor3DSecureEvent $event
     * @param string $message
     * @param string $subject
     * @param string $buttonText
     * @return void
     */
    public function sendMail(GuestMailFor3DSecureEvent $event, string $message, string $subject, string $buttonText) {

        if(!empty($event->transaction->authenticationUrl)) {

            try {

                // $url = \Shortener::shorten($event->transaction->authenticationUrl);
                $url = $event->transaction->authenticationUrl;

                $fromArr = [ (!is_null($event->propertyInfo->property_email) ? $event->propertyInfo->property_email : (!is_null($event->userAccount->email) ? $event->userAccount->email : $event->userAccount->users->first()->email)),  $event->propertyInfo->name ];
                //$logo = ($event->propertyInfo->logo == 'no_image.png') ? '' : asset('storage/uploads/property_logos/'.$event->propertyInfo->logo);

                $check_property_image = checkImageExists( $event->propertyInfo->logo, $event->propertyInfo->name, config('db_const.logos_directory.property.value') );
                $logo = asset('storage/uploads/property_logos/'.$check_property_image['property_image']);

                if(!empty($event->bookingInfo->guest_email))
                    Mail::to($event->bookingInfo->guest_email)->send(
                        new GenericEmail(
                            array(
                                'subject' => $subject,
                                'markdown' => 'emails.NewBookingReceivedMessage',
                                'name' => $event->bookingInfo->guest_name,
                                'company' => $event->userAccount->name,
                                'companyImage' => $logo,
                                'companyInitials' => $check_property_image['property_initial'],
                                'companyName' => $event->propertyInfo->name,
                                'from'=> $fromArr,
                                'url' => $url,
                                'noReply' => true,
                                'btn' => $buttonText,
                                'msgs'=> $message
                            )
                        )
                    );

            } catch(\Exception $e){
                Log::error($e->getMessage(), ['File'=>GuestMailFor3DSecureAbstractListener::class, 'Stack'=>$e->getTraceAsString()]);
            }

        }


    }

}