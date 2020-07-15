<?php


namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\System\PaymentGateway\Models\Card;
use App\System\PMS\Models\Booking;
use App\BookingInfo;
use App\UserAccount;
use App\UserPaymentGateway;

class BAModifyBookingsEvent {
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
    /**
     * @var null
     */
    public $dueDate;

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
     * @param $dueDate
     */

    public function __construct(Card $card, Booking $booking, UserAccount $userAccount, string $typeOfPaymentSource,
                                int $propertyInfoId, int $userId,UserPaymentGateway $userPaymentGateway,
                                BookingInfo $bookingInfoNewObject, $dueDate = null)
    {
        $this->card = $card;
        $this->booking = $booking;
        $this->userAccount = $userAccount;
        $this->typeOfPaymentSource = $typeOfPaymentSource;
        $this->propertyInfoId = $propertyInfoId;
        $this->userId=$userId;
        $this->userPaymentGateway=$userPaymentGateway;
        $this->bookingInfoNewObject=$bookingInfoNewObject;
        $this->dueDate = $dueDate;
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
