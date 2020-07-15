<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/25/18
 * Time: 2:57 PM
 */

namespace App\System\PaymentGateway\Stripe3D;


use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Account as GatewayAccount;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PaymentGateway\PG_Generic;
use App\System\PaymentGateway\PGInterface;
use App\System\PaymentGateway\Stripe\StripeSpecific;
use App\UserPaymentGateway;
use Exception;
use Stripe\Account;
use Stripe\ApiRequestor;
use Stripe\Charge;
use Stripe\HttpClient\CurlClient;
use Stripe\OAuth;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\SetupIntent;
use Stripe\Stripe;

class PG_Stripe3D extends PG_Generic implements PGInterface, StripeSpecific {

    use StripeHelpers;

    private $public_key;
    private $secret_key;
    private $userAccountId = -1;
    private $MY_Secret_Key = '';
    private $stripeUserId = '';
    private $isStripeConnect = false;
    private $optionsParam = [];

    /**
     * PG_Stripe3D constructor.
     * @param array|null $credentials
     *
     */
    public function __construct(array $credentials = null) {

        $this->MY_Secret_Key = config('db_const.auth_keys.stripe.secret_key');

        if($credentials != null && is_array($credentials)) {

            $this->userAccountId = $credentials['user_account_id'];

            if(key_exists('stripe_user_id', $credentials)) {

                if(!empty($credentials['stripe_user_id'])) {
                    Stripe::setApiKey($this->MY_Secret_Key);
                    $this->stripeUserId = $credentials['stripe_user_id'];
                    $this->isStripeConnect = true;
                    $this->optionsParam['stripe_account'] = $this->stripeUserId;
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
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function chargeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($this->verifyCardForDirectCharge($card)) {

            try {

                $cardParams = $this->makeParametersFromCard($card);
                $paymentMethod = PaymentMethod::create($cardParams, $this->optionsParam);

                $paymentIntentParams = $this->makeParametersForPaymentIntent($card, $paymentMethod->id);
                $intent = PaymentIntent::create($paymentIntentParams, $this->optionsParam);

                $transaction = $this->getTransactionObject($intent, 'Charge', $card);

                return $transaction;

            } catch (Exception $e) {
                throw $this->handleStripeExceptions($e);
            }

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

            $customer = new Customer();

            try {

//                $setupIntent = SetupIntent::create(['usage' => 'off_session'], $this->optionsParam);

                $cardParams = $this->makeParametersFromCard($card);
                $paymentMethod = PaymentMethod::create($cardParams, $this->optionsParam);

                $customerParams = $this->makeParametersForCustomer($card, $paymentMethod);
                $stripeCustomer = \Stripe\Customer::create($customerParams, $this->optionsParam);

//                SetupIntent::update($setupIntent->id, [
//                    'customer' =>$stripeCustomer->id,
//                    'payment_method' => $paymentMethod->id,
//                ], $this->optionsParam);

                $toArray = $stripeCustomer->toArray();

                $customer->payment_method = $paymentMethod->id;
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

                $customer->card_type = $paymentMethod->card->brand;
                $customer->last_four_digits = $paymentMethod->card->last4;
                $customer->three_d_secure_usage = $paymentMethod->card->three_d_secure_usage->supported;

                return $customer;

            } catch (Exception $e) {
                throw $this->handleStripeExceptions($e);
            }
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

//        if(empty($customer->payment_method))
//            throw new GatewayException('Missing Payment Method ID', 50203);

        try {

            $paymentIntentParams = $this->makeParametersForPaymentIntent($card, $customer->payment_method);
            $paymentIntentParams['customer'] = $customer->token;
            $paymentIntentParams['payment_method_types'] = ['card'];

            $intent = PaymentIntent::create($paymentIntentParams, $this->optionsParam);

            $transaction = $this->getTransactionObject($intent, 'Charge', $card);

            return $transaction;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }

    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function refund(Transaction $transaction) {

        if(!isset($transaction->token) || $transaction->token == null)
            throw new GatewayException('Token is not set', 50301);

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

            $refund = Refund::create($params, $this->optionsParam);
            $refundArray = $refund->toArray();

            $tran->status = true;
            $tran->state = $refundArray['status'];
            $tran->token = $refundArray['id'];
            $tran->fullResponse = json_encode($refund);

            return $tran;

        } catch (Exception $e) {
            $op = $this->handleStripeExceptions($e);
        }

        $tran->status = false;
        $tran->state = 'Failed';
        $tran->message = $op->getMessage() . '. code: ' . $op->getCode();
        return $tran;
    }

    /**
     * Authorize with card
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($this->verifyCardForDirectCharge($card)) {

            try {

                $cardParams = $this->makeParametersFromCard($card);
                $paymentMethod = PaymentMethod::create($cardParams, $this->optionsParam);

                $paymentIntentParams = $this->makeParametersForPaymentIntent($card, $paymentMethod->id);
                $paymentIntentParams['capture_method'] = 'manual';

                $intent = PaymentIntent::create($paymentIntentParams, $this->optionsParam);

                $transaction = $this->getTransactionObject($intent, 'Authorize', $card);

                return $transaction;

            } catch (Exception $e) {
                throw $this->handleStripeExceptions($e);
            }
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

//        if(empty($customer->payment_method))
//            throw new GatewayException('Missing Payment Method ID', 50203);

        try {

            $paymentIntentParams = $this->makeParametersForPaymentIntent($card, $customer->payment_method);
            $paymentIntentParams['customer'] = $customer->token;
            $paymentIntentParams['payment_method_types'] = ['card'];
            $paymentIntentParams['capture_method'] = 'manual';
//            $paymentIntentParams['setup_future_usage'] = 'off_session';
//            $paymentIntentParams['off_session'] = false; // TODO: test with normal and 3ds card.
//            $paymentIntentParams['confirm'] = false;

            $intent = PaymentIntent::create($paymentIntentParams, $this->optionsParam);

            $transaction = $this->getTransactionObject($intent, 'Authorize', $card);

            return $transaction;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param Transaction $tran
     * @return Transaction|null
     * @throws GatewayException
     */
    function capture(Transaction $tran) {

        if(empty($tran->paymentIntentId))
            throw new GatewayException('Authorized Payment Intent ID Missing', 50601);

        if($tran->isPartial)
            if(empty($tran->amount) || $tran->amount <= 0)
                throw new GatewayException('Invalid amount', 50602);


        try {

            $paymentIntent = PaymentIntent::retrieve($tran->paymentIntentId, $this->optionsParam);

            $cur = $paymentIntent->currency;

            if($this->isZeroDecimalCurrency($cur))
                $capturable = $paymentIntent->amount_capturable;
            else
                $capturable = $paymentIntent->amount_capturable / 100.0;

            $captureParam = [];

            if($tran->isPartial) {

                if ($tran->amount > $capturable) {
                    $msg = 'Cannot charge ' . $cur . ' ' . $tran->amount . ' because its more than capturable amount ' . $cur . ' ' . $capturable;
                    throw new GatewayException($msg, 50603);
                }

                $amountToBeCaptured = $tran->amount;
                $captureParam['amount_to_capture'] = $this->getAmountInLowestPositiveUnitOfCurrency($cur, $tran->amount);

            } else {
                $amountToBeCaptured = $capturable;
            }

            $paymentIntent = $paymentIntent->capture($captureParam, $this->optionsParam);

            $card = new Card();
            $card->amount = $amountToBeCaptured;
            $card->order_id = $tran->order_id;
            $card->currency = $cur;
            $card->general_description = '';

            $transaction = $this->getTransactionObject($paymentIntent, 'Capture', $card);
            $transaction->isPartial = $tran->isPartial;

            return $transaction;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param Transaction $transaction
     * @return Transaction|null
     * @throws GatewayException
     */
    function cancelAuthorization(Transaction $transaction) {

        if(empty($transaction->paymentIntentId))
            throw new GatewayException('Payment Intent ID Missing', 50301);

        $tran = new Transaction();

        $tran->amount = $transaction->amount;
        $tran->order_id = $transaction->order_id;
        $tran->currency_code = $transaction->currency_code;
        $tran->description = $transaction->description;
        $tran->type = 'Refund';

        try {

            $paymentIntent = PaymentIntent::retrieve($transaction->paymentIntentId, $this->optionsParam);
            $paymentIntent = $paymentIntent->cancel(null, $this->optionsParam);

            $tran->fullResponse = json_encode($paymentIntent);
            $obj = json_decode($tran->fullResponse, true);

            if(key_exists('charges', $obj)) {
                if($obj['charges']['object'] == 'list' && $obj['charges']['total_count'] >= 1) {

                    $data = $obj['charges']['data'][0];

                    $tran->token = $data['id'];
                    $tran->message = $data['outcome']['seller_message'];

                }
            }

            $tran->state = $paymentIntent->status;
            $tran->status = $tran->state == 'canceled';
            $tran->paymentIntentId = $paymentIntent->id;

            return $tran;

        } catch (Exception $e) {
            $op = $this->handleStripeExceptions($e);
        }

        $tran->status = false;
        $tran->state = 'Failed';
        $tran->message = $op->getMessage() . '. code: ' . $op->getCode();
        return $tran;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     * @throws GatewayException
     * TODO: Need to complete this function.
     */
    function chargeThroughCardToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($this->verifyCardForToken($card)) {

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
                    $params["statement_descriptor"] = $card->statement_descriptor;

                $options = null;

                if($this->isStripeConnect)
                    $options = array("stripe_account" => $this->stripeUserId);

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'] && $chargeArray['captured'] ? true : false;
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch (Exception $e) {
                throw $this->handleStripeExceptions($e);
            }
        }

        return null;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     * TODO: Need to complete this function.
     */
    function authorizeWithToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($this->verifyCardForAuthorizeWithToken($card)) {

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

                $charge = Charge::create($params, $options);

                $chargeArray = $charge->toArray();

                $transaction->fullResponse = json_encode($chargeArray);
                $transaction->token = $chargeArray['id'];
                $transaction->state = $chargeArray['status'];
                $transaction->status = $chargeArray['paid'];
                $transaction->message = $chargeArray['outcome']['seller_message'];
                $transaction->created_at = $chargeArray['created'];

                return $transaction;

            } catch (Exception $e) {
                throw $this->handleStripeExceptions($e);
            }
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

        try {

            $paymentMethod = PaymentMethod::retrieve($card->token, $this->optionsParam);

            $customerParams = $this->makeParametersForCustomer($card, $paymentMethod);
            $stripeCustomer = \Stripe\Customer::create($customerParams, $this->optionsParam);

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

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @return array
     * @throws GatewayException
     */
    function getTerminal() {

        if(empty($this->public_key))
            throw new GatewayException('Publishable key not found', PaymentGateway::ERROR_CODE_GENERAL);

        try {

            $setup_intent = SetupIntent::create(['usage' => 'off_session'], $this->optionsParam);

            return array(
                'is_token' => true,
                'is_redirect' => false,
                'redirect_link' => null,
                'cc_form_name' => 'stripe-add-card',
                'public_key' => $this->public_key,
                'client_secret' => $setup_intent->client_secret,
                'account_id' => $this->stripeUserId
            );

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param string $code
     * @param GateWay $gateway
     * @return GateWay
     * @throws GatewayException
     */
    function stripeConnectAttachAccount(string $code, GateWay $gateway) {

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

            if($account != null) {

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

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param string $payment_intent
     * @param string $payment_intent_client_secret
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function afterAuthentication(string $payment_intent, string $payment_intent_client_secret, UserPaymentGateway $userPaymentGateway) {

        try {

            $intent = PaymentIntent::retrieve($payment_intent, $this->optionsParam);

            $card = new Card();
            if($this->isZeroDecimalCurrency($intent->currency))
                $card->amount = $intent->amount / 100;
            else
                $card->amount = $intent->amount;
            $card->currency = $intent->currency;

            $transaction = $this->getTransactionObject($intent, 'Charge', $card);

            return $transaction;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param $amount
     * @param string $currency
     * @param string $customer_token
     * @param bool $capture
     * @param UserPaymentGateway $userPaymentGateway
     * @param string $description
     * @return array
     * @throws GatewayException
     */
    function getTerminalForFrontEndCharge($amount, string $currency, string $customer_token, bool $capture, UserPaymentGateway $userPaymentGateway, string $description = '')
    {
        if(empty($this->public_key))
            throw new GatewayException('Publishable key not found', PaymentGateway::ERROR_CODE_GENERAL);

        try {

            $params = [
                'amount' => $this->getAmountInLowestPositiveUnitOfCurrency($currency, $amount),
                'currency' => $currency,
                'setup_future_usage' => 'off_session'
            ];

            if(!empty($description))
                $params['description'] = $description;

            if(!empty($customer_token))
                $params['customer'] = $customer_token;

            if($capture)
                $params['capture_method'] = 'automatic';
            else
                $params['capture_method'] = 'manual';

            $intent = PaymentIntent::create($params, $this->optionsParam);

            return array(
                'is-token' => true,
                'is-redirect' => false,
                'redirect-link' => null,
                'cc-form-name' => 'v2.guest.checkout.checkout',
                'public-key' => $this->public_key,
                'client_secret' => $intent->client_secret,
                'account_id' => $this->stripeUserId,
                'payment_intent_id' => $intent->id
            );

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
    }

    /**
     * @param Customer $customer
     * @param UserPaymentGateway $userPaymentGateway
     * @return mixed
     * @throws GatewayException
     */
    function updateCustomerPaymentMethod(Customer $customer, UserPaymentGateway $userPaymentGateway) {

        try {

            $paymentMethod = PaymentMethod::retrieve($customer->payment_method, $this->optionsParam);
            $paymentMethod->attach(['customer' => $customer->token], $this->optionsParam);

            $stripeCustomer = \Stripe\Customer::retrieve($customer->token, $this->optionsParam);

            $toArray = $stripeCustomer->toArray();

            $customer->payment_method = $paymentMethod->id;
            $customer->token = $toArray['id'];
            $customer->fullResponse = json_encode($toArray);
            $customer->created_at = $toArray['created'];
            $customer->year = $paymentMethod->card->exp_year;
            $customer->month = $paymentMethod->card->exp_month;
//            $customer->email = $card->eMail;
            $customer->succeeded = true;
            $customer->state = "Success";

            $customer->card_type = $paymentMethod->card->brand;
            $customer->last_four_digits = $paymentMethod->card->last4;
            $customer->three_d_secure_usage = $paymentMethod->card->three_d_secure_usage->supported;

            return $customer;

        } catch (Exception $e) {
            throw $this->handleStripeExceptions($e);
        }
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