<?php

namespace App\PMS;


use App\BookingInfo;
use App\BookingInfoDetail;
use App\CaCapability;
use App\Events\Emails\EmailEvent;
use App\Events\PMSPreferencesEvent;
use App\Events\StripeCommissionBilling\StripeCommissionUsageUpdateEvent;
use App\Events\TransactionInitEvent;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\Repositories\BookingSources\BookingSources;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Services\CreditCardInfoService;
use App\Services\PropertySettings;
use App\System\PaymentGateway\Models\Card;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\System\StripeCommissionBilling\StripeCommissionBillingBase;
use App\UserAccount;
use App\UserPaymentGateway;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


trait BookingJobHelper {

    use CreditCardInfoService;

    public static $PMS_BOOKING_STATUS_NEW = 'new';
    public static $PMS_BOOKING_STATUS_MODIFY = 'modify';
    public static $PMS_BOOKING_STATUS_CANCEL = 'cancel';
    public static $BOOKING_AUTOMATION_PMS_NAME = 'Booking Automation';
    public static $BEDS_24_PMS_NAME = 'Beds24';

    /**
     * @var BookingSources
     */
    public static $bsRepo = null;
    public static $bsCapabilities = [];

    private function init_booking_job_helper() {
        if(self::$bsRepo == null) {
            self::$bsRepo = new BookingSources();
            self::$bsCapabilities = self::$bsRepo->getAllBookingSourcesCapabilities();
        }
    }

    public function getPmsOptions_FetchBooking(PropertyInfo $propertyInfo, $pms_booking_id) {
        /**
         * @var $options PmsOptions
         */
        $options = resolve(PmsOptions::class);
        $options->propertyID = $propertyInfo->pms_property_id;
        $options->propertyKey = $propertyInfo->property_key;
        $options->bookingID = $pms_booking_id;
        $options->includeCard = true;
        $options->includeInvoice = true;
        $options->includeInfoItems = true;
        return $options;
    }

    /**
     * @param UserAccount $user_account
     * @param PropertyInfo $property_info
     * @param PMS $pms
     * @param $get_card_token
     * @param $card_cvv
     * @param Booking $pms_booking
     * @return Card
     */
    public function getCard(UserAccount $user_account, PropertyInfo $property_info, PMS $pms, $get_card_token, $card_cvv, Booking &$pms_booking) {

        /**
         * @var $pms_options PmsOptions
         */
        $pms_options = resolve(PmsOptions::class);

        $pms_options->bookingToken = $get_card_token;
        $pms_options->cardCvv = $card_cvv;
        $pms_options->requestType = PmsOptions::REQUEST_TYPE_JSON;
        $pms_options->bookingID = $pms_booking->id;
        $pms_options->propertyKey = $property_info->property_key;
        $pms_options->propertyID = $property_info->pms_property_id;

        $card = Bookings::BA_GetCard($user_account, $pms, $pms_options, $pms_booking);

        $card->currency = $property_info->currency_code;

        $pms_booking->cardNumber = $pms_booking->cardCvv  = null; // Remove card number, cvv for security reasons.

        return $card;
    }

    /**
     * Insert New Booking Info Record For New | Sync.
     * @param UserAccount $user_account
     * @param PropertyInfo $property_info
     * @param Booking $pms_booking
     * @param string $typeOfPaymentCard
     * @param null $bs_formId Booking Source Form ID
     * @param bool $is_manual
     * @return mixed
     * @throws Exception
     */
    public function insertBookingInfoRecord(UserAccount $user_account, PropertyInfo $property_info, Booking $pms_booking,
                                            $typeOfPaymentCard, $bs_formId = null, bool $is_manual = false) {

        $bs_formId = !is_null($bs_formId)
            ? $bs_formId
            : self::$bsRepo::getBookingSourceFormIdByChannelCode($user_account->pms->pms_form_id, $pms_booking->channelCode);

        $property_time_zone = get_property_time_zone($property_info);

        $this->setBABookingDatesToGMT($user_account, $pms_booking, $property_time_zone);

        $cancellationSettings = $this->getCancellationPolicies($user_account, $property_info, $pms_booking, $bs_formId);

        $isAutoPaymentCapability = $this->isBookingSourceCapableFor($bs_formId, CaCapability::AUTO_PAYMENTS);
        $isAutoSDDCapability = $this->isBookingSourceCapableFor($bs_formId, CaCapability::SECURITY_DEPOSIT);

        $isProcessable = ($pms_booking->balancePrice > 0 && ($isAutoPaymentCapability || $isAutoSDDCapability) ? 1 : 0);


        if ($isProcessable != 0) { // User Payment Gateway added and verified.
            $isProcessable = empty($this->getUserPaymentGateway($property_info)) ? 2 : $isProcessable;
        }

        /**
         * @var $booking_info BookingInfo
         */
        $booking_info = BookingInfo::create(
            [
                'pms_booking_id' => $pms_booking->id,
                'master_id' => $pms_booking->getMasterId(),
                'bs_booking_id' => $pms_booking->refererOriginal,
                'user_account_id' => $user_account->id,
                'user_id' => $property_info->user_id,
                'pms_id' => $user_account->pms->pms_form_id, //Place pms_id before channel_code, to access in accessors
                'channel_code' => $pms_booking->channelCode,
                'property_id' => $pms_booking->propertyId,
                'property_info_id' => $property_info->id,
                'room_id' => $pms_booking->roomId,
                'unit_id'=> $pms_booking->unitId,
                'guest_email' => $pms_booking->guestEmail,
                'guest_title' => $pms_booking->guestTitle,
                'guest_phone'=> !empty($pms_booking->guestPhone) ? $pms_booking->guestPhone : $pms_booking->guestMobile,
                'guest_name' => $pms_booking->guestFirstName,
                'guest_last_name' => $pms_booking->guestLastName,
                'guest_zip_code' => $pms_booking->guestPostcode,
                'guest_post_code' => $pms_booking->guestPostcode,
                'guest_country' => $pms_booking->guestCountry,
                'guest_address' => $pms_booking->guestAddress,
                'guest_currency_code' => $pms_booking->currencyCode,
                'guestMobile' =>  $pms_booking->guestMobile,
                'guestFax' =>  $pms_booking->guestFax,
                'guestCity' =>  $pms_booking->guestCity,
                'notes' =>  $pms_booking->notes,
                'flagColor' =>  $pms_booking->flagColor,
                'flagText' =>  $pms_booking->flagText,
                'bookingStatusCode' =>  $pms_booking->bookingStatusCode,
                'price' =>  $pms_booking->price,
                'bookingReferer' =>  $pms_booking->bookingReferer,
                'guestComments' =>  $pms_booking->guestComments,
                'guestArrivalTime' =>  $pms_booking->guestArrivalTime,
                'invoiceNumber' =>  $pms_booking->invoiceNumber,
                'invoiceDate' =>  $pms_booking->invoiceDate,
                'apiMessage' =>  $pms_booking->apiMessage,
                'message' =>  $pms_booking->message,
                'bookingIp' =>  $pms_booking->bookingIp,
                'host_comments' =>  $pms_booking->hostComments,
                //GMT DATE TIME
                'booking_time' => $pms_booking->bookingTime,
                'pms_booking_modified_time' => $pms_booking->bookingModifyTime,
                'check_in_date' => $pms_booking->firstNight,
                'check_out_date' => $pms_booking->lastNight,
                'property_time_zone' => $property_time_zone,
                'pms_booking_status' => $pms_booking->bookingStatus,
                'total_amount' => $pms_booking->balancePrice,
                'booking_older_than_24_hours' => 0,
                'full_response' => json_encode($pms_booking),
                'record_source' => 1,
                'is_vc' => $typeOfPaymentCard,  // CC, VC, BT
                'is_manual' => $is_manual ? 1 : 0,
                'cancellation_settings' => $cancellationSettings,
                'is_process_able' => $isProcessable,
                'num_adults'=> $pms_booking->numberOfAdults,
                'channel_reference'=> $pms_booking->channelReference,
                'num_nights'=> $pms_booking->numNight
            ]
        );

        if($isProcessable != 1){

            $preferenceFormId = config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED');

            event(new PMSPreferencesEvent($user_account, $booking_info, 0, $preferenceFormId));
        }

        StripeCommissionUsageUpdateEvent::dispatch(
            StripeCommissionBillingBase::BOOKING_INFO_MODEL,
            $booking_info->id,
            StripeCommissionBillingBase::ACTION_NUMBER_OF_BOOKING_UPDATE);

        $bookings = app()->makeWith(Bookings::class, ['user_account_id' => $user_account->id]);

        $bookings->UpdateSystemJobOnSuccess(
            $user_account->id,
            $booking_info->pms_booking_id,
            $booking_info->property_id,
            self::$PMS_BOOKING_STATUS_NEW, $booking_info->id);

        //Email To Both Client And Guest
        event(new EmailEvent(config('db_const.emails.heads.new_booking.type'),$booking_info->id));
        $booking_info = BookingInfo::find($booking_info->id);

        return $booking_info;
    }

    /**
     * Set Booking Info Local Hotel Times to GMT By memory reference
     * @param UserAccount $user_account
     * @param Booking $pms_booking
     * @param $property_time_zone
     */
    public function setBABookingDatesToGMT(UserAccount $user_account, Booking &$pms_booking, $property_time_zone)
    {
        switch ($user_account->pms->pms_form->name) {

            case self::$BOOKING_AUTOMATION_PMS_NAME:
            case self::$BEDS_24_PMS_NAME:

                // Add One hour (1 am) for VC and 16 hours (4pm) for CC | BT to Check-in Date.
                $check_in_hour = $pms_booking->getTypeofPaymentSource() == BS_Generic::PS_VIRTUAL_CARD ? 1 : 16;
                $check_out_hour = 10; // Add Ten hours (10 am) to Check-Out Date.

                $pms_booking->firstNight = Carbon::parse($pms_booking->firstNight, $property_time_zone)
                    ->setTime($check_in_hour, 0, 0)->setTimezone('GMT')->toDateTimeString();

                // IF Booking Time greater than generated check-in time then use Booking Time as Check-in.
                //$pms_booking->firstNight = $pms_booking->bookingTime > $check_in ? $pms_booking->bookingTime : $check_in;

                $pms_booking->lastNight = Carbon::parse($pms_booking->lastNight, $property_time_zone)
                    ->setTime($check_out_hour, 0, 0)->setTimezone('GMT')->toDateTimeString();

                break;
        }
    }

    /**
     * @param UserAccount $user_account
     * @param PropertyInfo $property_info
     * @param Booking $pms_booking
     * @param $bs_formId
     * @return string
     */
    public function getCancellationPolicies(UserAccount $user_account, PropertyInfo $property_info,
                                            Booking $pms_booking, $bs_formId) {
        return (($this->isBookingSourceCapableFor($bs_formId, CaCapability::AUTO_PAYMENTS))
            ? Bookings::getBACancellationSettingsByCheckingNonRefundableBooking($user_account,
                $property_info, $pms_booking, $bs_formId) : '');
    }

    /**
     * Returns True if Booking Source Capability Status Active for regarding capability_name else False,
     * Pass $capability_name from CaCapability Model's defined Constants
     * @param $bs_formId
     * @param $capability_name
     * @return bool
     */
    public function isBookingSourceCapableFor($bs_formId, $capability_name)
    {
        return isset(self::$bsCapabilities[$bs_formId][$capability_name])
            ? self::$bsCapabilities[$bs_formId][$capability_name]
            : false;
    }

    /**
     * Validate Booking Source Before Dispatching or triggering  Transaction Init Listener for Payment and Auth Entries.
     * @param $booking_source_form_id
     * @return bool
     */
    public function shouldDispatchTransactionInitEvent($booking_source_form_id)
    {
        return $this->isBookingSourceCapableFor($booking_source_form_id, CaCapability::AUTO_PAYMENTS)
            || $this->isBookingSourceCapableFor($booking_source_form_id, CaCapability::SECURITY_DEPOSIT);
    }

    /**
     * Return User Payment Gateway against Specific Property | null if not verified Yet!
     * @param PropertyInfo $property_info
     * @return UserPaymentGateway|null
     */
    public  function getUserPaymentGateway(PropertyInfo $property_info) {
        /**
         * @var $payment_gateway_repo PaymentGateways
         */
        $payment_gateway_repo = resolve(PaymentGateways::class);
        $userGateway = $payment_gateway_repo->getPropertyPaymentGatewayFromProperty($property_info);
        return (!empty($userGateway) && ($userGateway->is_verified == 1)) ? $userGateway : null;
    }

    /**
     * Transaction Init Event Trigger by validating Payment Gateway.
     * @param UserAccount $user_account
     * @param PropertyInfo $property_info
     * @param Booking $pms_booking
     * @param BookingInfo $booking_info
     * @param Card $card
     * @param $bs_formId
     * @param string $typeOfPaymentCard
     */
    public function insertTransactionRecords(UserAccount $user_account, PropertyInfo $property_info, Booking $pms_booking,
                                             BookingInfo $booking_info, Card $card, $bs_formId, $typeOfPaymentCard) {

        $user_payment_gateway = $this->getUserPaymentGateway($property_info);
        $is_group_booking = $pms_booking->isGroupBooking();
        $meta_data = [
            'booking'=> $pms_booking,
            'booking_info'=> $booking_info,
            'user_account'=> $user_account,
            'card'=> $card,
            'property_info'=> $property_info
        ];

        $customer_object = [];
        $cc_info = null;
        if($booking_info->total_amount > 0) {
            switch ($typeOfPaymentCard) {
                case BS_Generic::PS_CREDIT_CARD:

                    if (!$is_group_booking) {
                        $customer_object = $this->createCustomerObject(0, $card, $booking_info, $pms_booking, $user_account, $user_payment_gateway);
                        if (!$customer_object['status']) {
                            
                            $ccStatus = $customer_object['reason'] == config('db_const.credit_card_infos.status.Gateway-Missing') 
                                    ? config('db_const.credit_card_infos.status.Gateway-Missing') 
                                    : config('db_const.credit_card_infos.status.Failed');
                            
                            $cc_info = $this->addCcInfoEntry(0, 
                                    $meta_data, 
                                    $customer_object['error_message'], 
                                    $ccStatus, 
                                    $customer_object['type']);
                            
                            $this->insert_CC_Info_Log($cc_info->id, 
                                    $user_account->id, 
                                    $customer_object['error_message'], 
                                    $cc_info->status);
                        }
                    }
                    break;
                case BS_Generic::PS_VIRTUAL_CARD:

                    if (!$is_group_booking) {
                        $cc_info = $this->addCcInfoEntry(1, $meta_data, 'Due to VC customer object will be created after Checkin/due date');
                    }
                    break;
                case BS_Generic::PS_BANK_TRANSFER:

                    $cc_info = $this->addCcInfoEntry(2, $meta_data, 'No, error. Bank Transfer Booking.', config('db_const.credit_card_infos.status.Void'));
                    break;
            }
        }else{

            $this->ccInfoEntryForBookingAmountNotValid($typeOfPaymentCard, $meta_data);
        }
        
        if($this->shouldDispatchTransactionInitEvent($bs_formId)) {
            
            if(empty($user_payment_gateway)) {
                
                /**
                 * NOTE: this event is deleted so don't uncomment this -- Use new email plugin
                 * As Account Setup Skip Payment Gateway Added.
                 *
                    event(new ClientBookingFetchingFailedNotifyEvent(
                        $property_info->pms_property_id,
                        $user_account,
                        $pms_booking->id,
                        '',
                        'Payment Gateway not Active',
                        config('db_const.booking_fetching_failed.exception_type.payment_gateway_not_active')
                    ));
                 *
                 */
                
                $booking_info->update(['is_process_able' => BookingInfo::PAYMENT_GATEWAY_INACTIVE]);
                
                // TODO: @harbrinder send email from here
                
                Log::notice('Payment Gateway not Active For Payments', 
                        [
                            'userAccountId' => $user_account->id, 
                            'Pms Booking Id' => $pms_booking->id, 
                            'File' => __FILE__
                        ]
                );
            }
            
            TransactionInitEvent::dispatch(
                $card,
                $pms_booking,
                $user_account,
                $typeOfPaymentCard,
                $property_info->id,
                $property_info->user_id,
                $user_payment_gateway,
                $booking_info,
                $customer_object,
                $cc_info);
            
        }

    }

    private function ccInfoEntryForBookingAmountNotValid($type, $meta_data) {
        switch ($type) {

            case BS_Generic::PS_BANK_TRANSFER:
            case BS_Generic::PS_CREDIT_CARD:
                $is_vc = $type == BS_Generic::PS_CREDIT_CARD ? 0 : 2;
                $this->addCcInfoEntry($is_vc, $meta_data,'No, error. Balance Price greater than Zero', null);
                break;

            case BS_Generic::PS_VIRTUAL_CARD:
                $this->addCcInfoEntry(1, $meta_data, 'No, error. Balance Price greater than Zero', null);
                break;

        }

        log::info("Booking Amount not valid or Zero for Booking ID : " . $meta_data['booking_info']->id,
            array('File'=> __FILE__));

        event(new PMSPreferencesEvent(
            $meta_data['user_account'],
            $meta_data['booking_info'],
            0,
            config('db_const.user_preferences.preferences.BOOKINGS_THAT_WILL_NOT_BE_CHARGED')
        ));
    }

    public function getTypeOfPaymentCard(Booking $pms_booking, UserAccount $userAccount) {

        $typeOfPaymentCard = $pms_booking->getTypeofPaymentSource();

        if($pms_booking->isGroupBooking()) {
            /**
             * @var $masterBookingInfo BookingInfo
             */
            $masterBookingInfo = BookingInfo::where('pms_booking_id', $pms_booking->getMasterId())
                ->where('pms_id', $userAccount->pms->pms_form_id)
                ->where('user_account_id', $userAccount->id)
                ->first();
            if(!empty($masterBookingInfo))
                $typeOfPaymentCard = $masterBookingInfo->is_vc;
        }

        return $typeOfPaymentCard;
    }
}
