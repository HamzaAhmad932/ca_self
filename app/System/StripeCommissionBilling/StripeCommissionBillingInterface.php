<?php


namespace App\System\StripeCommissionBilling;


use Illuminate\Http\Request;
use Stripe\Event;

interface StripeCommissionBillingInterface
{
    function ListenStripeEvent(Request $request);
    function handleEvent(Event $event);

}