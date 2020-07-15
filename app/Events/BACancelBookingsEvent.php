<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\UserAccount;
use App\BookingInfo;
use App\PropertyInfo;


class BACancelBookingsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * @var UserAccount
     */
    public $userAccount;
    
    /**
     * @var PropertyInfo
     */
    public $propertyInfo;

    /**
     * @var BookingInfo
     */
    public $bookingInfo;

    /**
     * @var string
     */

    public $bookingStatus;
    
    /**
     * @var int
     */
    public $bookingChannelCode;
    

    /**
     * Create a new event instance.
     *
     * @param UserAccount $userAccount
     * @param PropertyInfo $propertyInfo
     * @param BookingInfo $bookingInfo
     * @param $bookingChannelCode
     * @param string $bookingStatus
     */

    public function __construct(UserAccount $userAccount, PropertyInfo  $propertyInfo, BookingInfo $bookingInfo, $bookingChannelCode, string $bookingStatus)
    {
        $this->userAccount  = $userAccount;
        $this->bookingInfo = $bookingInfo;
        $this->propertyInfo = $propertyInfo;
        $this->bookingChannelCode = $bookingChannelCode;
        $this->bookingStatus = $bookingStatus;
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
