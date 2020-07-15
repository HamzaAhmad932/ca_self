<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use App\UserAccount;
use App\BookingInfo;


class PMSPreferencesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userAccount;
    public $bookingInfo;
    public $transactionInitId;
    public $preferenceFormId;
    public $authorizationId;

    /**
     * PMSPreferencesEvent constructor.
     * @param UserAccount $userAccount
     * @param BookingInfo $bookingInfo
     * @param $transactionInitId
     * @param $preferenceFormId
     * @param int $authorizationId
     */

    public function __construct(UserAccount $userAccount, BookingInfo $bookingInfo,  $transactionInitId, $preferenceFormId, $authorizationId = 0)
    {

      $this->userAccount = $userAccount;
      $this->bookingInfo = $bookingInfo;
      $this->transactionInitId = $transactionInitId;
      $this->authorizationId = $authorizationId;
      $this->preferenceFormId = $preferenceFormId;
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
