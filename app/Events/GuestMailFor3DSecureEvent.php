<?php

namespace App\Events;

use App\BookingInfo;
use App\CreditCardAuthorization;
use App\PropertyInfo;
use App\System\PaymentGateway\Models\Transaction;
use App\TransactionInit;
use App\UserAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GuestMailFor3DSecureEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const MAIL_FOR_3DS_CHARGE = 1;
    const MAIL_FOR_3DS_AUTH = 2;
    const MAIL_FOR_3DS_AUTH_SSD = 3;
    const MAIL_FOR_3DS_UPSELL = 4;

    /**
     * @var Transaction
     */
    public $transaction;
    /**
     * @var PropertyInfo
     */
    public $propertyInfo;
    /**
     * @var UserAccount
     */
    public $userAccount;
    /**
     * @var BookingInfo
     */
    public $bookingInfo;
    /**
     * @var int
     */
    public $mailFor3DS;
    /**
     * @var TransactionInit|null
     */
    public $transactionInit;
    /**
     * @var CreditCardAuthorization|null
     */
    public $creditCardAuthorization;


    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction
     * @param PropertyInfo $propertyInfo
     * @param UserAccount $userAccount
     * @param BookingInfo $bookingInfo
     * @param int $mailFor3DS
     * @param TransactionInit|null $transactionInit
     * @param CreditCardAuthorization|null $creditCardAuthorization
     */
    public function __construct(Transaction $transaction, PropertyInfo $propertyInfo, UserAccount $userAccount,
                                BookingInfo $bookingInfo, int $mailFor3DS, TransactionInit $transactionInit = null, CreditCardAuthorization $creditCardAuthorization = null) {

        $this->transaction = $transaction;
        $this->propertyInfo = $propertyInfo;
        $this->userAccount = $userAccount;
        $this->bookingInfo = $bookingInfo;
        $this->mailFor3DS = $mailFor3DS;
        $this->transactionInit = $transactionInit;
        $this->creditCardAuthorization = $creditCardAuthorization;
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
