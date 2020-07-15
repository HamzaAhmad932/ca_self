<?php

namespace App\Repositories\Properties;

use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use App\Repositories\Settings\CancellationAmountType;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\Settings\PaymentSchedule;
use App\Repositories\Settings\SecurityDamageDeposit;
use Illuminate\Support\Arr;
use App\Events\PropertyConnectStatusChangeEvent;
use App\Events\StripeCommissionBilling\StripeCommissionUsageUpdateEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\General\BookingSource\ClientActiveBookingSourcesWithDetailResource;
use App\Repositories\Bookings\Bookings;
use App\Repositories\Settings\GenericAmountType;
use App\System\StripeCommissionBilling\StripeCommissionBillingBase;
use App\Unit;
use App\UserBookingSource;
use DB;
use App\UserAccount;
use App\PropertyInfo;
use App\RoomInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Self_;
use Yajra\Datatables\Datatables;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\System\PMS\exceptions\PmsExceptions;
use Exception;
use App\Repositories\GenericEmailSMSWithContent\GenericEmailWithContent;
use App\System\PMS\Models\Property;


class Properties
{
    private $user_account_id;

    public function __construct($user_account_id)
    {
        $this->user_account_id = $user_account_id;
    }


    public static function isPropertyActive(PropertyInfo $property_info = null) {
        return !is_null($property_info) && $property_info->status == 1;
    }



    /**
     * @param ClientActiveBookingSourcesWithDetailResource|null $userBookingSource
     * @return array
     */

   public static function getBookingSourceSettingsDetailsOfClientProperty(ClientActiveBookingSourcesWithDetailResource  $userBookingSource = null)
   {

        $BSSettingsArr = [
            'booking_payment'  => ['status' => false, 'details' => ''],
            'booking_deposit'  => ['status' => false, 'details' => ''],
            'security_deposit' => ['status' => false, 'details' => ''],
            'return_rules'     => ['status' => false, 'details' => '']
        ];

       if (!is_null($userBookingSource)) {

            // Payment Settings
           $booking_payment  = new PaymentSchedule(!empty($userBookingSource->payment_schedule_setting[0])
               ? $userBookingSource->payment_schedule_setting[0]->settings : UserBookingSource::DEFAULT_PAYMENT_SETTING);

           // Credit Card Settings
           $booking_deposit  = new CreditCardValidation(!empty($userBookingSource->credit_card_validation_setting[0])
               ? $userBookingSource->credit_card_validation_setting[0]->settings : UserBookingSource::DEFAULT_CREDIT_CARD_SETTING);

            // SD Settings
           $security_deposit = new SecurityDamageDeposit(!empty($userBookingSource->security_damage_deposit_setting[0])
               ? $userBookingSource->security_damage_deposit_setting[0]->settings : UserBookingSource::DEFAULT_SECURITY_DEPOSIT_SETTING);

           // Cancellation Settings
           $return_rules     = new CancellationAmountType(!empty($userBookingSource->cancellation_setting[0])
               ? $userBookingSource->cancellation_setting[0]->settings : UserBookingSource::DEFAULT_CANCELLATION_SETTING);


           $BSSettingsArr['booking_payment']['status']  = $booking_payment->status;
           $BSSettingsArr['booking_deposit']['status']  = $booking_deposit->status;
           $BSSettingsArr['security_deposit']['status'] = $security_deposit->status;
           $BSSettingsArr['return_rules']['status']     = $return_rules->status;

           /** Payment Settings */
           if ($BSSettingsArr['booking_payment']['status']) {
               $BSSettingsArr['booking_payment']['details'] = $booking_payment->onlyVC == true
                   ? 'Only VC Bookings'
                   : Properties::getSettingsChargeTimeByCheckingGenericDayType($booking_payment->dayType, $booking_payment->afterBookingDays, $booking_payment->beforeCheckInDays);
           }

           /** CC Auth Settings */
           if ($BSSettingsArr['booking_deposit']['status']) {
               $BSSettingsArr['booking_deposit']['details']  = Properties::getSettingsChargeAmountByCheckingGenericAmountType($booking_deposit->amountType, $booking_deposit->amountTypeValue);
           }
           /** SD Auth Settings */
           if ($BSSettingsArr['security_deposit']['status']) {
               $BSSettingsArr['security_deposit']['details']  = Properties::getSettingsChargeAmountByCheckingGenericAmountType($security_deposit->amountType, $security_deposit->amountTypeValue);
           }

           /**  Cancellation Policies | Return Rules Auth Settings */
           if ($BSSettingsArr['return_rules']['status']) {
               $count = 0;
               if (($return_rules->afterBookingStatus) || ($return_rules->beforeCheckInStatus))
                   $count ++;
               foreach ($return_rules->rules as $rule){
                   if ((($rule['canFee'] === 'first_night') || ($rule['canFee'] > 0)) && ($rule['is_cancelled'] >= 0)){
                       $count ++;
                   }
               }
               $BSSettingsArr['return_rules']['details'] = $count .($count === 1 ? ' rule' : ' rules');
           }
       }
       return $BSSettingsArr;
   }

    /**
     * @param $genericAmountType
     * @param int $amount
     * @return string
     */
   public static function getSettingsChargeAmountByCheckingGenericAmountType($genericAmountType, $amount = 0){
       $amountType = '';
       switch ($genericAmountType){
           case GenericAmountType::AMOUNT_TYPE_FIXED :
               $amountType = $amount . ' fixed amount';
               break;
           case GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE :
               $amountType = $amount . ' % of booking';
               break;
           case GenericAmountType::AMOUNT_TYPE_FIRST_NIGHT :;
               $amountType = 'First Night';
               break;
       }
       return $amountType;
   }

    /**
     * @param $genericDayType
     * @param $afterBookingDays
     * @param $beforeCheckInDays
     * @return string
     */
   public static function getSettingsChargeTimeByCheckingGenericDayType($genericDayType, $afterBookingDays, $beforeCheckInDays){
       $dayType = '';
       switch ($genericDayType) {
           case GenericAmountType::AFTER_BOOKING :
               $dayType = ($afterBookingDays == 0 ? 'Immediately' : \Carbon\CarbonInterval::seconds($afterBookingDays)->cascade()->forHumans()). ' after booking';
               break;
           case GenericAmountType::BEFORE_CHECK_IN :
               $dayType = ($beforeCheckInDays == 0 ? 'Immediately' :  \Carbon\CarbonInterval::seconds($beforeCheckInDays)->cascade()->forHumans()) . ' before check-in';
               break;
       }
       return $dayType;
   }


    /**
     * @param UserAccount $userAccount
     * @param $propertyInfoId
     * @param bool $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePropertyConnectStatusOnPMS (UserAccount $userAccount,  $propertyInfoId, bool $status) {

        $propertyInfo = $userAccount->properties_info->where('id', $propertyInfoId)->first();
        $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
        $state = $status ? 'Connect' : 'Disconnect';
        $notificationUrl = '';
        $controllerObject = resolve(Controller::class);
        try{
            $pms = new PMS($userAccount);
            $options = new PmsOptions();
            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $options->propertyKey = $propertyInfo->property_key;
            $options->propKey = $propertyInfo->property_key;
            $options->propertyID  = $propertyInfo->pms_property_id;

            $property = new Property();
            $property->action = Property::BA_ACTION_MODIFY;


            $notificationUrl = $status ? generatePropertyNotificationUrlForBookingAutomation($userAccount->id) : '';

            $options->requestType = PmsOptions::REQUEST_TYPE_XML;
            $remote_property = $pms->fetch_property($options);

            $notify_url_string = append_notify_url($remote_property[0], $notificationUrl);


            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

            $property->caNotifyURL = $notify_url_string;
            //$property->caNotifyURL = $notificationUrl;

            try {
                $pms_updated = $pms->update_properties($options, [$property]);
            } catch (PmsExceptions $e) {
                report($e);
                if ($status)
                return $controllerObject->errorResponse("Property # $propertyInfo->pms_property_id  $state Failed.<br><br>".
                    $e->getCADefineMessage(),$e->getPMSCode());
            }

            if($pms_updated || !$status) {
                $propertyInfo->update(['status' => $status, 'notify_url' => $notificationUrl]);

                if ($status)
                {
                    //send email to client for activated properties
                    event(new EmailEvent(config('db_const.emails.heads.properties_activated.type'), $this->user_account_id, [ 'property_status' => $connect, 'properties_info_ids' => [ $propertyInfo->id ] ] ));
                }
                else
                {
                    //send email to client for deactivated properties
                    event(new EmailEvent(config('db_const.emails.heads.properties_deactivated.type'), $this->user_account_id, [ 'property_status' => $connect, 'properties_info_ids' => [ $propertyInfo->id ] ] ));
                }


                event(new PropertyConnectStatusChangeEvent([$propertyInfo->id], false, ($status == 1)));


                /**
                 * Update Property Records Usage On Stripe Commission Billing Invoice
                 */
                StripeCommissionUsageUpdateEvent::dispatch(StripeCommissionBillingBase::PROPERTY_INFO_MODEL, $propertyInfo->id, StripeCommissionBillingBase::ACTION_NUMBER_OF_PROPERTY_UPDATE);
                return $controllerObject->successResponse("Property # $propertyInfo->pms_property_id  $state Success",200);
            } else {
                return $controllerObject->errorResponse("Property # $propertyInfo->pms_property_id  $state Fail",501);
            }
        }  catch (\Exception $e) {
            return $controllerObject->errorResponse("Property # $propertyInfo->pms_property_id  $state Failed.<br><br>", 500);
        }
    }

    /**
     * @param \SimpleXMLElement $responseFormPMS
     * @return array
     */
    public static  function decodeUpdatedPropertyXMLResponse(\SimpleXMLElement $responseFromPMS){

        $result = ['failed'=>[], 'success' => []];
        $attribute = '@attributes';
        $responseFromPMS = json_decode(json_encode($responseFromPMS), true);
        
        if(!empty($responseFromPMS['property'])) {

            if (isset($responseFromPMS['property'][$attribute])) {
                $result['success'][] = $responseFromPMS['property'][$attribute]['id'];
            } else {
                foreach ($responseFromPMS['property'] as $response) {
                    $result['success'][] = $response[$attribute]['id'];
                }
            }

        } else {

            if (isset($responseFromPMS[$attribute]['code'])) {
                $result['failed'][] = $responseFromPMS[$attribute]['code'];
            } else {
                $result['failed'][] = $responseFromPMS;
            }

        }

        return $result;
    }

    /**
     * @param bool $connect
     * @param array $propertyIds
     * @return array
     */
    public function bulkConnectDisconnectProperties(bool $connect, array $propertyIds) {
        try{
            $userAccount   = UserAccount::with('properties_info')->where('id', $this->user_account_id)->first();
            $propertyInfos = $userAccount->properties_info->whereIn('id', $propertyIds);

            if ($propertyInfos->count() == 0)
                return ['status' => false, 'message' => 'No Property Found!'];

            $pms = new PMS($userAccount);
            $options = resolve(PmsOptions::class);
            $notificationUrl = $connect ? generatePropertyNotificationUrlForBookingAutomation($this->user_account_id) : '';
            $propertyArr     = array();

            /* Fetch PropertyKey for Property Form BA if not available on CA */
            $props = self::syncUserPropertiesKey($pms, $options, $propertyInfos);

            $prop_pms_ids = $connect ?
                $propertyInfos->whereNotIn('property_key',  [null, '', '-1'])->pluck('pms_property_id')->toArray()
                : $propertyInfos->pluck('pms_property_id')->toArray();

            if (empty($prop_pms_ids))
                return ['status' => false, 'message' => 'Missing Property Key on PMS, Please update Property key & try again.'];

            $options->requestType = PmsOptions::REQUEST_TYPE_XML;
            $options->getFullXmlResponse = true;
            //$props = $pms->fetch_properties($options);

            $userAccount->fresh(['properties_info']);
            $propertyInfos = $userAccount->properties_info->whereIn('id', $propertyIds);

            foreach($props as $prop) {
                if(in_array($prop->id, $prop_pms_ids)){
                    $notify_url_string = append_notify_url($prop, $notificationUrl);
                    $property = new Property();
                    $property->action      = Property::BA_ACTION_MODIFY;
                    $property->id          = $prop->id;
                    $property->caNotifyURL = $notify_url_string;
                    $propertyArr[]         = $property;
                }
            }

            $data = self::decodeUpdatedPropertyXMLResponse($pms->update_properties($options, $propertyArr));

            $data['propertiesWithOutKey'] = $propertyInfos->whereIn('property_key',  [null, '', '-1'])->pluck('pms_property_id')->toArray();

            if (count($data['success']) > 0) {

                PropertyInfo::where('user_account_id', $this->user_account_id)
                    ->whereIn('pms_property_id', $data['success'])
                    ->update(['status' => $connect,'notify_url' => $notificationUrl]);

                if ($connect) {
                    //resume transactions and auth for properties
                    event(new PropertyConnectStatusChangeEvent($data['success'], true, true));

                    //send email to client for activated properties
                    event(new EmailEvent(config('db_const.emails.heads.properties_activated.type'), $this->user_account_id, [ 'property_status' => $connect, 'properties_info_ids' => $propertyInfos->whereIn('pms_property_id', $data['success'])->pluck('id')->toArray() ] ));

                } else {
                    //send email to client for deactivated properties
                    event(new EmailEvent(config('db_const.emails.heads.properties_deactivated.type'), $this->user_account_id, [ 'property_status' => $connect, 'properties_info_ids' => $propertyInfos->whereIn('pms_property_id', $data['success'])->pluck('id')->toArray() ] ));

                    event(new PropertyConnectStatusChangeEvent($data['success'], true, false));
                }


                /**
                 * Update Property Records Usage On Stripe Commission Billing Invoice
                 */
                StripeCommissionUsageUpdateEvent::dispatch(StripeCommissionBillingBase::PROPERTY_INFO_MODEL, $userAccount->properties_info->first()->id, StripeCommissionBillingBase::ACTION_NUMBER_OF_PROPERTY_UPDATE);
                return ['status' => true, 'data' => $data, 'message' => 'Property was updated successfully'];

            } else {
                return ['status' => false, 'message' => 'Property was not updated due to integration problem'];
            }

        }  catch (PmsExceptions $e) {
            report($e);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }



    /**
     * @param PMS $pms
     * @param PmsOptions $options
     * @param $propertyInfosCollection
     * @return array|null
     */
    public static function syncUserPropertiesKey(PMS $pms, PmsOptions $options, $propertyInfosCollection) {

        try {
            $props = [];
            $props = $pms->fetch_properties_json_xml($options);
            foreach ($props as $prop) {
                $property = $propertyInfosCollection->where('pms_property_id', $prop->id)->first();
                if (!empty($property) && $property->property_key != $prop->propertyKey)
                    $property->update(['property_key'=> $prop->propertyKey]);
            }
        }  catch (PmsExceptions $e) {
            report($e);
        }

        return $props;
    }
}
