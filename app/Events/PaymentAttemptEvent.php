<?php

namespace App\Events;

use App\User;
use App\BookingInfo;
use App\TransactionInit;
use App\TransactionDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PaymentAttemptEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking_info;

    /**
     * Create a new event instance.
     *
     * @param User $userd
     */
    public function __construct(BookingInfo $booking_info){
        $this->booking_info = $booking_info;
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
