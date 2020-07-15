<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/24/18
 * Time: 11:57 AM
 */

namespace App\System\PaymentGateway;


use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\PGOptions;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\Models\Account;
use App\UserPaymentGateway;

interface PGInterface {

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCard(Card $card, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function refund(Transaction $transaction);

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCard(Card $card, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithToken(Card $card, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     */
    function capture(Transaction $transaction);

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    function addAsCustomer(Card $card);

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    function addAsCustomerWithToken(Card $card);

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function cancelAuthorization(Transaction $transaction);

    /**
     * @return array
     */
    function getTerminal();

    /**
     * @param $amount
     * @param string $currency
     * @param string $customer_token
     * @param bool $capture
     * @param UserPaymentGateway $userPaymentGateway
     * @param string $description
     * @return array
     */
    function getTerminalForFrontEndCharge($amount, string $currency, string $customer_token, bool $capture, UserPaymentGateway $userPaymentGateway, string $description = '');

    /**
     * @param Customer $customer
     * @param UserPaymentGateway $userPaymentGateway
     * @return mixed
     */
    function updateCustomerPaymentMethod(Customer $customer, UserPaymentGateway $userPaymentGateway);

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     */
    function chargeThroughCardToken(Card $card, UserPaymentGateway $userPaymentGateway);

    /**
     * @param string $payment_intent
     * @param string $payment_intent_client_secret
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     */
    function afterAuthentication(string $payment_intent, string $payment_intent_client_secret, UserPaymentGateway $userPaymentGateway);

    /**
     * @param UserPaymentGateway $userPaymentGateway
     * @return Account|null
     */
    function getAccount(UserPaymentGateway $userPaymentGateway);

}