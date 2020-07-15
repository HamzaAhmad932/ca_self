<?php

namespace App\Events\Auth\BA;

use App\AuthorizationDetails;
use App\CreditCardAuthorization;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\System\PaymentGateway\Models\Transaction;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AuthResponseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const AUTH_SUCCESS_CASE = 'auth_success';
    const AUTH_FAILED_CASE = 'auth_failed';
    const AUTH_3DS_REQUIRED_CASE = 'auth_3ds_required';
    const AUTH_NETWORK_FAILURE_CASE = 'auth_network_failure';

    public $credit_card_authorization;

    public $response;

    public $type;

    public $authorization_details;

    /**
     * Create a new event instance.
     *
     * @param CreditCardAuthorization $cardAuthorization
     * @param Transaction $response
     * @param string $type
     * @param AuthorizationDetails|null $authorizationDetails
     */
    public function __construct(CreditCardAuthorization $cardAuthorization, Transaction $response, string $type, AuthorizationDetails $authorizationDetails = null)
    {
        $this->type = $type;
        $this->response = $response;
        $this->authorization_details = $authorizationDetails;
        $this->credit_card_authorization = $cardAuthorization;
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
