<?php

namespace App\Events\StripeCommissionBilling;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Stripe\Event;

class StripeCommissionWebHookEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;

    /**
     * StripeCommissionWebHookEvent constructor.
     * @param Event $event
     */
     public function __construct(Event $event)
    {
        $this->event = $event;
    }


    /**
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
