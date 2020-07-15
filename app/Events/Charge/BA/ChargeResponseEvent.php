<?php

namespace App\Events\Charge\BA;

use App\System\PaymentGateway\Models\Transaction;
use App\TransactionDetail;
use App\TransactionInit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChargeResponseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const CHARGE_SUCCESS_CASE = 'charge_success';
    const CHARGE_FAILED_CASE = 'charge_failed';
    const CHARGE_3DS_REQUIRED_CASE = 'charge_3ds_required';
    const CHARGE_NETWORK_FAILURE_CASE = 'charge_network_failure';

    /**
     * @var TransactionInit
     */
    public $transaction_init;
    /**
     * @var Transaction
     */
    public $response;
    /**
     * @var string
     */
    public $type;
    /**
     * @var TransactionDetail
     */
    public $transaction_detail;


    /**
     * ChargeResponseEvent constructor.
     * @param TransactionInit $transaction_init
     * @param Transaction $response
     * @param string $type
     * @param TransactionDetail|null $transaction_detail
     */
    public function __construct(TransactionInit $transaction_init, Transaction $response, string $type, TransactionDetail $transaction_detail = null)
    {
        $this->transaction_detail = $transaction_detail;
        $this->transaction_init = $transaction_init;
        $this->response = $response;
        $this->type = $type;

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
