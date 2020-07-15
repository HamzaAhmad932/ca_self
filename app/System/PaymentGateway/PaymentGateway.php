<?php

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/24/18
 * Time: 12:01 PM
 */

namespace App\System\PaymentGateway;

use App\PaymentGatewayForm;
use App\PaymentGatewayParent;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\Stripe\PG_Stripe;
use App\System\PaymentGateway\Stripe\StripeSpecific;
use App\System\PaymentGateway\Stripe3D\PG_Stripe3D;
use App\UserPaymentGateway;

class PaymentGateway {

    const ERROR_CODE_GENERAL = 10000;
    const CODE_NETWORK_ERROR_RE_TRY_ABLE = 10001;
    const ERROR_CODE_CARD = 10002;
    const ERROR_CODE_INVALID_REQUEST = 10003;
    const ERROR_CODE_AUTHENTICATION = 1004;
    const ERROR_CODE_API_CONNECTION = 1005;
    const ERROR_CODE_3D_SECURE = 10006;
    const ERROR_CODE_RATE_LIMIT = 10007;
    const ERROR_CODE_API_ERROR = 10008;


    public function __construct() {
    }

    /**
     * @param array $credentials
     * @return PGInterface
     * @throws GatewayException
     */
    private function makeClass(array $credentials) {

        $classToLoad = config('db_const.'.$credentials['backend_name'].'.backend_name');

        if($classToLoad == null)
            throw new GatewayException('Class ' . $credentials['backend_name'] . ' not found, Check if class exists or cache is recreated', 50003);

        return app()->make($classToLoad, ['credentials'=>$credentials['credentials']]);
    }

    /**
     * @param string $form_id
     * @return ParentGatewayInterface
     * @throws GatewayException
     */
    private function makeClassForParent(string $form_id) {

        $classToLoad = config('db_const.'.$form_id.'.backend_name');

        if($classToLoad == null)
            throw new GatewayException('Class ' . $form_id . ' not found, Check if class exists or cache is recreated', 50004);

        return resolve($classToLoad);
    }

    /**
     * @param UserPaymentGateway $userPaymentGateway
     * @return array
     * @throws GatewayException
     */
    private function getCredentials(UserPaymentGateway $userPaymentGateway) {

        $paymentGatewayForm = PaymentGatewayForm::where('id', $userPaymentGateway->payment_gateway_form_id)->get();

        if(count($paymentGatewayForm) > 0) {

            $paymentGatewayParent = PaymentGatewayParent::where('id', $paymentGatewayForm[0]->payment_gateway_parent_id)->get();

            $info = array('credentials' => ['user_account_id' => $userPaymentGateway->user_account_id]);

            if(count($paymentGatewayParent) > 0) {
                // use parent's credentials

                $info['backend_name'] = $paymentGatewayParent[0]->backend_name;
                $info['credentials'] = json_decode($paymentGatewayParent[0]->credentials, true);

                return $info;

            } else {

                $gateway = new GateWay($userPaymentGateway->gateway);

                $info['backend_name'] = $paymentGatewayForm[0]->backend_name;

                if(count($gateway->credentials) > 0) {

                    /**
                     * @var $rec CredentialFormField
                     */
                    foreach ($gateway->credentials as $rec)
                        $info['credentials'][$rec->name] = $rec->value;

                } else {
                    throw new GatewayException('Please add credentials for ' . $gateway->name, 50002);
                }

                return $info;
            }
        }
        else
            throw new GatewayException('Gateway Form not found', 50001);
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function chargeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($card == null)
            throw new GatewayException('Card object null', 50201);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50202);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->chargeWithCard($card, $userPaymentGateway);

        return null;

    }

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function chargeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway) {

        if($cardForOtherInformation == null)
            throw new GatewayException('Card object null', 50301);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50302);

        if($customer == null)
            throw new GatewayException('Customer Object Null', 50303);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($cardForOtherInformation, $userPaymentGateway);

        if($parent != null)
            return $parent->chargeWithCustomer($customer, $cardForOtherInformation, $userPaymentGateway);

        return null;

    }

    /**
     * @param Transaction $transaction
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function refund(Transaction $transaction, UserPaymentGateway $userPaymentGateway) {

        if($transaction == null)
            throw new GatewayException('Transaction Object null', 50401);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->refund($transaction);

        return null;

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Customer|null
     * @throws GatewayException
     */
    public function addAsCustomer(Card $card, UserPaymentGateway $userPaymentGateway) {
        if($card == null)
            throw new GatewayException('Card object null', 50501);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50502);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->addAsCustomer($card);

        return null;

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Customer|null
     * @throws GatewayException
     */
    public function addAsCustomerWithToken(Card $card, UserPaymentGateway $userPaymentGateway) {
        if($card == null)
            throw new GatewayException('Card object null', 50501);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50502);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->addAsCustomerWithToken($card);

        return null;

    }

    /**
     * @param Transaction $transaction
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function cancelAuthorization(Transaction $transaction, UserPaymentGateway $userPaymentGateway) {

        if($transaction == null)
            throw new GatewayException('Transaction Object null', 50601);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50602);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->cancelAuthorization($transaction);

        return null;

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCard(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($card == null)
            throw new GatewayException('Card object null', 50401);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50402);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->authorizeWithCard($card, $userPaymentGateway);

        return null;

    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($card == null)
            throw new GatewayException('Card object null', 50401);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50402);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->authorizeWithToken($card, $userPaymentGateway);

        return null;

    }

    /**
     * @param Customer $customer
     * @param Card $cardForOtherInformation
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function authorizeWithCustomer(Customer $customer, Card $cardForOtherInformation, UserPaymentGateway $userPaymentGateway) {

        if($cardForOtherInformation == null)
            throw new GatewayException('Card object null', 50401);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50402);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($cardForOtherInformation, $userPaymentGateway);

        if($parent != null)
            return $parent->authorizeWithCustomer($customer, $cardForOtherInformation, $userPaymentGateway);

        return null;

    }

    /**
     * @param Transaction $transaction
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    public function capture(Transaction $transaction, UserPaymentGateway $userPaymentGateway) {

        if($transaction == null)
            throw new GatewayException('Transaction Object null', 50201);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway null', 50202);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->capture($transaction);

        return null;
    }

    /**
     * @param string $backend_name
     * @param array $credentials
     * @return array of GateWay
     *
     * Provide form_id and form_data
     * form_id: it points to config file stored in config/db_const/
     * @throws GatewayException
     */
    public function listAllGateways(string $backend_name, array $credentials) {

        if(!isset($backend_name) || $backend_name == null)
            throw new GatewayException('Backend Name Missing ', 50601);

        $parent = $this->makeClassForParent($backend_name);

        if($parent != null)
            return $parent->listAllGateways($backend_name, $credentials);
        else
            return [];
    }

    /**
     * @param string $form_id
     * @param array $form_data
     * @return array|mixed
     * @throws GatewayException
     */
    public function getAddedGateways(string $form_id, array $form_data) {

        if(!isset($form_id) || $form_id == null)
            throw new GatewayException('Form Id Missing ', 50701);

        $parent = $this->makeClassForParent($form_id);

        if($parent != null)
            return $parent->getAddedGateways($form_id, $form_data);
        else
            return [];
    }

    /**
     * @param string $form_id as backend name of parent
     * @param array $credentials Parent Credentials
     * @param GateWay $gateway
     * @return array|null
     * @throws GatewayException
     */
    public function addGatewayOnParentServer(string $form_id, array $credentials, GateWay $gateway) {

        if(!isset($form_id) || $form_id == null)
            throw new GatewayException('Form Id Missing ', 50801);

        $parent = $this->makeClassForParent($form_id);

        if($parent != null)
            return $parent->addGatewayOnParentServer($form_id, $credentials, $gateway);
        else
            return [];

    }

    /**
     * @param string $form_id
     * @param array $form_data
     * @param GateWay $gateway Stored in db as json
     * @return array|null
     * @throws GatewayException
     */
    public function removeGatewayOnParentServer(string $form_id, array $form_data, GateWay $gateway) {

        if(!isset($form_id) || $form_id == null)
            throw new GatewayException('Form Id Missing ', 50901);

        $parent = $this->makeClassForParent($form_id);

        if($parent != null)
            return $parent->removeGatewayOnParentServer($form_id, $form_data, $gateway);
        else
            return [];
    }

    /**
     * @param string $form_id
     * @param array $form_data
     * @param GateWay $gateway Stored in db as json
     * @return GateWay|null
     * @throws GatewayException
     */
    public function updateGatewayOnParentServer(string $form_id, array $form_data, GateWay $gateway) {

        if(!isset($form_id) || $form_id == null)
            throw new GatewayException('Form Id Missing ', 50901);

        $parent = $this->makeClassForParent($form_id);

        if($parent != null)
            return $parent->updateGatewayOnParentServer($form_id, $form_data, $gateway);
        else
            return null;

    }

    /**
     * @param UserPaymentGateway $userPaymentGateway
     * @return array
     * @throws GatewayException
     */
    function getTerminal(UserPaymentGateway $userPaymentGateway) {

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50202);

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent PGInterface
         */
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->getTerminal();

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
     * @throws GatewayException
     */
    function getTerminalForFrontEndCharge($amount, string $currency, string $customer_token, bool $capture, UserPaymentGateway $userPaymentGateway, string $description = '')
    {
        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50202);

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent PGInterface
         */
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->getTerminalForFrontEndCharge($amount, $currency, $customer_token, $capture, $userPaymentGateway, $description);

        return null;
    }

    /**
     * @param Card $card
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction
     * @throws GatewayException
     */
    function chargeThroughCardToken(Card $card, UserPaymentGateway $userPaymentGateway) {

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50202);

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent PGInterface
         */
        $parent = $this->makeClass($credentials);

        $this->set_user_default_statement_descriptor($card, $userPaymentGateway);

        if($parent != null)
            return $parent->chargeThroughCardToken($card, $userPaymentGateway);

        return null;
    }

    /**
     * @param string $code
     * @param UserPaymentGateway $userPaymentGateway
     * @param GateWay $gateway initial gateway object stored in database. This function will update this object's Credentials
     * @return GateWay
     * @throws GatewayException
     */
    function stripeConnectAttachAccount(string $code, UserPaymentGateway $userPaymentGateway, GateWay $gateway) {

        if($code == null || $code == '')
            throw new GatewayException('Invalid Stripe Connect Authorization code', 50201);

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 50202);

        if($gateway == null)
            throw new GatewayException('Gateway Object is null', 50203);

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent StripeSpecific
         */
        $parent = $this->makeClass($credentials);

        if(!($parent instanceof PG_Stripe) && !($parent instanceof PG_Stripe3D))
            throw new GatewayException("Stripe-Connect feature is only for CA implementation of Stripe", 50204);

        if($parent != null)
            return $parent->stripeConnectAttachAccount($code, $gateway);

        throw new GatewayException('Gateway class not found, Parent = null', 50205);

    }

    /**
     * @param string $payment_intent
     * @param string $payment_intent_client_secret
     * @param UserPaymentGateway $userPaymentGateway
     * @return Transaction|null
     * @throws GatewayException
     */
    function afterAuthentication(string $payment_intent, string $payment_intent_client_secret, UserPaymentGateway $userPaymentGateway)
    {
        if(empty($payment_intent))
            throw new GatewayException('Payment Intent Id not found', 50901);

        if(empty($payment_intent_client_secret))
            throw new GatewayException('Payment Intent Client Secret not found', 50902);

        if($userPaymentGateway == null)
            throw new GatewayException('Gateway Object is null', 50903);

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent StripeSpecific
         */
        $parent = $this->makeClass($credentials);

        if(!($parent instanceof PG_Stripe) && !($parent instanceof PG_Stripe3D))
            throw new GatewayException("AfterAuthentication feature is only for CA implementation of Stripe", 50204);

        if($parent != null)
            return $parent->afterAuthentication($payment_intent, $payment_intent_client_secret, $userPaymentGateway);

        throw new GatewayException('Gateway class not found, Parent = null', 50905);

    }

    /**
     * @param Customer $customer
     * @param UserPaymentGateway $userPaymentGateway
     * @return mixed
     * @throws GatewayException
     */
    public function updateCustomerPaymentMethod(Customer $customer, UserPaymentGateway $userPaymentGateway)
    {

        $credentials = $this->getCredentials($userPaymentGateway);

        /**
         * @var $parent PGInterface
         */
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->updateCustomerPaymentMethod($customer, $userPaymentGateway);

        return null;
    }

    public function isCountry3dsSupported(string $country) {

        $list = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT',
            'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'UK', 'IS', 'LI', 'NO'];

        return in_array($country, $list);
    }

    private function set_user_default_statement_descriptor(Card &$card, UserPaymentGateway $userPaymentGateway) {
        $gatewayObj = new GateWay($userPaymentGateway->gateway);
        /**
         * Here we are assigning null to statement_descriptor because this user had set default statement descriptor on
         * his/her account, by setting to null next classes will not use card->statement_descriptor.
         */
        if(!empty($gatewayObj->statement_descriptor))
            $card->statement_descriptor = null;
    }

    /**
     * @param UserPaymentGateway $userPaymentGateway
     * @return Account|null
     */
    function getAccount(UserPaymentGateway $userPaymentGateway) {

        if($userPaymentGateway == null)
            throw new GatewayException('User Payment Gateway object null', 90101);

        $credentials = $this->getCredentials($userPaymentGateway);
        $parent = $this->makeClass($credentials);

        if($parent != null)
            return $parent->getAccount($userPaymentGateway);

        return null;

    }

    /**
     * This function is used to check if gateway account support application fee.
     * It is checked by a known list of countries.
     *
     * @param UserPaymentGateway $userPaymentGateway
     * @return bool
     */
    function isSupportedForApplicationFee(UserPaymentGateway $userPaymentGateway) {

        /**
         * @var $paymentGatewayForm PaymentGatewayForm
         */
        $paymentGatewayForm = $userPaymentGateway->payment_gateway_form;

        if(!empty($paymentGatewayForm)) {
            if($paymentGatewayForm->backend_name == 'pg_form_stripe_connect_3ds_temp') {

                $supportedCountries = ['Australia', 'Austria', 'Belgium', 'Canada', 'Denmark', 'Estonia','Finland',
                    'France', 'Germany', 'Greece', 'Hong Kong', 'Ireland', 'Italy', 'Japan', 'Latvia', 'Lithuania',
                    'Luxembourg', 'Malaysia', 'Mexico', 'Netherlands', 'New Zealand', 'Norway', 'Poland', 'Portugal',
                    'Singapore', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'United Kingdom', 'United States'];

                $gateway = $userPaymentGateway->getGatewayObject();

                if(in_array(get_country_name_full_by_code($gateway->country), $supportedCountries))
                    return true;
            }
        }

        return false;
    }

}