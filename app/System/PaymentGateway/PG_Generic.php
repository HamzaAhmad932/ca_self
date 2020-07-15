<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 11/9/18
 * Time: 11:43 AM
 */

namespace App\System\PaymentGateway;


use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;

abstract class PG_Generic {

    /**
     * @param Card $card
     * @return bool
     * @throws GatewayException
     */
    public function verifyCardForDirectCharge(Card $card) {

        if(empty($card->firstName))
            throw new GatewayException('Missing First Name', 10001);

        if(empty($card->lastName))
            throw new GatewayException('Missing Last Name', 10002);

        if(empty($card->cardNumber))
            throw new GatewayException('Missing Card Number', 10003);

        if(empty($card->expiryMonth))
            throw new GatewayException('Missing Expiry Month', 10004);

        if(empty($card->expiryYear))
            throw new GatewayException('Missing Expiry Year', 10005);

        if(empty($card->amount))
            throw new GatewayException('Missing Amount', 10006);

        if(empty($card->currency))
            throw new GatewayException('Missing Currency Code', 10007);

        return true;

    }

    /**
     * @param Card $card
     * @return bool
     * @throws GatewayException
     */
    public function verifyCardForAuthorizeWithToken(Card $card) {

        // if(empty($card->firstName))
        //     throw new GatewayException('Missing First Name', 10001);

        // if(empty($card->lastName))
        //     throw new GatewayException('Missing Last Name', 10002);

        // if(empty($card->cardNumber))
        //     throw new GatewayException('Missing Card Number', 10003);

        // if(empty($card->expiryMonth))
        //     throw new GatewayException('Missing Expiry Month', 10004);

        // if(empty($card->expiryYear))
        //     throw new GatewayException('Missing Expiry Year', 10005);

        if(empty($card->amount))
            throw new GatewayException('Missing Amount', 10006);

        if(empty($card->currency))
            throw new GatewayException('Missing Currency Code', 10007);

        return true;

    }

    /**
     * @param Card $card
     * @return bool
     * @throws GatewayException
     */
    public function verifyCardForCustomerAdd(Card $card) {

        if(empty($card->firstName))
            throw new GatewayException('Missing First Name', 10001);

        if(empty($card->lastName))
            throw new GatewayException('Missing Last Name', 10002);

        if(empty($card->cardNumber))
            throw new GatewayException('Missing Card Number', 10003);

        if(empty($card->expiryMonth))
            throw new GatewayException('Missing Expiry Month', 10004);

        if(empty($card->expiryYear))
            throw new GatewayException('Missing Expiry Year', 10005);

        return true;

    }

    /**
     * @param Card $card
     * @return bool
     * @throws GatewayException
     */
    public function verifyCardForToken(Card $card) {

        if(empty($card->token) || $card->token == null)
            throw new GatewayException('Card token not set', 50901);

        if(empty($card->amount))
            throw new GatewayException('Missing Amount', 10006);

        if(empty($card->currency))
            throw new GatewayException('Missing Currency Code', 10007);

        return true;

    }

    /**
     * @param Card $card
     * @return bool
     * @throws GatewayException
     */
    public function verifyCardTokenForCustomer(Card $card) {

        if(empty($card->token) || $card->token == null)
            throw new GatewayException('Card token not set', 50901);

        // if(empty($card->amount))
        //     throw new GatewayException('Missing Amount', 10006);

        if(empty($card->currency))
            throw new GatewayException('Missing Currency Code', 10007);

        return true;

    }

    /**
     * check For Network Error From Exception Message
     * @param string $message
     * @return bool
     */
    protected function isNetworkError(string $message) {

        if($message != null) {

            $strPosition = strpos($message, 'Network error');
            if($strPosition !== false)
                return true;

            $strPosition = strpos($message, 'error communicating');
            if($strPosition !== false)
                return true;
        }

        return false;
    }

    /**
     * Check for Network error specifically 5xx
     * @param int $code
     * @return bool
     */
    protected function isNetworkErrorCode(int $code) {
        if($code >= 500)
            return true;

        return false;
    }

    protected function getNetworkErrorCode(bool $isNetworkError, int $defaultErrorCode) {
        if($isNetworkError)
            return PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE;
        return $defaultErrorCode;
    }

    /**
     * @param string $currency
     * @return bool
     * @throws GatewayException
     */
    public function isZeroDecimalCurrency(string $currency) {

        if(empty($currency))
            throw new GatewayException('Missing Currency Code.');

        $list = ['MGA', 'BIF', 'CLP', 'PYG', 'DJF', 'RWF', 'GNF', 'UGX',
            'JPY', 'VND', 'VUV', 'XAF', 'KMF', 'XOF', 'KRW', 'XPF'];

        if(in_array(strtoupper($currency), $list))
            return true;

        return false;

    }

    /**
     * Lowest unit mean that if currency contains floating decimal or not
     * if it does then multiple with 100 and return
     *
     * @param string $currency
     * @param string $amount
     * @return int
     * @throws GatewayException
     */
    public function getAmountInLowestPositiveUnitOfCurrency(string $currency, string $amount) {

        if(empty($amount))
            throw new GatewayException('Missing amount, cannot calculate lowest unit');

        if($this->isZeroDecimalCurrency($currency))
            return (int) abs((int) trim($amount));
        else
            return (int) abs(( ((float) trim($amount)) * 100.0));

    }

}