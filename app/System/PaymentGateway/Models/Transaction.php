<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 11/6/18
 * Time: 11:09 AM
 */

namespace App\System\PaymentGateway\Models;


class Transaction {

    const STATE_REQUIRE_ACTION = 'requires_action';
    const STATE_REQUIRE_CAPTURE = 'requires_capture';
    const STATE_SUCCEEDED = 'succeeded';
    const STATE_AUTHENTICATION_FAILED = 'payment_intent_authentication_failure';

    public function __construct(string $json_from_db = null) {

        if($json_from_db !== null) {
            $obj = json_decode($json_from_db, true);
            $this->token = $obj['token'];
            $this->type = $obj['type'];
            $this->message = $obj['message'];
            $this->order_id = $obj['order_id'];
            $this->state = $obj['state'];
            $this->status = $obj['status'];
            $this->currency_code = $obj['currency_code'];
            $this->amount = $obj['amount'];
            $this->created_at = $obj['created_at'];
            $this->updated_at = $obj['updated_at'];
            $this->description = $obj['description'];
            $this->fullResponse = $obj['fullResponse'];
            $this->exceptionMessage = $obj['exceptionMessage'];

            if($obj != null) {
                if (key_exists('paymentIntentId', $obj))
                    $this->paymentIntentId = $obj['paymentIntentId'];

                if (key_exists('payment_intent_client_secret', $obj))
                    $this->payment_intent_client_secret = $obj['payment_intent_client_secret'];

                if (key_exists('authenticationUrl', $obj))
                    $this->authenticationUrl = $obj['authenticationUrl'];

                if (key_exists('checkout_form', $obj))
                    $this->checkout_form = $obj['checkout_form'];
            }

        }

    }

    public $token = '';
    public $type = '';
    public $message = '';
    public $order_id = '';
    public $state = '';

    /**
     * @var boolean
     */
    public $status = false;

    public $currency_code = '';
    public $amount = '';
    public $description = '';
    public $created_at = '';
    public $updated_at = '';

    public $isPartial = false;

    // Server response as json
    public $fullResponse = '';

    public $exceptionMessage = '';

    /**
     * For Stripe Payment Intent ID
     * @var string
     */
    public $paymentIntentId = '';

    /**
     * Client Secret for payment intent, only for Stripe
     * @var string
     */
    public $payment_intent_client_secret = '';

    /**
     * Guest will we/should be redirected to this link if present and
     * state is <strong>requires_action</strong>
     * @var null|string
     */
    public $authenticationUrl = null;

    /**
     * For Spreedly 3d secure v1
     * @var null|string
     */
    public $checkout_form = null;

}