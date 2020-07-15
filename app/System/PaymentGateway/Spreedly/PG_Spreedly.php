<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/26/18
 * Time: 11:25 AM
 */

namespace App\System\PaymentGateway\Spreedly;

use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\ParentGatewayInterface;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PG_Generic;
use App\System\PaymentGateway\PGInterface;
use App\UserPaymentGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\SeekException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PG_Spreedly extends PG_Generic implements PGInterface, ParentGatewayInterface {

    private $baseUrl = 'https://core.spreedly.com/v1/';
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ParseSpreedly
     */
    private $parser;

    private $env_key = null;
    private $api_secret = null;

    public function __construct(Client $client, array $credentials = null) {

        $this->client = $client;
        $this->parser = new ParseSpreedly();

        if($credentials != null) {
            if (key_exists('env_key', $credentials))
                $this->env_key = $credentials['env_key'];

            if (key_exists('api_secret', $credentials))
                $this->api_secret = $credentials['api_secret'];
        }
    }

    private function setParametersFor3Ds(array &$prams, GateWay $gateWay) {

        // TODO: remove below check when spreedly 3ds is ready.
        if(config('app.debug') == false)
            return;

        if(in_array(GateWay::CHARACTERISTICS_3DS_AUTHORIZE, $gateWay->characteristics)
            && in_array(GateWay::CHARACTERISTICS_3DS_PURCHASE, $gateWay->characteristics)) {

            $prams['transaction']['attempt_3dsecure'] = true;
            $prams['transaction']['three_ds_version'] = "2";

            // callback_url and redirect_url used in the event that the transaction falls back to 3DS1
            $prams['transaction']['redirect_url'] = route('payment-confirmation-spreedly');
            $prams['transaction']['callback_url'] = route('spreedly-hook');
        }
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        // TODO: charge with token

       $this->verifyCardForDirectCharge($card);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 40110);

        $year = date('Y');
        $yearFistHalf = substr($year, 0, 2);

        if(strlen($card->expiryYear) == 2)
            $card->expiryYear = $yearFistHalf . $card->expiryYear;

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/purchase.json';

        $data = array(
            'transaction' => array(
                'credit_card' => array(
                    'first_name' => $card->firstName,
                    'last_name' => $card->lastName,
                    'number' => $card->cardNumber,
                    'month' => $card->expiryMonth,
                    'year' => $card->expiryYear
                ),
                'amount' => (int) (((float)$card->amount) * 100.0),
                'currency_code' => $card->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);

        if(isset($card->cvvCode))
            $data['transaction']['credit_card']['verification_value'] = $card->cvvCode;

        if(isset($card->statement_descriptor))
            $data['transaction']['description'] = $card->statement_descriptor;

        if(isset($card->order_id))
            $data['transaction']['order_id'] = $card->order_id;

        if(isset($card->postalCode))
            $data['transaction']['zip'] = $card->postalCode;

        if(isset($card->city))
            $data['transaction']['city'] = $card->city;

        if(isset($card->state))
            $data['transaction']['state'] = $card->state;

        if(isset($card->country))
            $data['transaction']['country'] = $card->country;

        if(isset($card->phone))
            $data['transaction']['phone'] = $card->phone;

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40111);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40112);


        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }
    }

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway) {

        if(!isset($cardForOtherInformation->amount))
            throw new GatewayException('Missing Amount', 41201);

        if(!isset($cardForOtherInformation->currency))
            throw new GatewayException('Missing Currency Code', 41202);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 41203);

        if(!isset($customer->token))
            throw new GatewayException('Customer token is missing', 41204);

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/purchase.json';

        $data = array(
            'transaction' => array(
                'payment_method_token' => $customer->token,
                'amount' => (int) (((float)$cardForOtherInformation->amount) * 100.0),
                'currency_code' => $cardForOtherInformation->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);


        if(isset($cardForOtherInformation->statement_descriptor))
            $data['transaction']['description'] = $cardForOtherInformation->statement_descriptor;

        if(isset($cardForOtherInformation->order_id))
            $data['transaction']['order_id'] = $cardForOtherInformation->order_id;

        if(isset($cardForOtherInformation->postalCode))
            $data['transaction']['zip'] = $cardForOtherInformation->postalCode;

        if(isset($cardForOtherInformation->city))
            $data['transaction']['city'] = $cardForOtherInformation->city;

        if(isset($cardForOtherInformation->state))
            $data['transaction']['state'] = $cardForOtherInformation->state;

        if(isset($cardForOtherInformation->country))
            $data['transaction']['country'] = $cardForOtherInformation->country;

        if(isset($cardForOtherInformation->phone))
            $data['transaction']['phone'] = $cardForOtherInformation->phone;

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40411);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40412);

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();

            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function refund(Transaction $transaction) {

        if(!isset($transaction->token))
            throw new GatewayException('Token not set for Capture', 40301);

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40302);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40303);


        $url = $this->baseUrl . 'transactions/' . $transaction->token . '/credit.json';

        if($transaction->isPartial) {

            if(!isset($transaction->amount))
                throw new GatewayException('Amount not set', 40304);

            if(!isset($transaction->currency_code))
                throw new GatewayException('Currency Code not set', 40305);
        }

        $data = array('transaction' => array(
            'amount' => (int) (((float)$transaction->amount) * 100.0),
            'currency_code' => $transaction->currency_code
        ));

        try {

            if($transaction->isPartial)
                $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            else
                $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret]]);

            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        $this->verifyCardForDirectCharge($card);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 40410);

        if(!isset($card->firstName) || strlen($card->firstName) == 0)
            throw new GatewayException('First Name required', 40413);

        if(!isset($card->lastName) || strlen($card->lastName) == 0)
            throw new GatewayException('Last Name required', 40414);

        $year = date('Y');
        $yearFistHalf = substr($year, 0, 2);

        if(strlen($card->expiryYear) == 2)
            $card->expiryYear = $yearFistHalf . $card->expiryYear;

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/authorize.json';

        $data = array(
            'transaction' => array(
                'credit_card' => array(
                    'first_name' => $card->firstName,
                    'last_name' => $card->lastName,
                    'number' => $card->cardNumber,
                    'month' => $card->expiryMonth,
                    'year' => $card->expiryYear
                ),
                'amount' => (int) (((float)$card->amount) * 100.0),
                'currency_code' => $card->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);

        if(isset($card->cvvCode))
            $data['transaction']['credit_card']['verification_value'] = $card->cvvCode;

        if(isset($card->statement_descriptor))
            $data['transaction']['description'] = $card->statement_descriptor;

        if(isset($card->order_id))
            $data['transaction']['order_id'] = $card->order_id;

        if(isset($card->postalCode))
            $data['transaction']['zip'] = $card->postalCode;

        if(isset($card->city))
            $data['transaction']['city'] = $card->city;

        if(isset($card->state))
            $data['transaction']['state'] = $card->state;

        if(isset($card->country))
            $data['transaction']['country'] = $card->country;

        if(isset($card->phone))
            $data['transaction']['phone'] = $card->phone;

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40411);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40412);


        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);

            $jsonResponse = $response->getBody()->getContents();

            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function authorizeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway) {

        if(!isset($cardForOtherInformation->amount))
            throw new GatewayException('Missing Amount', 41201);

        if(!isset($cardForOtherInformation->currency))
            throw new GatewayException('Missing Currency Code', 41202);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 41203);

        if(!isset($customer->token))
            throw new GatewayException('Customer token is missing', 41204);

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/authorize.json';

        $data = array(
            'transaction' => array(
                'payment_method_token' => $customer->token,
                'amount' => (int) (((float)$cardForOtherInformation->amount) * 100.0),
                'currency_code' => $cardForOtherInformation->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);


        if(isset($cardForOtherInformation->statement_descriptor))
            $data['transaction']['description'] = $cardForOtherInformation->statement_descriptor;

        if(isset($cardForOtherInformation->order_id))
            $data['transaction']['order_id'] = $cardForOtherInformation->order_id;

        if(isset($cardForOtherInformation->postalCode))
            $data['transaction']['zip'] = $cardForOtherInformation->postalCode;

        if(isset($cardForOtherInformation->city))
            $data['transaction']['city'] = $cardForOtherInformation->city;

        if(isset($cardForOtherInformation->state))
            $data['transaction']['state'] = $cardForOtherInformation->state;

        if(isset($cardForOtherInformation->country))
            $data['transaction']['country'] = $cardForOtherInformation->country;

        if(isset($cardForOtherInformation->phone))
            $data['transaction']['phone'] = $cardForOtherInformation->phone;

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40411);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40412);

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    public function capture(Transaction $transaction) {

        if(!isset($transaction->token))
            throw new GatewayException('Token not set for Capture', 40501);

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40503);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40504);


        $url = $this->baseUrl . 'transactions/' . $transaction->token . '/capture.json';

        if($transaction->isPartial) {

            if(!isset($transaction->amount))
                throw new GatewayException('Amount not set', 40505);

            if(!isset($transaction->currency_code))
                throw new GatewayException('Currency Code not set', 40506);
        }

        $data = array('transaction' => array(
            'amount' => (int) (((float)$transaction->amount) * 100.0),
            'currency_code' => $transaction->currency_code
        ));

        try {

            if($transaction->isPartial)
                $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            else
                $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret]]);

            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    public function addAsCustomer(Card $card) {

        if(!isset($card->firstName))
            throw new GatewayException('Missing First Name', 41101);

        if(!isset($card->lastName))
            throw new GatewayException('Missing Last Name', 41102);

        if(!isset($card->cardNumber))
            throw new GatewayException('Missing Card Number', 41103);

        if(!isset($card->expiryMonth))
            throw new GatewayException('Missing Expiry Month', 41104);

        if(!isset($card->expiryYear))
            throw new GatewayException('Missing Expiry Year', 41105);

        if(!isset($card->eMail))
            throw new GatewayException('Missing Email', 41106);

        $year = date('Y');
        $yearFistHalf = substr($year, 0, 2);

        if(strlen($card->expiryYear) == 2)
            $card->expiryYear = $yearFistHalf . $card->expiryYear;

        $data = array(
            'payment_method' => array(
                'credit_card' => array(
                    'first_name' => $card->firstName,
                    'last_name' => $card->lastName,
                    'number' => $card->cardNumber,
                    'month' => $card->expiryMonth,
                    'year' => $card->expiryYear
                ),
                'email' => $card->eMail,
                'retained' => true
            )
        );

        if(isset($card->cvvCode))
            $data['payment_method']['credit_card']['verification_value'] = $card->cvvCode;

        if($card->metadata != null && count($card->metadata) > 0)
            $data['payment_method']['metadata'] = $card->metadata;

        $url = $this->baseUrl . 'payment_methods.json';

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();

            $cus = $this->parser->parsePaymentMethod($jsonResponse);
            $cus->fullResponse = $jsonResponse;
            return $cus;


        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    public function addAsCustomerWithToken(Card $card) {

        if(!isset($card->firstName))
            throw new GatewayException('Missing First Name', 41101);

        if(!isset($card->lastName))
            throw new GatewayException('Missing Last Name', 41102);

        if(!isset($card->eMail))
            throw new GatewayException('Missing Email', 41106);

        $data = array(
            'payment_method' => array(
                // 'credit_card' => array(
//                    'first_name' => $card->firstName,
//                    'last_name' => $card->lastName,
                   // 'token' => $card->token,
                    // 'payment_method_token' => $card->token
                // ),
                // 'payment_method_token' => $card->token,
                'token' => $card->token,
                'email' => $card->eMail,
                'retained' => true
            )
        );

        if($card->metadata != null && count($card->metadata) > 0)
            $data['payment_method']['metadata'] = $card->metadata;

        if(isset($card->cvvCode))
            $data['payment_method']['credit_card']['verification_value'] = $card->cvvCode;

        $url = $this->baseUrl . 'payment_methods.json';

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();

            $cus = $this->parser->parsePaymentMethod($jsonResponse);
            $cus->fullResponse = $jsonResponse;
            return $cus;


        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    public function cancelAuthorization(Transaction $transaction) {

        if(!isset($transaction->token))
            throw new GatewayException('Token not set for Capture', 41301);

        $url = $this->baseUrl . 'transactions/' . $transaction->token . '/void.json';

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 41302);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 41303);

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret]]);
            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @return array
     * @throws GatewayException
     */
    public function listAllGateways(string $backend_name, array $credentials) {

        $api = 'gateways_options.json';

        if(key_exists('env_key', $credentials) && key_exists('api_secret', $credentials)) {

            $env_key = $credentials['env_key'];
            $api_secret = $credentials['api_secret'];

            try {

                $response = $this->client->get($this->baseUrl . $api, ['auth' => [$env_key, $api_secret]]);
                $jsonResponse = $response->getBody()->getContents();
//                header('Content-Type: application/json');
//                echo $jsonResponse;
//                die();
                 return $this->parser->gateways_options($jsonResponse);

            } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
            BadResponseException | BadRequestHttpException | ClientException | ConnectException |
            SeekException $e) {

                $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
                throw new GatewayException($this->getErrorMessage($e), $code);
            }

        }

        return [];

    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway
     * @return GateWay
     * @throws GatewayException
     */
    public function addGatewayOnParentServer(string $backend_name, array $credentials, Gateway $gateway) {

        $api = 'gateways.json';

        if (!key_exists('env_key', $credentials) || !key_exists('api_secret', $credentials))
            throw new GatewayException('Environment Key OR API Access Secret key missing ', 40701);

        if ($gateway == null)
            throw new GatewayException('Gateway object is Null', 40702);

        if (!isset($gateway->gatewayType))
            throw new GatewayException('Gateway type not set', 40703);

        if (!is_array($gateway->credentials) && $gateway->gatewayType != 'test')
            throw new GatewayException('Credentials should be array of CredentialFormField::class', 40704);

        if (count($gateway->credentials) == 0 && $gateway->gatewayType != 'test')
            throw new GatewayException('Credential array is empty', 40705);

        $postFields = array("gateway" => array("gateway_type" => $gateway->gatewayType));

        if($gateway->description != null && isset($gateway->description))
            $postFields['gateway']['description'] = $gateway->description;

        /**
         * @var $record CredentialFormField
         */
        if($gateway->gatewayType != 'test')
            foreach ($gateway->credentials as $record) {
                $postFields['gateway'][$record->name] = $record->value;
            }

        $env_key = $credentials['env_key'];
        $api_secret = $credentials['api_secret'];

        try {

            $response = $this->client->post($this->baseUrl . $api,
                ['auth' => [$env_key, $api_secret], 'json' => $postFields]);

            $jsonResponse = $response->getBody()->getContents();

//            header('Content-Type: application/json');
//            echo $jsonResponse;
//            die();

            return $this->parser->parseSingleGateway(json_decode($jsonResponse, true)['gateway']);

        } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }
    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @return array
     * @throws GatewayException
     */
    public function getAddedGateways(string $backend_name, array $credentials) {

        $api = 'gateways.json';

        if(key_exists('env_key', $credentials) && key_exists('api_secret', $credentials)) {

            $env_key = $credentials['env_key'];
            $api_secret = $credentials['api_secret'];

            try {

                $response = $this->client->get($this->baseUrl . $api, ['auth' => [$env_key, $api_secret]]);
                $jsonResponse = $response->getBody()->getContents();
                return $this->parser->gateways($jsonResponse);

            } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
            BadResponseException | BadRequestHttpException | ClientException | ConnectException |
            SeekException $e) {

                $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
                throw new GatewayException($this->getErrorMessage($e), $code);
            }

        } else {
            throw new GatewayException('Environment Key OR API Access Secret key missing ', 40701);
        }

    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway Stored in db as json
     * @return GateWay
     * @throws GatewayException
     */
    public function removeGatewayOnParentServer(string $backend_name, array $credentials, GateWay $gateway)
    {

        if(!key_exists('env_key', $credentials) || !key_exists('api_secret', $credentials))
            throw new GatewayException('Environment Key OR API Access Secret key missing ', 40901);

        if(!isset($gateway->token) || $gateway == null)
            throw new GatewayException('Gateway token missing ', 40902);

        $env_key = $credentials['env_key'];
        $api_secret = $credentials['api_secret'];
        $api = '/redact.json';
        $url = $this->baseUrl . 'gateways/' . $gateway->token . $api;

        try {

            $response = $this->client->put($url, ['auth'=>[$env_key, $api_secret]]);
            $op = json_decode($response->getBody()->getContents(), true);

            if(!key_exists('transaction', $op))
                throw new GatewayException('Malformed response', 40903);

            if(!key_exists('gateway', $op['transaction']))
                throw new GatewayException('Gateway object not found', 40904);

            return $this->parser->parseSingleGateway($op['transaction']['gateway']);

        } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway Stored in db as json
     * @return GateWay
     * @throws GatewayException TODO: not tested yet
     */
    public function updateGatewayOnParentServer(string $backend_name, array $credentials, GateWay $gateway) {

        if (!key_exists('env_key', $credentials) || !key_exists('api_secret', $credentials))
            throw new GatewayException('Environment Key OR API Access Secret key missing ', 41001);

        if ($gateway == null)
            throw new GatewayException('Gateway object is Null', 41002);

        if(!isset($gateway->token) || $gateway == null)
            throw new GatewayException('Gateway token missing ', 41003);


        if (!is_array($gateway->credentials) && $gateway->gatewayType != 'test')
            throw new GatewayException('Credentials should be array of CredentialFormField::class', 41004);

        if (count($gateway->credentials) == 0 && $gateway->gatewayType != 'test')
            throw new GatewayException('Credential array is empty', 41005);

        $postFields = array("gateway" => array());

        if($gateway->description != null && isset($gateway->description))
            $postFields['gateway']['description'] = $gateway->description;

        /**
         * @var $record CredentialFormField
         */
        if($gateway->gatewayType != 'test')
            foreach ($gateway->credentials as $record) {
                $postFields['gateway'][$record->name] = $record->value;
            }

        $env_key = $credentials['env_key'];
        $api_secret = $credentials['api_secret'];

        try {

            $url = $this->baseUrl .  'gateways/' . $gateway->token . '.json';
            $response = $this->client->put($url, ['auth' => [$env_key, $api_secret], 'json' => $postFields]);
            $jsonResponse = $response->getBody()->getContents();
            return $this->parser->parseSingleGateway(json_decode($jsonResponse, true)['gateway']);

        } catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     * @throws GatewayException
     */
    function chargeThroughCardToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        $this->verifyCardForToken($card);

        if(!isset($card->firstName) || strlen($card->firstName) == 0)
            throw new GatewayException('First Name required', 40413);

        if(!isset($card->lastName) || strlen($card->lastName) == 0)
            throw new GatewayException('Last Name required', 40414);

        if(!isset($card->order_id) || strlen($card->order_id) == 0)
            throw new GatewayException('Order Id missing', 40417);

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40411);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40412);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 41203);

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/purchase.json';

        $data = array(
            'transaction' => array(
                'payment_method_token' => $card->token,
                'amount' => (int) (((float)$card->amount) * 100.0),
                'currency_code' => $card->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);

        if(isset($cardForOtherInformation->statement_descriptor))
            $data['transaction']['description'] = $card->statement_descriptor;

        if(isset($cardForOtherInformation->order_id))
            $data['transaction']['order_id'] = $card->order_id;

        if(isset($cardForOtherInformation->postalCode))
            $data['transaction']['zip'] = $card->postalCode;

        if(isset($cardForOtherInformation->city))
            $data['transaction']['city'] = $card->city;

        if(isset($cardForOtherInformation->state))
            $data['transaction']['state'] = $card->state;

        if(isset($cardForOtherInformation->country))
            $data['transaction']['country'] = $card->country;

        if(isset($cardForOtherInformation->phone))
            $data['transaction']['phone'] = $card->phone;

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();

            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    /**
     * @return array
     */
    function getTerminal() {

        return array(
            'is-token' => true,
            'is-redirect' => false,
            'redirect-link' => null,
            'cc-form-name' => 'gateway_partials.spreedly_partial',
            'public-key' => $this->env_key
        );
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        $this->verifyCardForToken($card);

        if(!isset($card->firstName) || strlen($card->firstName) == 0)
            throw new GatewayException('First Name required', 40413);

        if(!isset($card->lastName) || strlen($card->lastName) == 0)
            throw new GatewayException('Last Name required', 40414);

        if($this->env_key == null)
            throw new GatewayException('Environment Key not found', 40411);

        if($this->api_secret == null)
            throw new GatewayException('API Access Secret not found', 40412);

        $gateWay = new Gateway($userPaymentGateway->gateway);

        if(!isset($gateWay->token))
            throw new GatewayException('Gateway token is missing', 41203);

        $url = $this->baseUrl . 'gateways/' . $gateWay->token . '/authorize.json';

        $data = array(
            'transaction' => array(
                'payment_method_token' => $card->token,
                'amount' => (int) (((float)$card->amount) * 100.0),
                'currency_code' => $card->currency
            )
        );

        $this->setParametersFor3Ds($data, $gateWay);

        if(isset($cardForOtherInformation->statement_descriptor))
            $data['transaction']['description'] = $card->statement_descriptor;

        if(isset($cardForOtherInformation->order_id))
            $data['transaction']['order_id'] = $card->order_id;

        if(isset($cardForOtherInformation->postalCode))
            $data['transaction']['zip'] = $card->postalCode;

        if(isset($cardForOtherInformation->city))
            $data['transaction']['city'] = $card->city;

        if(isset($cardForOtherInformation->state))
            $data['transaction']['state'] = $card->state;

        if(isset($cardForOtherInformation->country))
            $data['transaction']['country'] = $card->country;

        if(isset($cardForOtherInformation->phone))
            $data['transaction']['phone'] = $card->phone;

        try {

            $response = $this->client->post($url, ['auth' => [$this->env_key, $this->api_secret], RequestOptions::JSON => $data]);
            $jsonResponse = $response->getBody()->getContents();
            $tran = $this->parser->parseAuthorizeTransaction($jsonResponse);
            $tran->fullResponse = $jsonResponse;
            return $tran;

        }  catch (ServerException | TooManyRedirectsException | TransferException | RequestException |
        BadResponseException | BadRequestHttpException | ClientException | ConnectException |
        SeekException $e) {

            $code = $this->getNetworkErrorCode($this->isNetworkErrorCode($e->getCode()), $e->getCode());
            throw new GatewayException($this->getErrorMessage($e), $code);
        }

    }

    private function getErrorMessage($e) {

        $content = $e->getResponse()->getBody()->getContents();
        $json = json_decode($content, true);
        $message = '';
        $mFlag = true;
        if(key_exists('errors', $json)) {
            foreach($json['errors'] as $error) {
                if(key_exists('message', $error)) {
                    $message .= ' ' . $error['message'];
                    $mFlag = false;
                }
            }

        } elseif(key_exists('transaction', $json)) {
            $message = $json['transaction']['message'];
            $mFlag = false;
        }


        if($mFlag)
            $message = $content;

        return $message;
    }

    /**
     * @param string $payment_intent
     * @param string $payment_intent_client_secret
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     */
    public function afterAuthentication(string $payment_intent, string $payment_intent_client_secret, UserPaymentGateway $userPaymentGateway)
    {
        // TODO: After authentication capture payment here, also change parameters to array as spreedly does not have intents
        return null;
    }

    public function getTerminalForFrontEndCharge($amount, string $currency, string $customer_token, bool $capture, UserPaymentGateway $userPaymentGateway)
    {
        // TODO: Implement getTerminalForFrontEndCharge() method.
    }

    public function updateCustomerPaymentMethod(Customer $customer, UserPaymentGateway $userPaymentGateway)
    {
        // TODO: Implement updateCustomerPaymentMethod() method.
    }
}