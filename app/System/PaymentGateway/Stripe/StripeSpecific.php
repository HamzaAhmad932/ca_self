<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 3/8/19
 * Time: 12:50 PM
 */

namespace App\System\PaymentGateway\Stripe;


use App\System\PaymentGateway\Models\GateWay;

interface StripeSpecific {

    /**
     * @param string $code
     * @param GateWay $gateway
     * @return GateWay
     */
    function stripeConnectAttachAccount(string $code, GateWay $gateway);
}