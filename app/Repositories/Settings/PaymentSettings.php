<?php
/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 27-Dec-18
 * Time: 3:36 PM
 */

namespace App\Repositories\Settings;

use App\BookingInfo;
use App\CaCapability;
use App\PropertyInfo;
use App\Traits\Resources\General\Booking;
use App\Repositories\BookingSources\BookingSources;
use App\Services\PropertySettings;
use App\Services\Settings\PaymentRules;
use App\System\PMS\BookingSources\BS_Generic;
use App\UserSettingsBridge;
use App\UserBookingSource;
use App\CancellationSetting;
use App\PaymentScheduleSettings;
use App\CreditCardValidationSetting;
use App\SecurityDamageDepositSetting;
use App\Repositories\Bookings\Bookings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class PaymentSettings
{

    /**
     * @var $ccvArr
     * Credit Card Auth
     */

    private $ccvArr;

    /**
     * @var $psArr
     * PaymentSettings TransactionInit Amounts
     */

    private $psArr;

    /**
     * @var $sdArr
     * Security Deposit Auth
     */

    private $sdArr;

    /**
     * @var array $cancelArr
     * Cancellation Charges
     */
    private $cancelArr = array('transactionType' => CancellationSetting::TRANSACTION_TYPE_VOID, 'amount' => 0 );

    /**
     * @var PaymentSettingsOptions
     */
    private $options;
    /**
     * @var PropertyInfo
     */
    private $property_info;
    /**
     * @var PaymentRules
     */
    private $paymentRules;

    /**
     * @var PropertySettings
     */
    private $allSettings;

    private $checkInDateInDateFormat;
    private $checkOutDateInDateFormat;
    private $bookingTimeInDateFormat;

    /*
     * Check-in date-time default given in Construct
     * @var defaultCheckInDate
     */
    private $defaultCheckInDate;
    private $bookingSourceCapabilities;



    /**
     * PaymentSettings constructor.
     * @param PaymentSettingsOptions $options
     */
    public function __construct(PaymentSettingsOptions $options)
    {
        /**
         * @var $booking_source_repo BookingSources
         */


        $this->options = $options;
        $this->property_info = PropertyInfo::find($this->options->property_info_id);
        $this->options->bookingTime = Carbon::parse($this->options->bookingTime )->format('Y-m-d H:i:s');

        $this->defaultCheckInDate = $this->options->checkInDate;

        $this->checkInDateInDateFormat =  Carbon::parse($this->options->checkInDate)->format('Y-m-d');
        $this->checkOutDateInDateFormat = Carbon::parse($this->options->checkOutDate)->format('Y-m-d');
        $this->bookingTimeInDateFormat = Carbon::parse($this->options->bookingTime)->format('Y-m-d');

        $this->bookingSourceCapabilities = BookingSources::getBookingSourceAllCapabilitiesById($this->options->booking_source_id);


        //$this->CheckAndAdjustBookingTimeConflictWithCheckInDate();
    }


    private function initializeConstructValues()
    {
        if ($this->property_info->isActive()
            && ($this->bookingSourceCapabilities[CaCapability::AUTO_PAYMENTS]
                || $this->bookingSourceCapabilities[CaCapability::SECURITY_DEPOSIT])) {

            $this->allSettings = new PropertySettings($this->property_info);
            $paymentRules = $this->allSettings->paymentRules($this->options->booking_source_id);
            $this->paymentRules = $paymentRules->autoPayments() ? $paymentRules : null;
        }
    }



    /**
     * Return all TransactionInit , Credit Card Auth, Security Auth Transactions
     * @param \App\System\PMS\Models\Booking $booking
     * @return array
     */
    public function transactionDetails(\App\System\PMS\Models\Booking $booking)
    {
        $this->initializeConstructValues();

        //If Settings Exits...
        if (!empty($this->paymentRules)) {

            $paymentTypeMeta = new PaymentTypeMeta();

            // Transaction Inits
            $this->paymentSchedules($paymentTypeMeta, $booking);

            // Call creditCardAuth after paymentSchedules
            // if first payment date greater than CC Auth Date then proceed else no CC auth entry will be created
            $first_payment_date = !empty($this->psArr[0]['dueDate'])
                ? Carbon::parse($this->psArr[0]['dueDate'], 'GMT')->setTimezone($this->options->timeZone)->toDateTimeString()
                : null;

            // Pass first_payment_date in Local Hotel Time.
            $this->creditCardAuth($paymentTypeMeta, $booking, $first_payment_date);

            // Security Damage Deposit Auth
            $this->securityDepositAuth($paymentTypeMeta, $booking);
        }

        return array('creditCardValidation' => $this->ccvArr, 'paymentSchedule' => $this->psArr, 'securityDeposit' => $this->sdArr);
    }

    /**
     * @return PropertySettings
     */
    public function allSettings() {
        if (empty($this->allSettings))
            $this->initializeConstructValues();

        return $this->allSettings;
    }

    /**
     * Get Transaction Init entries by checking Payment Schedule Settings
     * @param PaymentTypeMeta $paymentTypeMeta
     * @param \App\System\PMS\Models\Booking $booking
     */
    private function paymentSchedules(PaymentTypeMeta $paymentTypeMeta, \App\System\PMS\Models\Booking $booking) {
        /*PaymentSchedule settings */
        $psSetting  = $this->getPaymentScheduleSetting();
        $psSetting->status = $this->bookingSourceCapabilities[CaCapability::AUTO_PAYMENTS] ? $psSetting->status : false;
        $this->typeofPaymentSource($booking, $psSetting);

        $amount1 = 0; //default zero

        if (($psSetting->status) && (!$this->options->isNonRefundable)) {

            $isWholeAmount = false;

            switch ($psSetting->dayType) {

                case GenericAmountType::AFTER_BOOKING :
                    $dueDate1 = Carbon::parse($this->options->bookingTime)->addSeconds($psSetting->afterBookingDays)->format('Y-m-d H:i:s');
                    break;

                case GenericAmountType::BEFORE_CHECK_IN :
                    $dueDate1 = Carbon::parse($this->options->checkInDate)->addSeconds(-($psSetting->beforeCheckInDays))->format('Y-m-d H:i:s');
                    break;
                default:
                    Log::critical('Day Type Not Valid for Payment Settings', ['details' => $this->options]);
                    return;
                    break;
            } //-------Checking dueDate-----------

            $dueDate2 = Carbon::parse($this->options->checkInDate)->subSeconds($psSetting->remainingBeforeCheckInDays)->format('Y-m-d H:i:s');

            // Due Date not be less than booking time and not greater than check-in date time
            $dueDate1 = $dueDate1 > $this->options->checkInDate ? $this->options->checkInDate : $dueDate1;
            $dueDate2 = $dueDate2 < $this->options->bookingTime ? $this->options->bookingTime : $dueDate2;

            switch ($psSetting->amountType) {

                case GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE :
                    $isWholeAmount = ($psSetting->amountTypeValue ==  100 ? true : false);
                    $amount1= ($this->options->totalAmount/100)*$psSetting->amountTypeValue;
                    break;

                case GenericAmountType::AMOUNT_TYPE_FIRST_NIGHT :

                    $date1 = \Carbon\Carbon::createFromFormat('Y-m-d', $this->checkInDateInDateFormat);
                    $date2 = \Carbon\Carbon::createFromFormat('Y-m-d', $this->checkOutDateInDateFormat);
                    $diff_in_days = ($date1->diffInDays($date2) == 0 ? 1 : $date1->diffInDays($date2));
                    $amount1 = round(($this->options->totalAmount / $diff_in_days),2);
                    break;
            }


            if (($dueDate2 < $dueDate1) && !$isWholeAmount)
                $dueDate1 = $dueDate2;

            $dueDate1 =  $this->isGreaterBookingTime($dueDate1);
            $dueDate2 =  $this->isGreaterBookingTime($dueDate2);

            $dueDate1InDateFormat = Carbon::parse($dueDate1)->format('Y-m-d');
            $dueDate2InDateFormat = Carbon::parse($dueDate2)->format('Y-m-d');

            if ((!$isWholeAmount) && ($dueDate1InDateFormat  != $dueDate2InDateFormat)) {

                $amount2 = ($this->options->totalAmount - $amount1);
                $this->psArr[0]['dueDate'] = $this->toGMT($dueDate1);
                $this->psArr[0]['amount'] = $amount1;
                $this->psArr[0]['paymentTypeMeta'] = ($this->options->totalAmount == $amount1 ? $paymentTypeMeta->getBookingPaymentAutoCollectionFull() : $paymentTypeMeta->getBookingPaymentAutoCollectionPartial1of2());

                if ($amount2 > 0) {
                    $this->psArr[1]['dueDate'] = $this->toGMT($dueDate2);
                    $this->psArr[1]['amount'] = $amount2;
                    $this->psArr[1]['paymentTypeMeta'] = $paymentTypeMeta->getBookingPaymentAutoCollectionPartial2of2();
                }

            } else {
                $this->psArr[0]['dueDate'] = $this->toGMT($dueDate1);
                $this->psArr[0]['amount'] = $this->options->totalAmount;
                $this->psArr[0]['paymentTypeMeta'] = $paymentTypeMeta->getBookingPaymentAutoCollectionFull();
            }

        } elseif (($psSetting->status)
            && ($this->options->isNonRefundable)) {
            $dueDate1 = $this->isGreaterBookingTime($this->options->bookingTime);
            $this->psArr[0]['dueDate'] = $this->toGMT($dueDate1);
            $this->psArr[0]['amount'] = $this->options->totalAmount;
            $this->psArr[0]['paymentTypeMeta'] = $paymentTypeMeta->getBookingPaymentAutoCollectionFull();
        }
    }

    /**
     * Get Credit Card Auth amount and due date by checking Payment Schedule Settings
     * @param PaymentTypeMeta $paymentTypeMeta
     * @param \App\System\PMS\Models\Booking $booking
     * @param null $firstPaymentDate
     */
    private function creditCardAuth(PaymentTypeMeta $paymentTypeMeta, \App\System\PMS\Models\Booking $booking, $firstPaymentDate = null) {

        // Check for Auth Settings if CC Type Booking otherwise no need to get CC Auth for VC or BT.
        if ($booking->getTypeofPaymentSource() == BS_Generic::PS_CREDIT_CARD) {

            /* CreditCardValidation settings */
            $ccvSetting = $this->getCcvSetting();
            $ccvSetting->status = $this->bookingSourceCapabilities[CaCapability::AUTO_PAYMENTS] ? $ccvSetting->status : false;

            if ($ccvSetting->status) {

                $authTimeAfterBooking = Carbon::parse($this->options->bookingTime)->addSeconds($ccvSetting->authorizeAfterDays)->format('Y-m-d H:i:s');
                $authTimeAfterBooking = $this->isGreaterBookingTime($authTimeAfterBooking);

                $authorize = (
                    !empty($firstPaymentDate)
                    ? (Carbon::parse($authTimeAfterBooking)->format('Y-m-d') < Carbon::parse($firstPaymentDate)->format('Y-m-d'))
                    : true
                );

                if ($authorize) {
                    switch ($ccvSetting->amountType) {
                        case GenericAmountType::AMOUNT_TYPE_FIXED :
                            if ($ccvSetting->amountTypeValue > 0)
                                $this->ccvArr['amount'] = ($ccvSetting->amountTypeValue > $this->options->totalAmount
                                    ? $this->options->totalAmount : $ccvSetting->amountTypeValue);
                            else
                                $authorize = false;

                            break;

                        case GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE :
                            if ($ccvSetting->amountTypeValue > 0)
                                $this->ccvArr['amount'] = ($this->options->totalAmount / 100) * $ccvSetting->amountTypeValue;
                            else
                                $authorize = false;
                            break;

                        case GenericAmountType::AMOUNT_TYPE_FIRST_NIGHT :
                            $stay_nights = Booking::calculateStayNightsFromPmsBooking($booking);
                            $diff_in_days = ($stay_nights == 0 ? 1 : $stay_nights);
                            $this->ccvArr['amount'] = round(($this->options->totalAmount / $diff_in_days), 2);
                            break;
                    }

                    if (($authorize) && (isset($this->ccvArr['amount'])) && ($this->ccvArr['amount'] > 0)) {
                        $this->ccvArr['dueDate'] = $this->toGMT($authTimeAfterBooking);
                        $this->ccvArr['paymentTypeMeta'] = $paymentTypeMeta->getAuthTypeCCValidation();
                        $this->ccvArr['autoReauthorize'] = $ccvSetting->autoReauthorize;
                        $this->ccvArr['autoReauthorizeDays'] = CreditCardValidation::$autoReauthorizeDays;

                    }
                }
            }
        }
    }

    /**
     * Get Security Damage Deposit amount and due date by checking Payment Schedule Settings
     * @param PaymentTypeMeta $paymentTypeMeta
     * @param \App\System\PMS\Models\Booking $booking
     */
    private function securityDepositAuth(PaymentTypeMeta $paymentTypeMeta, \App\System\PMS\Models\Booking $booking) {

        /* --------------- SecurityDamageDeposit Settings ----------------------*/
        $sdSetting  = $this->getSecurityDepositSetting();
        $sdSetting->status = $this->bookingSourceCapabilities[CaCapability::SECURITY_DEPOSIT] ? $sdSetting->status : false;
        if ($sdSetting->status){
            $authorizeSD = true;
            switch ($sdSetting->amountType){
                case GenericAmountType::AMOUNT_TYPE_FIXED :
                    if ($sdSetting->amountTypeValue > 0)
                        $this->sdArr['amount'] = $sdSetting->amountTypeValue;
                    else
                        $authorizeSD = false;
                    break;

                case GenericAmountType::AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE :
                    if ($sdSetting->amountTypeValue > 0)
                        $this->sdArr['amount'] = round(($this->options->totalAmount / 100) * $sdSetting->amountTypeValue, 2);
                    else
                        $authorizeSD = false;
                    break;

                case GenericAmountType::AMOUNT_TYPE_FIRST_NIGHT :
                    $stay_nights = Booking::calculateStayNightsFromPmsBooking($booking);
                    $diff_in_days = ($stay_nights == 0 ? 1 : $stay_nights);
                    $this->sdArr['amount']=round(($this->options->totalAmount / $diff_in_days),2);
                    break;
            }

            if (($authorizeSD) && (isset($this->sdArr['amount'])) && ($this->sdArr['amount'] > 0)) {
                $timeBeforeCheckIn = Carbon::parse($this->options->checkInDate)->subSeconds($sdSetting->authorizeAfterDays)->format('Y-m-d H:i:s');
                $this->sdArr['dueDate'] = $this->toGMT($this->isGreaterBookingTime($timeBeforeCheckIn));
                $this->sdArr['autoReauthorize'] = $sdSetting->autoReauthorize;
                $this->sdArr['autoReauthorizeDays'] = CreditCardValidation::$autoReauthorizeDays;
                $this->sdArr['paymentTypeMeta'] = $paymentTypeMeta->getAuthTypeSecurityDamageValidation();

            }
        }
    }


    /**
     * Cancellation Adjustment Amount
     * @return array
     */
    public function cancellationTransaction()
    {
        $cancelSetting = $this->bookingSourceCapabilities[CaCapability::AUTO_PAYMENTS] ?
            $this->getCancellationSetting() : null;

        if($this->options->cancellationTime
            > Carbon::parse($this->defaultCheckInDate)->addHours(15)->addMinute(30)->toDateTimeString()) {
            Log::notice('Booking Cancellation Time is greater than Check-in date so system not able 
            to Auto Refund Booking Amount after check-in date only voiding its Auth entries, and System assumed 
            4 PM of check-in date for comparison',
                ['Booking Details' => json_encode($this->options), 'File'=>PaymentSettings::class, 'Line Hint'=> 247]);
            return $this->cancelArr;
        }

        if (!is_null($cancelSetting)) {

            $cancelSetting->status = filter_var($cancelSetting->status, FILTER_VALIDATE_BOOLEAN);
            $cancelSetting->afterBookingStatus = filter_var($cancelSetting->afterBookingStatus, FILTER_VALIDATE_BOOLEAN);
            $cancelSetting->beforeCheckInStatus = filter_var($cancelSetting->beforeCheckInStatus, FILTER_VALIDATE_BOOLEAN);
            $cancelSetting->isNonRefundable = (isset($cancelSetting->isNonRefundable) ? filter_var($cancelSetting->isNonRefundable, FILTER_VALIDATE_BOOLEAN) : false );
            $rulesArr = array();

            /* IF Booking Cancellation Policy Is Non-refundAble then no need to refund any thing , Charge Full amount*/
            if ($cancelSetting->isNonRefundable) {
                return array('transactionType' => CancellationSetting::NON_REFUNDABLE_BOOKING, 'amount' => 0 );
            }

            if ($cancelSetting->status) {

                $timeAfterBooking = Carbon::parse($this->options->bookingTime)->addSeconds($cancelSetting->afterBooking)->format('Y-m-d H:i:s');
                $timeBeforeCheckIn = Carbon::parse($this->options->checkInDate)->addSeconds(-($cancelSetting->beforeCheckIn))->format('Y-m-d H:i:s');

                if ($cancelSetting->afterBookingStatus) {
                    $charge = ((($this->options->cancellationTime <= $timeAfterBooking) || ($cancelSetting->afterBooking == 0)) ? false : true);
                    if(!$charge){
                        $this->cancelArr = array('transactionType' => CancellationSetting::TRANSACTION_TYPE_REFUND, 'amount' => $this->options->totalAmount );
                        return $this->cancelArr;
                    } else {
                        $notLieInAnyRefundPolicyCheckForCharge = true; //Else Charge Whole Amount if not any other Custom charge rules defined
                    }
                }

                if ($cancelSetting->beforeCheckInStatus) {
                    $charge = ((($this->options->cancellationTime <= $timeBeforeCheckIn) || ($cancelSetting->beforeCheckIn == 0)) ? false : true);
                    if (!$charge){
                        $this->cancelArr = array('transactionType' => CancellationSetting::TRANSACTION_TYPE_REFUND, 'amount' => $this->options->totalAmount );
                        return $this->cancelArr;
                    } else {
                        $notLieInAnyRefundPolicyCheckForCharge = true; //Else Charge Whole Amount if not any other Custom charge rules defined
                    }
                }
                if (count($cancelSetting->rules) == 0){
                    return $this->cancelArr;
                }

                if(count($cancelSetting->rules) > 0 ){
                    $shouldCharge = 0;
                    foreach ($cancelSetting->rules as $key => $rule) {
                        if ( (($rule['canFee'] === 'first_night') || ($rule['canFee'] > 0)) && ($rule['is_cancelled'] >= 0)) {
                            $timeStr = intval($rule['is_cancelled']);
                            $rulesArr[$timeStr]['percent'] = $rule['canFee'];
                            $rulesArr[$timeStr]['flatFee'] = $rule['is_cancelled_value'];
                        }
                    }

                    if (count($rulesArr) > 0){
                        ksort($rulesArr);
                        $anyTime = (array_key_exists(0,$rulesArr));
                        switch ($anyTime) {
                            case true:

                                if ($rulesArr[0]['percent'] === 'first_night')
                                    $shouldCharge = ($this->cancellationFirstNightAmount() + $rulesArr[0]['flatFee']);
                                else
                                    $shouldCharge = round((($this->options->totalAmount / 100) * $rulesArr[0]['percent']) + $rulesArr[0]['flatFee'], 2);
                                break;

                            default:

                                foreach ($rulesArr as $canceledBefore => $chargePolicy) {
                                    if ($this->options->cancellationTime >= Carbon::parse($this->options->checkInDate)->addSeconds(-($canceledBefore))->format('Y-m-d H:i:s')) {
                                        if ($chargePolicy['percent'] === 'first_night')
                                            $shouldCharge = ($this->cancellationFirstNightAmount() + $chargePolicy['flatFee']);
                                        else
                                            $shouldCharge = round((($this->options->totalAmount/100) * $chargePolicy['percent']) + $chargePolicy['flatFee'],2);

                                        break;
                                    }
                                }
                                break;
                        }
                    } else if (isset($notLieInAnyRefundPolicyCheckForCharge) && $notLieInAnyRefundPolicyCheckForCharge) {
                        $this->cancelArr = array('transactionType' => CancellationSetting::TRANSACTION_TYPE_CHARGE, 'amount' => $this->options->totalAmount );
                    }

                    if ($shouldCharge > 0) {
                        $this->cancelArr = array('transactionType' => CancellationSetting::TRANSACTION_TYPE_CHARGE, 'amount' => $shouldCharge);
                    }
                }
            }
        }
        return $this->cancelArr;
    }

    /**
     * @return CreditCardValidation
     */
    private function getCcvSetting()
    {
        return $this->paymentRules->creditCardValidationSetting(true);
    }

    /**
     * @return float
     */
    private function cancellationFirstNightAmount()
    {
        if($this->options->timeZone != null){
            $checkInDate = \Carbon\Carbon::parse($this->options->checkInDate, 'GMT')->setTimezone($this->options->timeZone)->toDateTimeString();
            $checkOutDate = \Carbon\Carbon::parse($this->options->checkOutDate, 'GMT')->setTimezone($this->options->timeZone)->toDateTimeString();
        }else{
            $checkInDate = $this->options->checkInDate;
            $checkOutDate =$this->options->checkOutDate;
        }

        $date1 = \Carbon\Carbon::createFromFormat('Y-m-d', Carbon::parse($checkInDate)->format('Y-m-d'));
        $date2 = \Carbon\Carbon::createFromFormat('Y-m-d', Carbon::parse($checkOutDate)->format('Y-m-d'));
        $diff_in_days = ($date1->diffInDays($date2) == 0 ? 1 : $date1->diffInDays($date2));
        return round(($this->options->totalAmount / $diff_in_days),2);
    }

    /**
     * @return PaymentSchedule
     */
    private function getPaymentScheduleSetting()
    {
        return $this->paymentRules->paymentScheduleSetting(true);
    }

    /**
     * @return SecurityDamageDeposit
     */
    private function getSecurityDepositSetting()
    {
        return $this->paymentRules->securityDepositSetting(true);
    }


     /*
     *Getting Cancellation settings from booking_info table regarding to cancellation
     * policies applied  at the time of booking received.
     * @return CancellationAmountType|null\
     */
    private function getCancellationSetting()
    {
        $cancellationModel = BookingInfo::where('id' , $this->options->booking_id)->first();
        if(!is_null($cancellationModel))
            return (new CancellationAmountType($cancellationModel->cancellation_settings));
        else
            return null;
    }

    /*
    * if datetime less than booking time then entertain on booking dateTime.
    * Convert to GMT by regarded timezone
    */
    private function isGreaterBookingTime($dueDate)
    {
        return ($dueDate < $this->options->bookingTime ? $this->options->bookingTime : $dueDate);
    }

    private  function toGMT($localDateTime)
    {
        return !empty(($this->options->timeZone))
            ? Carbon::parse($localDateTime, $this->options->timeZone)->setTimezone('GMT')->toDateTimeString()
            : $localDateTime;
    }

    private function CheckAndAdjustBookingTimeConflictWithCheckInDate()
    {
        if ($this->options->checkInDate < $this->options->bookingTime){
            Log::info( 'Booking Time Greater than check-in date. System Assumed  bookingTime as checkInDate.',
                array(
                    'Check-in date' => $this->options->checkInDate,
                    'Booking Time' => $this->options->bookingTime,
                    'Booking_info_id' => ($this->options->booking_id ? $this->options->booking_id : '' ),
                    'property_info_id' => $this->options->property_info_id,
                    'File' => PaymentSettings::class,
                    'Line Hint' => '459',
                )
            );
            /* Set check-in Date as equal to  BookingTime */
            $this->options->checkInDate = $this->options->bookingTime;
            $this->checkInDateInDateFormat = $this->bookingTimeInDateFormat;
        }
    }

    /**
     * IF VC Booking CC Auth not required and check for VC Process Status active in settings.
     * @param \App\System\PMS\Models\Booking $booking
     * @param PaymentSchedule $ps
     */
    private function typeofPaymentSource(\App\System\PMS\Models\Booking $booking, PaymentSchedule &$ps) {
        if ($ps->status
            && ($booking->getTypeofPaymentSource() != BS_Generic::PS_VIRTUAL_CARD)) {
            $ps->status = $ps->onlyVC == false;
        }
    }
}