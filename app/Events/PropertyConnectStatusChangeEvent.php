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

class PropertyConnectStatusChangeEvent {

    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var array
     */
    public $ids;
    /**
     * @var bool
     */
    public $isPMSPropertyIds;
    public $is_active;


    /**
     * Create a new event instance.
     *
     * @param array $ids
     * @param bool $isPMSPropertyIds set to true if passed ids are PMS Property IDs Or set it to false if ids are of PropertyInfo table.
     * @param bool $is_active
     */
    public function __construct(array $ids, bool $isPMSPropertyIds, $is_active = true) {
        $this->ids = $ids;
        $this->isPMSPropertyIds = $isPMSPropertyIds;
        $this->is_active = $is_active;
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
