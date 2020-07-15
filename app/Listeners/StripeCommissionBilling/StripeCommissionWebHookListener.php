<?php

namespace App\Listeners\StripeCommissionBilling;

use App\Events\StripeCommissionBilling\StripeCommissionWebHookEvent;
use App\System\StripeCommissionBilling\StripeCommissionBillingTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StripeCommissionWebHookListener implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;
    use StripeCommissionBillingTrait;

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
     * @param StripeCommissionWebHookEvent $event
     * @throws \Exception
     */
    public function handle(StripeCommissionWebHookEvent $event)
    {
        $this->event = $event->event;
        $this->handleStripeCommissionHooks();
        Log::notice('Stripe Hook Received: ' .
            $this->event->type , ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Event Content'=> json_encode($this->event->type)]);
    }

    /**
     * @throws \Exception
     */
    private function handleStripeCommissionHooks()
    {    // Add these Cases to StripeCommissionBilling too.
        switch ($this->event->type) {
            case 'customer.created' :
            case 'customer.updated' :
                $this->handleCustomerCreatedHook($this->event);
                break;
            case 'customer.subscription.updated' :
                $this->handleCustomerSubscriptionUpdated($this->event);
                break;
            case 'customer.subscription.trial_will_end':
                $this->handleCustomerTrialWillEndHook($this->event);
                break;
            case 'invoice.upcoming':
                //$this->handleUpComingInvoiceHook($this->event->data->object);
                break;
            case 'invoice.payment_failed':
                //$this->handleCustomerInvoicePaymentFailedHook($this->event);
                break;
            case 'invoice.payment_succeeded':
                //$this->handleCustomerInvoicePaymentSuccessHook($this->event);
                break;
            default:
                //Log::notice('Unexpected event type: ' .  $this->event->type , ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Reason'=>'Unexpected event type']);
                break;
        }
    }
}
