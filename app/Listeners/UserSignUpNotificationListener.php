<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UserSignUpNotification;
use NotificationChannels\Twilio\TwilioChannel;
use Illuminate\Notifications\Events\NotificationSent;

class UserSignUpNotificationListener
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
     * @param NotificationSent $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        if($event->notification instanceof UserSignUpNotification  && $event->channel === TwilioChannel::class) {
            if ($event->response !== null) {
                $response = $event->response->toArray();
                /*
                array:20 [▼
                    "accountSid" => "AC849a0823f8d2eb056d403b98a58802e4"
                    "apiVersion" => "2010-04-01"
                    "body" => "Sent from your Twilio trial account - Your account was approved!10"
                    "dateCreated" => DateTime @1537260735 {#267 ▼
                        date: 2018-09-18 08:52:15.0 +00:00
                    }
                    "dateUpdated" => DateTime @1537260735 {#268 ▼
                        date: 2018-09-18 08:52:15.0 +00:00
                    }
                    "dateSent" => DateTime @1537260735 {#269 ▼
                        date: 2018-09-18 08:52:15.654069 UTC (+00:00)
                    }
                    "direction" => "outbound-api"
                    "errorCode" => null
                    "errorMessage" => null
                    "from" => "+17174290884"
                    "messagingServiceSid" => null
                    "numMedia" => "0"
                    "numSegments" => "1"
                    "price" => null
                    "priceUnit" => "USD"
                    "sid" => "SM77bcd0f00cfd4678967556942ae38111"
                    "status" => "queued"
                    "subresourceUris" => array:1 [▼
                        "media" => "/2010-04-01/Accounts/AC849a0823f8d2eb056d403b98a58802e4/Messages/SM77bcd0f00cfd4678967556942ae38111/Media.json"
                    ]
                    "to" => "+923002438120"
                    "uri" => "/2010-04-01/Accounts/AC849a0823f8d2eb056d403b98a58802e4/Messages/SM77bcd0f00cfd4678967556942ae38111.json"
                ]
                */
                dd($response);
            } else {
                dd($event);
            }
        }

    }
}
