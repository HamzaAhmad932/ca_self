<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\UserAccount;
use App\PropertyInfo;

class GatewayAddEvent {
    
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $userAccount = null;
    public $propertyInfo = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserAccount $userAccount = null, PropertyInfo $propertyInfo = null) {
        $this->userAccount = $userAccount;
        $this->propertyInfo = $propertyInfo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
