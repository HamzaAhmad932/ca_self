<?php


namespace App\System\StripeCommissionBilling;

use App\BookingInfo;
use App\PropertyInfo;
use App\TransactionInit;
use App\UserAccount;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeCommissionBillingBase
{
    const BOOKING_INFO_MODEL     = BookingInfo::class;
    const TRANSACTION_INIT_MODEL = TransactionInit::class;
    const PROPERTY_INFO_MODEL    = PropertyInfo::class;

    const ACTION_NUMBER_OF_PROPERTY_UPDATE = 'number_of_property_usage_update';
    const ACTION_NUMBER_OF_TRANSACTION_UPDATE = 'number_of_transaction_usage_update';
    const ACTION_VOLUME_OF_TRANSACTION_UPDATE = 'volume_of_transaction_usage_update';
    const ACTION_NUMBER_OF_BOOKING_UPDATE = 'number_of_booking_usage_update';

    protected $stripeSecretKey;
    protected $endPointSecret;

    /**
     * @param $payload
     * @param $signatureHeader
     * @return Event
     * @throws SignatureVerificationException
     */
    public function validateStripeRequestSignature($payload, $signatureHeader)
    {
        Stripe::setApiKey($this->stripeSecretKey);
        return Webhook::constructEvent($payload, $signatureHeader,  $this->endPointSecret);
    }

    /**
     * @param $stripeSecretKey
     */
    public function setBillingApiKey($stripeSecretKey)
    {
        $this->stripeSecretKey = $stripeSecretKey;
    }

    /**
     * @param $endPointSecret
     */
    public function setEndPointSecretKey($endPointSecret)
    {
        $this->endPointSecret = $endPointSecret;
    }
}
