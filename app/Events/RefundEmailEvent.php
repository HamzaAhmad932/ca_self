<?php

namespace App\Events;

use App\BookingInfo;
use App\UserAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefundEmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingInfo;
    public $amount;
    public $userAccount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BookingInfo $bookingInfo, string $amount)
    {
        $this->userAccount = $bookingInfo->user_account;
        $this->bookingInfo = $bookingInfo;
        $this->amount = $amount;
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
