<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/25/18
 * Time: 2:57 PM
 */

namespace App\System\PaymentGateway\Stripe;


use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Account as GatewayAccount;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\PGOptions;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PaymentGateway\PG_Generic;
use App\System\PaymentGateway\PGInterface;
use App\UserPaymentGateway;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\ApiRequestor;
use Stripe\Charge;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\HttpClient\CurlClient;
use Stripe\OAuth;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\Token;

class PG_Stripe extends PG_Generic implements PGInterface, StripeSpecific {
    
    use \App\System\PaymentGateway\Stripe3D\StripeHelpers;

    private $public_key;
    private $secret_key;
    private $MY_Secret_Key = '';
    private $stripeUserId = '';
    private $isStripeConnect = false;

    /**
     * PG_Stripe constructor.
     * @param array|null $credentials
     * @throws GatewayException
     */
    public function __construct(array $credentials = null) {

        $this->MY_Secret_Key = config('db_const.auth_keys.stripe.secret_key');

        if($credentials != null && is_array($credentials)) {

            if(key_exists('stripe_user_id', $credentials)) {

                if(!empty($credentials['stripe_user_id'])) {
                    Stripe::setApiKey($this->MY_Secret_Key);
                    $this->stripeUserId = $credentials['stripe_user_id'];
                    $this->isStripeConnect = true;
                }

            } elseif(key_exists('secret_key', $credentials)) {
                Stripe::setApiKey($credentials['secret_key']);
                $this->secret_key = $credentials['secret_key'];
            }
            // TODO: add these checks regarding connect, express and normal keys
//            else
//                throw new GatewayException('Stripe Secret Key not set', 50001);

            if(key_exists('publishable_key', $credentials)) {
                $this->public_key = $credentials['publishable_key'];
            }
            // TODO: add these checks regarding connect, express and normal keys
//            else
//                throw new GatewayException('Stripe Publishable Key not set', 50002);
        }

        // TODO: Remove or update on Stripe Dashboard
        //Stripe::setApiVersion('2018-11-08');

        if (config('app.debug') == true)
            Stripe::setVerifySslCerts(false);

        $curl = new CurlClient();
        $curl->setConnectTimeout(100); // default 30
        $curl->setTimeout(200); // default 80
        //$curl->setEnablePersistentConnections(true);
        ApiRequestor::setHttpClient($curl);

    }

    /**
     * @param array $params
     * @param Card $card
     * @param bool $setForShipping if set to true then will add parameters against shipping key and if set to false
     * will add only address key in params
     */
    private function setAddressParams(array &$params, Card $card, bool $setForShipping = false) {

        if(!empty($card->address1)) { // required

            $address = [];

            $address['line1'] = $card->address1;

            if(!empty($card->address2))
                $address['line2'] = $card->address2; // optional

            if(!empty($card->city))
                $address['city'] = $card->city; // optional

            if(!empty($card->country))
                $address['country'] = $card->currency; // optional

            if(!empty($card->postalCode))
                $address['postal_code'] = $card->postalCode; // optional

            if(!empty($card->state))
                $address['state'] = $card->state; // optional

            if($setForShipping) {
                $shipping = [];

                if($card->isNameSet()) { // required
                    $shipping['name'] = $card->getName();

                    if (!empty($card->phone))
                        $shipping['phone'] = $card->phone; // optional

                    $shipping['address'] = $address;
                    $params['shipping'] = $shipping;
                }

            } else {
                $params['address'] = $address;
            }

        }
    }

    private function setParamsForDirectCardCharges(&$params, Card $card) {

        if(!empty($card->address1))
            $params['card']['address_line1'] = $card->address1; // optional Address line 1 (Street address / PO Box / Company name).

        if(!empty($card->address2))
            $params['card']['address_line2'] = $card->address2; // optional Address line 2 (Apartment / Suite / Unit / Building).

        if(!empty($card->city))
            $params['card']['address_city'] = $card->city; // optional City / District / Suburb / Town / Village.

        if(!empty($card->state))
            $params['card']['address_state'] = $card->state; // optional State / County / Province / Region.

        if(!empty($card->postalCode))
            $params['card']['address_zip'] = $card->postalCode; // optional ZIP or postal code.

        if(!empty($card->country))
            $params['card']['address_country'] = $card->country; // optional Billing address country, if provided.
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        $errorCode = 0;

        if($this->verifyCardForDirectCharge($card)) {

            $errorMessage = '';
            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Charge';

            try {

                $_c = array(
                    "card" => array(
                        "number" => $card->cardNumber,
                        "exp_month" => $card->expiryMonth,
                        "exp_year" => $card->expiryYear,
                        "name" => $card->getName()));

                $this->setParamsForDirectCardCharges($_c, $card);

                if (isset($card->cvvCode) && $card->cvvCode > 0)
                    $_c['card']['cvc'] = $card->cvvCode;

                $t = Token::create($_c);
                $t = json_decode(json_encode($t), true);

                $sourceToken = $t['id'];

                $params = array(
                    "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                    "currency" => $card->currency,
                    "metadata" => $card->metadata,
                    "source" => $sourceToken,
                    "description" => $card->general_description
                );

                if(!empty($card->statement_descriptor))
                    $params['statement_descriptor'] = $card->statement_descriptor;

                $this->setAddressParams($params, $card, true);

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                if($card->applicationFee > 0)
                    $params['application_fee_amount'] = $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->applicationFee);

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'] && $chargeArray['captured'] ? true : false;
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);

        }

        return null;
    }

    /**
     * @param Customer $customer
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     * @throws GatewayException
     */
    function chargeWithCustomer(Customer $customer, Card $card, UserPaymentGateway $userPaymentGateway) {

        if(!isset($card->amount) || $card->amount == 0 || $card->amount == null)
            throw new GatewayException('Missing Amount', 50201);

        if(!isset($card->currency) || $card->currency == null)
            throw new GatewayException('Missing Currency Code', 50202);

        if(!isset($customer->token) || $customer->token == null)
            throw new GatewayException('Missing Customer ID', 50203);

        // if(!isset($customer->default_source) || $customer->default_source == null)
        //     throw new GatewayException('Missing Customer Default payment source', 50204);

        $errorMessage = '';
        $errorCode = 0;

        try {

            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Charge';

            $params = array(
                "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                "currency" => $card->currency,
                "customer" => $customer->token,
                //"source" => $customer->default_source, // TODO: verify this
            );

            if(!empty($card->statement_descriptor))
                $params['statement_descriptor'] = $card->statement_descriptor;

            if(isset($card->general_description))
                $params['description'] = $card->general_description;

            if(isset($card->metadata) && count($card->metadata) > 0)
                $params['metadata'] = $card->metadata;

            $this->setAddressParams($params, $card, true);

            $options = null;

            if($this->isStripeConnect)
                $options = array("stripe_account" => $this->stripeUserId);

            if($card->applicationFee > 0)
                $params['application_fee_amount'] = $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->applicationFee);

            $charge = Charge::create($params, $options);

            $chargeArray = json_decode(json_encode($charge), true);

            $transaction->fullResponse = json_encode($chargeArray);
            $transaction->token = $chargeArray['id'];
            $transaction->state = $chargeArray['status'];
            $transaction->status = $chargeArray['paid'];
            $transaction->message = $chargeArray['outcome']['seller_message'];
            $transaction->created_at = $chargeArray['created'];

            return $transaction;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        if(strlen($errorMessage) > 0) {
            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);
        }

        return null;
    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function refund(Transaction $transaction) {

        if(!isset($transaction->token) || $transaction->token == null)
            throw new GatewayException('Token is not set', 50301);

        $errorMessage = '';
        $errorCode = 0;
        $params = array("charge" => $transaction->token);
        $tran = new Transaction();

        if($transaction->isPartial)
            if(isset($transaction->amount) && $transaction->amount != null && $transaction->amount > 0) {
                $params["amount"] = $this->getAmountInLowestPositiveUnitOfCurrency($transaction->currency_code, $transaction->amount);
            } else {
                throw new GatewayException('Amount to refund is not valid', 50302);
            }

        $tran->amount = $transaction->amount;
        $tran->order_id = $transaction->order_id;
        $tran->currency_code = $transaction->currency_code;
        $tran->description = $transaction->description;
        $tran->type = 'Refund';
        $tran->isPartial = $transaction->isPartial;

        if($transaction->description != null)
            $params['reason'] = $transaction->description;

        try {

            $options = null;

            if($this->isStripeConnect)
                $options = array("stripe_account" => $this->stripeUserId);

            $refund = Refund::create($params, $options);
            $refundArray = $refund->toArray();

            $tran->status = true;
            $tran->state = $refundArray['status'];
            $tran->token = $refundArray['id'];
            $tran->fullResponse = json_encode($refund);

            return $tran;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
        $tran->status = false;
        $tran->state = 'Failed';
        $tran->message = $errorMessage . '. code: ' . $errorCode;
        return $tran;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        $errorCode = 0;

        if($this->verifyCardForDirectCharge($card)) {

            $errorMessage = '';
            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Authorize';

            try {

                $_c = array(
                    "card" => array(
                        "number" => $card->cardNumber,
                        "exp_month" => $card->expiryMonth,
                        "exp_year" => $card->expiryYear,
                        "name" => $card->getName()));

                $this->setParamsForDirectCardCharges($_c, $card);

                if (isset($card->cvvCode) && $card->cvvCode > 0)
                    $_c['card']['cvc'] = $card->cvvCode;

                $t = Token::create($_c);
                $t = json_decode(json_encode($t), true);

                $sourceToken = $t['id'];

                $params = array(
                    "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                    "currency" => $card->currency,
                    "metadata" => $card->metadata,
                    "source" => $sourceToken,
                    "capture" => false
                );

                if(isset($card->general_description))
                    $params['description'] = $card->general_description;

                if(!empty($card->statement_descriptor))
                    $params['statement_descriptor'] = $card->statement_descriptor;

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                $this->setAddressParams($params, $card, true);

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'];
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);

        }

        return null;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        $errorCode = 0;

        if($this->verifyCardForAuthorizeWithToken($card)) {

            $errorMessage = '';
            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Authorize';

            try {

                $params = array(
                    "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                    "currency" => $card->currency,
                    "metadata" => $card->metadata,
                    "source" => $card->token,
                    "capture" => false
                );

                if(isset($card->general_description))
                    $params['description'] = $card->general_description;

                if(!empty($card->statement_descriptor))
                    $params['statement_descriptor'] = $card->statement_descriptor;

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                $this->setAddressParams($params, $card, true);

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'];
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);

        }

        return null;
    }

    /**
     * @param Customer $customer
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCustomer(Customer $customer, Card $card, UserPaymentGateway $userPaymentGateway) {

        if(!isset($card->amount) || $card->amount == 0 || $card->amount == null)
            throw new GatewayException('Missing Amount', 50201);

        if(!isset($card->currency) || $card->currency == null)
            throw new GatewayException('Missing Currency Code', 50202);

        if(!isset($customer->token) || $customer->token == null)
            throw new GatewayException('Missing Customer ID', 50203);

        // if(!isset($customer->default_source) || $customer->default_source == null)
        //     throw new GatewayException('Missing Customer Default payment source', 50204);

        $errorMessage = '';
        $errorCode = 0;

        try {

            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Authorize';

            $params = array(
                "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                "currency" => $card->currency,
                "customer" => $customer->token,
                "capture" => false
                //"source" => $customer->default_source, // TODO: verify this
            );

            if(!empty($card->statement_descriptor))
                $params['statement_descriptor'] = $card->statement_descriptor;

            if(isset($card->general_description))
                $params['description'] = $card->general_description;

            if(isset($card->metadata) && count($card->metadata) > 0)
                $params['metadata'] = $card->metadata;

            $options = null;

            if($this->isStripeConnect)
                $options = array("stripe_account" => $this->stripeUserId);

            $this->setAddressParams($params, $card, true);

            $charge = Charge::create($params, $options);

            $chargeArray = json_decode(json_encode($charge), true);

            $transaction->fullResponse = json_encode($chargeArray);
            $transaction->token = $chargeArray['id'];
            $transaction->state = $chargeArray['status'];
            $transaction->status = $chargeArray['paid'];
            $transaction->message = $chargeArray['outcome']['seller_message'];
            $transaction->created_at = $chargeArray['created'];

            return $transaction;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        if(strlen($errorMessage) > 0) {
            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, ($errorCode == null || $errorCode == '') ? 0 : $errorCode);
        }

        return null;

    }

    /**
     * @param Transaction $tran
     * @return Transaction|null
     * @throws GatewayException
     */
    function capture(Transaction $tran) {

        $errorMessage = '';
        $errorCode = 0;

        if(!isset($tran->token) || $tran->token == null)
            throw new GatewayException('Charge ID/Token Missing', 50601);

        if($tran->isPartial)
            if(!isset($tran->amount) || $tran->amount <= 0 || $tran->amount == null)
                throw new GatewayException('Invalid amount', 50602);


        try {

            $transaction = new Transaction();
            $transaction->amount = $tran->amount;
            $transaction->order_id = $tran->order_id;
            $transaction->currency_code = $tran->currency_code;
            $transaction->description = $tran->description;
            $transaction->type = 'Capture';

            $options = null;

            if($this->isStripeConnect)
                $options = array("stripe_account" => $this->stripeUserId);

            /**
             * @var $chargeRetrieved Charge
             */
            $chargeRetrieved = Charge::retrieve($tran->token, $options);

            $chargeArray = array();

            if($tran->isPartial) {
                $params = array(
                    "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($tran->currency_code, $tran->amount)
                    //"source" => $customer->default_source, // TODO: verify this
                );
                $charge = $chargeRetrieved->capture($params);
                $chargeArray = json_decode(json_encode($charge), true);

            } else {

                $charge = $chargeRetrieved->capture();
                $chargeArray = json_decode(json_encode($charge), true);
            }

            $transaction->fullResponse = json_encode($chargeArray);
            $transaction->token = $chargeArray['id'];
            $transaction->state = $chargeArray['status'];
            $transaction->status = $chargeArray['paid'] && $chargeArray['captured'] ? true : false;
            $transaction->message = $chargeArray['outcome']['seller_message'];
            $transaction->created_at = $chargeArray['created'];

            return $transaction;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        if(strlen($errorMessage) > 0) {
            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, ($errorCode == null || $errorCode == '') ? 0 : $errorCode);
        }

        return null;
    }

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    function addAsCustomer(Card $card) {

        if($this->verifyCardForCustomerAdd($card)) {
            
            $errorMessage = '';
            $errorCode = 0;
            $customer = new Customer();
            $params = array();

            if(isset($card->eMail))
                $params['email'] = $card->eMail;

            if(isset($card->general_description))
                $params['description'] = $card->general_description;

            if(isset($card->metadata) && count($card->metadata) > 0)
                $params['metadata'] = $card->metadata;

            try {

                $_c = array(
                    "card" => array(
                        "number" => $card->cardNumber,
                        "exp_month" => $card->expiryMonth,
                        "exp_year" => $card->expiryYear,
                        "name" => $card->getName()));

                $this->setParamsForDirectCardCharges($_c, $card);

                if (isset($card->cvvCode) && $card->cvvCode > 0)
                    $_c['card']['cvc'] = $card->cvvCode;

                $t = Token::create($_c);
                $t = json_decode(json_encode($t), true);

                $params['source'] = $t['id'];

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                $this->setAddressParams($params, $card);

                $result = \Stripe\Customer::create($params, $options);
                $toArray = $result->toArray();

                $customer->token = $toArray['id'];
                $customer->fullResponse = json_encode($toArray);
                $customer->created_at = $toArray['created'];
                $customer->first_name = $card->firstName;
                $customer->last_name = $card->lastName;
                $customer->year = $card->expiryYear;
                $customer->month = $card->expiryMonth;
                $customer->email = $card->eMail;
                $customer->succeeded = true;
                $customer->state = "Success";

                if(key_exists('sources', $toArray))
                    if(key_exists('data', $toArray['sources']))
                        if(count($toArray['sources']['data']) > 0) {

                            if(key_exists('brand', $toArray['sources']['data'][0]))
                                $customer->card_type = $toArray['sources']['data'][0]['brand'];

                            if(key_exists('last4', $toArray['sources']['data'][0]))
                                $customer->last_four_digits = $toArray['sources']['data'][0]['last4'];
                        }

                return $customer;

            } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);
        }

        return null;
    }

    /**
     * @param Card $card
     * @return Customer|null
     * @throws GatewayException
     */
    function addAsCustomerWithToken(Card $card) {
        
        if(empty($card->token))
            throw new GatewayException('Payment Method Token not Set', PaymentGateway::ERROR_CODE_GENERAL);

        $customer = new Customer();
        
        $errorMessage = '';

        try {

            $paymentMethod = PaymentMethod::retrieve($card->token);

            $customerParams = $this->makeParametersForCustomer($card, $paymentMethod);
            $stripeCustomer = \Stripe\Customer::create($customerParams);

            $toArray = $stripeCustomer->toArray();

            $customer->payment_method = $paymentMethod->id;
            $customer->token = $toArray['id'];
            $customer->fullResponse = json_encode($toArray);
            $customer->created_at = $toArray['created'];
            $customer->first_name = $card->firstName;
            $customer->last_name = $card->lastName;
            $customer->year = $paymentMethod->card->exp_year;
            $customer->month = $paymentMethod->card->exp_month;
            $customer->email = $card->eMail;
            $customer->succeeded = true;
            $customer->state = "Success";
            $customer->card_type = $paymentMethod->card->brand;
            $customer->last_four_digits = $paymentMethod->card->last4;
            $customer->three_d_secure_usage = $paymentMethod->card->three_d_secure_usage->supported;

            return $customer;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
        throw new GatewayException($errorMessage, $errorCode);
        

        return null;
    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function cancelAuthorization(Transaction $transaction) {

        if(!isset($transaction->token) || $transaction->token == null)
            throw new GatewayException('Token is not set', 50301);

        $errorMessage = '';
        $errorCode = 0;
        $params = array("charge" => $transaction->token);
        $tran = new Transaction();

        if($transaction->isPartial)
            if(isset($transaction->amount) && $transaction->amount != null && $transaction->amount > 0) {
                $params["amount"] = $this->getAmountInLowestPositiveUnitOfCurrency($transaction->currency_code, $transaction->amount);
            } else {
                throw new GatewayException('Amount to refund is not valid', 50302);
            }

        $tran->amount = $transaction->amount;
        $tran->order_id = $transaction->order_id;
        $tran->currency_code = $transaction->currency_code;
        $tran->description = $transaction->description;
        $tran->type = 'Refund';

        try {

            $options = null;

            if($this->isStripeConnect)
                $options = array("stripe_account" => $this->stripeUserId);

            $refund = Refund::create($params, $options);
            $refundArray = $refund->toArray();

            $tran->status = true;
            $tran->state = $refundArray['status'];
            $tran->token = $refundArray['id'];

            return $tran;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
        $tran->status = false;
        $tran->state = 'Failed';
        $tran->message = $errorMessage . '. code: ' . $errorCode;
        return $tran;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     * @throws GatewayException
     */
    function chargeThroughCardToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        $errorCode = 0;

        if($this->verifyCardForToken($card)) {

            $errorMessage = '';
            $transaction = new Transaction();
            $transaction->amount = $card->amount;
            $transaction->order_id = $card->order_id;
            $transaction->currency_code = $card->currency;
            $transaction->description = $card->general_description;
            $transaction->type = 'Charge';

            try {

                $params = array(
                    "amount" => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
                    "currency" => $card->currency,
                    "metadata" => $card->metadata,
                    "source" => $card->token,
                    "description" => $card->general_description
                );

                if(!empty($card->statement_descriptor))
                    $params['statement_descriptor'] = $card->statement_descriptor;

                $this->setAddressParams($params, $card, true);

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                if($card->applicationFee > 0)
                    $params['application_fee_amount'] = $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->applicationFee);

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'] && $chargeArray['captured'] ? true : false;
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, ($errorCode == null || $errorCode == '') ? 0 : $errorCode);

        }

        return null;
    }

    /**
     * @return array
     */
    function getTerminal() {

        if(empty($this->public_key))
            throw new GatewayException('Publishable key not found', PaymentGateway::ERROR_CODE_GENERAL);

        try {

            $setup_intent = SetupIntent::create(['usage' => 'off_session']);

            return array(
                'is_token' => true,
                'is_redirect' => false,
                'redirect_link' => null,
                'cc_form_name' => 'stripe-add-card',
                'public_key' => $this->public_key,
                'client_secret' => $setup_intent->client_secret,
                'account_id' => ''
            );

        } catch(CardException $e){
                $errorMessage.= $e->getMessage();
                $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
            }
            catch (InvalidRequestException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            }
            catch (AuthenticationException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            }
            catch (ApiConnectionException $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            }
            catch (\Exception $e) {
                $errorMessage .= $e->getMessage();
                $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            }

            $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
            throw new GatewayException($errorMessage, $errorCode);
    }

    /**
     * @param string $code
     * @param GateWay $gateway
     * @return GateWay
     * @throws GatewayException
     */
    function stripeConnectAttachAccount(string $code, GateWay $gateway) {

        $errorMessage = '';
        $errorCode = 0;

        try {

            Stripe::setApiKey($this->MY_Secret_Key);

            $result = OAuth::token(array(
                "grant_type" => "authorization_code",
                "code" => $code,
                "client_secret" => $this->MY_Secret_Key
            ));

            $result = json_encode($result);
            $result = json_decode($result, true);

            for($i = 0; $i < count($gateway->credentials); $i++) {
                if($gateway->credentials[$i]->name == 'stripe_user_id') {
                    $gateway->credentials[$i]->value = $result['stripe_user_id'];

                } elseif($gateway->credentials[$i]->name == 'publishable_key') {
                    $gateway->credentials[$i]->value = $result['stripe_publishable_key'];
                }
            }

            $account = Account::retrieve($result['stripe_user_id']);

            if($account != null){

                $accountToArray = $account->toArray();

                try {
                    $gateway->statement_descriptor = $account->settings->payments->statement_descriptor;
                    } catch(\Exception $e){}

                try {
                    $gateway->country = $account->country;
                } catch(\Exception $e){}
                
                try {
                    $gateway->displayName = $account->settings->dashboard->display_name;
                } catch(\Exception $e){}
                
                try {
                    $gateway->companyName = $account->business_profile->name;
                } catch(\Exception $e){}
            }

            $gateway->isStripeConnect = true;
            return $gateway;

        } catch(CardException $e){
            $errorMessage.= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;
        }
        catch (InvalidRequestException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
        }
        catch (AuthenticationException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
        }
        catch (ApiConnectionException $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
        }
        catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
        }

        $errorCode = $this->getNetworkErrorCode($this->isNetworkError($errorMessage), $errorCode);
        throw new GatewayException($errorMessage, $errorCode);
    }

    /**
     * @param string $payment_intent
     * @param string $payment_intent_client_secret
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     */
    function afterAuthentication(string $payment_intent, string $payment_intent_client_secret, UserPaymentGateway $userPaymentGateway)
    {
        return null;
    }

    /**
     * @param $amount
     * @param string $currency
     * @param string $customer_token
     * @param bool $capture
     * @param UserPaymentGateway $userPaymentGateway
     * @param string $description
     * @return array
     */
    function getTerminalForFrontEndCharge($amount, string $currency, string $customer_token, bool $capture, UserPaymentGateway $userPaymentGateway, string $description = '')
    {
        return [];
    }

    /**
     * @param Customer $customer
     * @param UserPaymentGateway $userPaymentGateway
     * @return mixed
     */
    function updateCustomerPaymentMethod(Customer $customer, UserPaymentGateway $userPaymentGateway)
    {
        return null;
    }

    /**
     * @param UserPaymentGateway $userPaymentGateway
     * @return Account|null
     */
    function getAccount(UserPaymentGateway $userPaymentGateway) {

        if(empty($this->stripeUserId))
            throw new GatewayException('Clients stripe account id not set', PaymentGateway::ERROR_CODE_GENERAL);

        try {

            $account = Account::retrieve($this->stripeUserId);

            if($account != null) {

                $gatewayAccount = new GatewayAccount();

                try {
                    $gateway->statement_descriptor = $account->settings->payments->statement_descriptor;
                } catch(\Exception $e){}

                try {
                    $gateway->country = $account->country;
                } catch(\Exception $e){}
                
                try {
                    $gateway->displayName = $account->settings->dashboard->display_name;
                } catch(\Exception $e){}
                
                try {
                    $gateway->companyName = $account->business_profile->name;
                } catch(\Exception $e){}

                try {
                    $gatewayAccount->id = $account->id;
                } catch(\Exception $e){}

                try {
                    $gatewayAccount->default_currency = $account->default_currency;
                } catch(\Exception $e){}

                try {
                    $gatewayAccount->email = $account->email;
                } catch(\Exception $e){}

                try {
                    $gatewayAccount->timezone = $account->settings->dashboard->timezone;
                } catch(\Exception $e){}

                return $gatewayAccount;
            }

            return null;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
        
    }
}