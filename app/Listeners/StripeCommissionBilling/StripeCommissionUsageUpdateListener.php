<?php

namespace App\Listeners\StripeCommissionBilling;

use App\Events\StripeCommissionBilling\StripeCommissionUsageUpdateEvent;
use App\System\StripeCommissionBilling\StripeCommissionBillingBase;
use App\System\StripeCommissionBilling\StripeCommissionBillingTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use \Exception;
use Illuminate\Support\Facades\Log;
use \Throwable;

class StripeCommissionUsageUpdateListener implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;
    use StripeCommissionBillingTrait;

    /**
     * @var StripeCommissionUsageUpdateEvent $event
     */
    private $event;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  StripeCommissionUsageUpdateEvent  $event
     * @return void
     */
    public function handle(StripeCommissionUsageUpdateEvent $event)
    {
        $this->event = $event;
        $instance = resolve($this->event->modelName)::findOrFail($this->event->modelId);
        // TODO Remove Checks For Releases
        if ((config('app.env') === 'local' || config('app.debug') == true)
            || (in_array($instance->user_account_id , ['micasa_account_id' => 2, 'life_suits_account_id' => 3]))) {
            $this->handleCommissionUsageUpdateRequest($instance);
        }
    }

    /**
     * @param $instance
     */
    private function handleCommissionUsageUpdateRequest($instance)
    {
        try {
            switch ($this->event->actionType) {
                case StripeCommissionBillingBase::ACTION_NUMBER_OF_TRANSACTION_UPDATE :
                case StripeCommissionBillingBase::ACTION_VOLUME_OF_TRANSACTION_UPDATE :
                    $this->updateMeteredUsageSubscriptionInvoiceOnTransactionSuccess($instance);
                    break;
                case StripeCommissionBillingBase::ACTION_NUMBER_OF_BOOKING_UPDATE :
                    $this->updateMeteredUsageSubscriptionInvoiceOnNewBooking($instance);
                    break;
                case StripeCommissionBillingBase::ACTION_NUMBER_OF_PROPERTY_UPDATE :
                    $this->updateMeteredUsageSubscriptionInvoiceOnPropertyConnectDisconnect($instance);
                    break;
                default:
                    Log::error('Undefined or inValid Commission Billing Usage Update Acton Type',
                        ['eventParameters' => json_encode($this->event)]);
                    break;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'StackTrace' => $e->getTraceAsString()]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'StackTrace' => $e->getTraceAsString()]);
        }
    }
}
