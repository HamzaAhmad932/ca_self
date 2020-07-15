<?php

namespace App\Events;

use App\UserAccount;
use App\BookingInfo;
use App\UserPaymentGateway;
use App\System\PMS\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\System\PaymentGateway\Models\Card;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TransactionInitEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Card
     */
    public $card;
    /**
     * @var Booking
     */
    public $booking;
    /**
     * @var UserAccount
     */
    public $userAccount;
    /**
     * @var string
     */
    public $typeOfPaymentSource;
    /**
     * @var int
     */
    public $propertyInfoId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var UserPaymentGateway
     */
    public $userPaymentGateway;
    /**
     * @var BookingInfo
     */
    public $bookingInfoNewObject;


    public $customer_object;
    public $cc_info;

    public $cc_info_type;

    /**
     * Create a new event instance.
     *
     * @param Card $card
     * @param Booking $booking
     * @param UserAccount $userAccount
     * @param string $typeOfPaymentSource
     * @param int $propertyInfoId
     * @param int $userId
     * @param UserPaymentGateway $userPaymentGateway
     * @param BookingInfo $bookingInfoNewObject
     */

    public function __construct(Card $card, Booking $booking, UserAccount $userAccount, string $typeOfPaymentSource, int $propertyInfoId, int $userId, ?UserPaymentGateway $userPaymentGateway, BookingInfo $bookingInfoNewObject, $customer_object, $cc_info){
        
        $this->card = $card;
        $this->booking = $booking;
        $this->userAccount = $userAccount;
        $this->typeOfPaymentSource = $typeOfPaymentSource;
        $this->propertyInfoId = $propertyInfoId;
        $this->userId=$userId;
        $this->userPaymentGateway=$userPaymentGateway;
        $this->bookingInfoNewObject=$bookingInfoNewObject;
        $this->customer_object = $customer_object;
        $this->cc_info = $cc_info;
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
