<?php

namespace App\Events;

use App\PropertyInfo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PropertyDisabledEvent {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var PropertyInfo
     */
    public $propertyInfo;

    /**
     * Create a new event instance.
     *
     * @param PropertyInfo $propertyInfo
     */
    public function __construct(PropertyInfo $propertyInfo) {
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
