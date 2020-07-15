<?php

namespace App\Events;

use App\UserAccount;
use App\UserPaymentGateway;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewGatewaySelectEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userAccount;
    public $property_info_id;
    public $userPaymentGateway;
    
    /**
     * Create a new event instance.
     *
     * @param UserAccount $userAccount
     * @param $property_info_id
     * @param UserPaymentGateway $userPaymentGateway
     *
     */

    public function __construct(UserAccount $userAccount, $property_info_id, UserPaymentGateway $userPaymentGateway){

        $this->userAccount = $userAccount;
        $this->property_info_id = $property_info_id;
        $this->userPaymentGateway = $userPaymentGateway;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(){
        
        return new PrivateChannel('channel-name');
    }
}
