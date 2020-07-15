<?php

namespace App\Repositories\PaymentGateways;

use App\Http\Controllers\client\PmsIntegrationController;
use App\Repositories\Bookings\Bookings;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\PaymentGateway;
use App\UserAccount;
use App\UserSettingsBridge;
use Exception;
use App\BookingInfo;
use App\PropertyInfo;
use App\PaymentGatewayForm;
use App\UserPaymentGateway;
use Illuminate\Support\Facades\Log;
use App\Events\GatewayAddEvent;

class PaymentGateways
{


    const WasRedirectedForPaymentGateway = 'p-g-redirect';
    const RedirectURL = 'redirectUrl';
    const CustomPropertyID = 'customPropertyId';
    const StripeConnectMsg = 'stripeConnectMsg';
    const CsrfSent = 'csrf_sent';
    const PaymentGatewayID = 'payment_gateway_id';
    const MasterSettingMsg = 'masterSettingMsg';
    const UserPaymentGatewayID = 'user_payment_gateway_id';
    const StripeConnectPaymentGatewayID = 'stripe-id';

    public function all_payment_gateways_list()
    {

        $payment_gateways = PaymentGatewayForm::all();

        return $payment_gateways;

    }


    /**
     * Returns Property Payment Gateway by checking Settings.
     * @param BookingInfo $bookingInfo
     * @return UserPaymentGateway|null
     */
    public function getPropertyPaymentGatewayFromBooking(BookingInfo $bookingInfo) {
        return $this->getPropertyPaymentGatewayFromProperty($bookingInfo->property_info);
    }

    /**
     * Returns Property Payment Gateway by checking Settings
     * @param PropertyInfo $propertyInfo
     * @return UserPaymentGateway|null
     */
    public function getPropertyPaymentGatewayFromProperty(PropertyInfo $propertyInfo) {
    	try {
    		return UserPaymentGateway::where('user_account_id', $propertyInfo->user_account_id)
                ->where('property_info_id', $propertyInfo->use_pg_settings == 0 ? 0 :  $propertyInfo->id)->first();
    	} catch (Exception $e) {
    		return null;
    	}
    }

    /**
     * @param BookingInfo $bookingInfo
     * @param Card $card
     * @param string $class
     */
    public static function addMetadataInformation(BookingInfo $bookingInfo, Card &$card, string $class) {
        try {

//            $card->metadata = array(
//                'Client ID' => $bookingInfo->user_account->id,
//                'Client Name' => $bookingInfo->user_account->name,
//                'PMS Name' => $bookingInfo->pms_form->name,
//                'PMS Booking ID' => $bookingInfo->pms_booking_id,
//                'PMS Booking Channel Code' => $bookingInfo->channel_code,
//                'PMS Property Name' => $bookingInfo->property_info->name,
//                'PMS Property ID' => $bookingInfo->property_info->pms_property_id,
//                'Guest First Name' => $card->firstName,
//                'Guest Last Name' => $card->lastName
//            );

            $card->metadata = array(
                'Booking ID' => $bookingInfo->pms_booking_id,
                'First Name' => $card->firstName,
                'Last Name' => $card->lastName
            );

            $card->statement_descriptor = 'B ID ' . $bookingInfo->pms_booking_id;

            $gFullName = $bookingInfo->guest_name != null ? $bookingInfo->guest_name : '';
            $gFullName .= strlen($gFullName) > 0 ? ' ' : '';
            $gFullName .= $bookingInfo->guest_last_name != null ? $bookingInfo->guest_last_name : '';

            $card->general_description = $gFullName . ' Booking ID ' . $bookingInfo->pms_booking_id;

            $card->address1 = $bookingInfo->guest_address;
            $card->setCountry($bookingInfo->guest_country);
            $card->postalCode = $bookingInfo->guest_post_code;
            $card->phone = empty($bookingInfo->guest_phone) ? $bookingInfo->guestMobile : $bookingInfo->guest_phone;
            if($card->phone != null)
                $card->phone = str_replace(' ', '', $card->phone);

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>$class, 'Stack'=>$e->getTraceAsString()]);
        }
    }

    /**
     * @param UserAccount $userAccount
     * @param $propertyInfoId
     * @return array|null
     */
    static function getLocalPropertyPaymentGatewayWithKeys(UserAccount $userAccount, $propertyInfoId){
       return PaymentGateways::getPaymentGatewayWithKeysAfterParamConstraintsCheck($userAccount, 'local', null, $propertyInfoId);
    }

    /**
     * @param UserAccount $userAccount
     * @return array|null
     */
    static function getGlobalPropertyPaymentGatewayWithKeys(UserAccount $userAccount){
        return PaymentGateways::getPaymentGatewayWithKeysAfterParamConstraintsCheck($userAccount, 'global', null, 0);
    }

    /**
     * @param UserAccount $userAccount
     * @param $paymentGatewayFormId
     * @return array|null
     */
    static function getPaymentGatewayWithKeys(UserAccount $userAccount, $paymentGatewayFormId, $propertyInfoId){
        return PaymentGateways::getPaymentGatewayWithKeysAfterParamConstraintsCheck($userAccount, 'paymentGatewayFormByIdWithUserKeys', $paymentGatewayFormId, $propertyInfoId);
    }

    /**
     * @param UserAccount $userAccount
     * @param $paymentGatewayFormId
     * @return array|null
     */
    static function getPaymentGatewayWithOutKeys(UserAccount $userAccount, $paymentGatewayFormId){
        return PaymentGateways::getPaymentGatewayWithKeysAfterParamConstraintsCheck($userAccount, 'paymentGatewayFormByIdWithOutUserKeys', $paymentGatewayFormId,0);
    }
    /**
     * @param UserAccount $userAccount
     * @param null $paymentGatewayFormId
     * @param int $propertyInfoId
     * @param $action | global | local | paymentGatewayFormByIdWithUserKeys | paymentGatewayFormByIdWithOutUserKeys
     * @return array|null
     */
    static function getPaymentGatewayWithKeysAfterParamConstraintsCheck(UserAccount $userAccount, $action, $paymentGatewayFormId = null, $propertyInfoId = 0){

        /**
         * @var $userPaymentGateway UserPaymentGateway
         */
        switch ($action){
            case 'global':
            case 'local':
                $userPaymentGateway = $userAccount->user_payment_gateways->where('property_info_id', $propertyInfoId)->first();
                break;

            case 'paymentGatewayFormByIdWithUserKeys':
                $userPaymentGateway = $userAccount->user_payment_gateways->where('property_info_id', $propertyInfoId)->where('payment_gateway_form_id', $paymentGatewayFormId)->first();
                break;
            case 'paymentGatewayFormByIdWithOutUserKeys':
                $userPaymentGateway = null;
                break;
        }

        if (($paymentGatewayFormId == null) && ($userPaymentGateway == null))
            return null;

        $paymentGateway = PaymentGatewayForm::where('id', (($userPaymentGateway != null) ? $userPaymentGateway->payment_gateway_form_id  : $paymentGatewayFormId))->where('status',1)->first();
        if($paymentGateway == null)
            return null;

        $userGatewayStatus = 0;
        $userGatewayIsVerified = 0;

        if(is_null($userPaymentGateway)) {
            $form = $paymentGateway->gateway_form;
        } else {
            $form = $userPaymentGateway->gateway;
            $userGatewayStatus = $userPaymentGateway->status;
            $userGatewayIsVerified = $userPaymentGateway->is_verified;
        }

        $gateWay = new GateWay($form); //Pass form string to gateway class to Entertain

        for ($i = 0; $i < count($gateWay->credentials); $i++) {
            if ($gateWay->credentials[$i]->name == 'stripe_user_id' && $gateWay->credentials[$i]->type == CredentialFormField::TYPE_BUTTON) {

                if(!empty($gateWay->credentials[$i]->value) && $userGatewayIsVerified == 1)
                    $gateWay->credentials[$i]->label = 'Connected To Stripe';

                if(config('app.env') == 'production' && config('app.url') == 'https://app.chargeautomation.com') {
                    $oldRouteToSearch = 'https://app.chargeautomation.com/client/pmsintegration';
                    $gateWay->credentials[$i]->url = str_replace($oldRouteToSearch, route('getResponseFromStripeConnectAndRedirect'),$gateWay->credentials[$i]->url);

                } elseif(config('app.env') == 'production' && config('app.url') == 'https://testapptor1a.chargeautomation.com') {
                    $oldRouteToSearch = 'https://testapptor1a.chargeautomation.com/client/pmsintegration';
                    $gateWay->credentials[$i]->url = str_replace($oldRouteToSearch, route('getResponseFromStripeConnectAndRedirect'),$gateWay->credentials[$i]->url);

                }  elseif(config('app.env') == 'production' && config('app.url') == 'https://master.chargeautomation.com') {
                    $oldRouteToSearch = 'https://testapptor1a.chargeautomation.com/client/pmsintegration';
                    $gateWay->credentials[$i]->url = str_replace($oldRouteToSearch, route('getResponseFromStripeConnectAndRedirect'),$gateWay->credentials[$i]->url);

                } elseif(config('app.url') == 'http://localhost:8000') {
                    $oldRouteToSearch = 'http://localhost:8000/client/pmsintegration';
                    $gateWay->credentials[$i]->url = str_replace($oldRouteToSearch, route('getResponseFromStripeConnectAndRedirect'),$gateWay->credentials[$i]->url);
                }

                break;
            }
        }
        
        $stripeAccountName = '';
        if(!empty($gateWay->companyName))
            $stripeAccountName = $gateWay->companyName;
        elseif(!empty($gateWay->displayName))
            $stripeAccountName = $gateWay->displayName;
        
        return [
            'paymentGatewayFormId' => $paymentGateway->id,
            'name' => $paymentGateway->name,
            'logo' => asset('storage/uploads/payment_gateway_logos/').'/'.$paymentGateway->logo,
            'credentials' => $gateWay->credentials,
            'userGatewayStatus' => $userGatewayStatus,
            'userGatewayIsVerified' => $userGatewayIsVerified,
            'stripe_account_name' => $stripeAccountName
            ]; //get credentials from gateway class to draw Form
    }

    public function getTestCard($userPaymentGatewaysFormId , $payment_gateway_id, $user_account_id){

        $card = new Card();
        $card->order_id   = time();
        $card->cardNumber = config('db_const.auth_keys.gateway_verification_card.number');
        $card->cvvCode    = config('db_const.auth_keys.gateway_verification_card.cvc');
        $card->amount     = config('db_const.auth_keys.gateway_verification_card.amount');
        $card->currency   = config('db_const.auth_keys.gateway_verification_card.currency');
        $card->firstName  = config('db_const.auth_keys.gateway_verification_card.first_name');
        $card->lastName   = config('db_const.auth_keys.gateway_verification_card.last_name');

        if (config('app.env') === 'local' || config('app.debug') == true) {
            $_paymentGatewayForm = PaymentGatewayForm::where('id', $payment_gateway_id)->first();
            switch ($_paymentGatewayForm->name) {
                case 'CV Test Gateway':
                    $card->cardNumber='4111111111111111';
                    $card->firstName = auth()->user()->name;
                    $card->lastName = auth()->user()->name;
                    break;
                case 'Stripe':
                    $card->cardNumber='4242424242424242';
                    break;
                case 'Payfirma':
                    $card->cardNumber='';
                    break;
                case 'Moneris':
                    $card->cardNumber='';
                    break;
                case 'Helcim':
                    $card->cardNumber='';
                    break;
            }
        }

        $card->expiryMonth=config('db_const.auth_keys.gateway_verification_card.expiry_month');
        $card->expiryYear=config('db_const.auth_keys.gateway_verification_card.expiry_year');
        $card->statement_descriptor="Card Verify ID:".$user_account_id;
        $card->general_description="PMS integration Card Verification for Account ID : ".$user_account_id;
        return $card;
    }

    /**
     * @param array $stripeConnect
     * @param UserAccount $userAccount
     * @param $propertyInfoId
     * @return array
     */
    public function savePropertySpecificStripeConnect(array $stripeConnect, UserAccount $userAccount, $propertyInfoId) {
        try {

            $payment_gateway_id = \Session::get(PaymentGateways::PaymentGatewayID, 0);
            $gatewayStripeConnect = $this->getEmptyGatewayObjectForStripeConnect();
            $userPaymentGateway   = $this->getUnsavedUserPaymentGatewayModal($gatewayStripeConnect, $payment_gateway_id, $propertyInfoId);
            $pg = new PaymentGateway();
            $_gateway = $pg->stripeConnectAttachAccount($stripeConnect['code'], $userPaymentGateway, $gatewayStripeConnect);

            //Payment Gateways Settings
            $userSettingsBridge = UserSettingsBridge::where('property_info_id', $propertyInfoId)->where('user_account_id', $userAccount->id)->where('model_name', UserPaymentGateway::class)->first();
            
            if($userSettingsBridge == null) {
                $userPaymentGateway->payment_gateway_form_id = $payment_gateway_id;
                $userPaymentGateway->property_info_id = $propertyInfoId;
                $userPaymentGateway->user_id = auth()->user()->id;
                $userPaymentGateway->user_account_id = $userAccount->id;
                $userPaymentGateway->gateway = json_encode($_gateway);
                $userPaymentGateway->is_verified = 1;
                $userPaymentGateway->save();

                UserSettingsBridge::create([
                'user_account_id' =>  $userAccount->id,
                'booking_source_form_id' =>  0,
                'property_info_id' =>  $propertyInfoId,
                'model_name' =>  UserPaymentGateway::class,
                'model_id' => $userPaymentGateway->id]);
                session()->put(PaymentGateways::UserPaymentGatewayID, $userPaymentGateway->id);
            } else {
                $paymentGatewayFormIdPre = $userSettingsBridge->user_payment_gateway->payment_gateway_form_id;
                $userSettingsBridge->user_payment_gateway->gateway = json_encode($_gateway);
                $userSettingsBridge->user_payment_gateway->payment_gateway_form_id = $payment_gateway_id;
                $userSettingsBridge->user_payment_gateway->is_verified = 1;
                session()->put(PaymentGateways::UserPaymentGatewayID, $userSettingsBridge->user_payment_gateway->id);
                if($userSettingsBridge->user_payment_gateway->save()){
                    if ($paymentGatewayFormIdPre != $payment_gateway_id){
                        Bookings::booking_effects($propertyInfoId, $userSettingsBridge->user_payment_gateway);
                    }
                }
            }
            
            $property = $userAccount->properties_info->where('id', $propertyInfoId)->first();
            if (!is_null($property)) {
                $property->use_pg_settings = 1;
                $property->save();
            }
            
            session()->put(PaymentGateways::StripeConnectMsg, 'verified');
            session()->save();
            \Session::put(PaymentGateways::StripeConnectMsg, 'verified');
            \Session::save();
            
            // Triggering Event to check if bookings did not processed due to gateway missing or not verified.
            event(new GatewayAddEvent($userAccount, $property));
            
            return ['status' => true, 'msg' =>'success'];
        }
        catch (GatewayException $e) {
            report($e);
            Log::critical($e->getMessage(), ['File'=>PmsIntegrationController::class, 'Stack'=>$e->getTraceAsString()]);
            session()->put(PaymentGateways::StripeConnectMsg, $e->getMessage());
            return ['status' => false, 'msg' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::critical($e->getMessage(), ['File'=>PmsIntegrationController::class, 'Stack'=>$e->getTraceAsString()]);
            session()->put(PaymentGateways::StripeConnectMsg, $e->getMessage());
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return GateWay
     */
    private function getEmptyGatewayObjectForStripeConnect() {
        $stripeConnect = new GateWay();
        $stripeConnect->name = "Stripe";
        $accountId = new CredentialFormField();
        $accountId->name = config('db_const.pg_form_stripe_connect.credentials.0.name');
        $accountId->safe = true;
        $accountId->label = config('db_const.pg_form_stripe_connect.credentials.0.label');
        $accountId->url = config('db_const.pg_form_stripe_connect.credentials.0.url');
        $accountId->type = CredentialFormField::TYPE_BUTTON;
        $accountId->state = CredentialFormField::STATE_SHOW;
        $accountId->value = '';
        $scPub = new CredentialFormField();
        $scPub->name = config('db_const.pg_form_stripe_connect.credentials.1.name');
        $scPub->safe = true;
        $scPub->label = config('db_const.pg_form_stripe_connect.credentials.1.label');
        $scPub->value = '';
        $scPub->type = CredentialFormField::TYPE_TEXT;
        $scPub->state = CredentialFormField::STATE_HIDDEN;
        $stripeConnect->credentials = array($accountId, $scPub);
        return $stripeConnect;
    }

    /**
     * @param GateWay $gateWay
     * @param $payment_gateway_id
     * @param $propertyInfoId
     * @return UserPaymentGateway
     */
    private function getUnsavedUserPaymentGatewayModal(Gateway $gateWay, $payment_gateway_id, $propertyInfoId) {
        $userPaymentGateway = new UserPaymentGateway();
        $userPaymentGateway->payment_gateway_form_id = $payment_gateway_id;
        $userPaymentGateway->property_info_id = $propertyInfoId;
        $userPaymentGateway->user_id = auth()->user()->id;
        $userPaymentGateway->user_account_id = auth()->user()->user_account_id;
        $userPaymentGateway->gateway = json_encode($gateWay);
        return $userPaymentGateway;
    }
}