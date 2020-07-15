<?php

namespace App\Events\StripeCommissionBilling;

use App\TransactionInit;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;


class StripeCommissionUsageUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $modelName;
    public $modelId;
    public $actionType;

    public function __construct(string $modelName,  $modelId, string  $actionType)
    {

        $this->modelId     =  $modelId;
        $this->modelName   =  $modelName;
        $this->actionType  =  $actionType;
    }

    /**
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
