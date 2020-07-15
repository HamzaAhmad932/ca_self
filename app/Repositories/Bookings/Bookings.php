<?php

namespace App\Repositories\Bookings;

use App\CaCapability;
use App\Events\Emails\EmailEvent;
use App\Jobs\BAChargeJob;
use App\Jobs\ReAttemptJob;
use App\CancellationSetting;
use App\Events\SendEmailEvent;
use App\Services\CapabilityService;
use App\Events\NewGatewaySelectEvent;
use App\Jobs\ReportPMSInvalidCardJob;
use App\Repositories\Settings\CancellationAmountType;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\Models\BookingCard;
use App\UserSettingsBridge;
use \DateTime;
use \DateTimeZone;
use App\SystemJob;
use App\BookingInfo;
use App\UserAccount;
use NumberFormatter;
use App\PropertyInfo;
use App\CreditCardInfo;
use App\System\PMS\PMS;
use App\SystemJobDetail;
use App\BookingSourceForm;
use App\Mail\GenericEmail;
use App\UserPaymentGateway;
use Illuminate\Support\Carbon;
use App\CreditCardAuthorization;
use Yajra\Datatables\Datatables;
use App\System\PMS\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\System\PMS\Models\PmsOptions;
use App\Listeners\PMSPreferencesListener;
use App\System\PaymentGateway\Models\Card;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\Settings\ParsePreferencesWithTemplateVar;
use App\Repositories\BookingSources\BookingSources;

class Bookings
{
    private $user_account_id;
    public static $bookingSourceChatStatus;
    public $last_error = null;

    public function __construct($user_account_id)
    {
        $this->user_account_id = $user_account_id;
    }

    public function booking_detail($id)
    {

        $booking_detail_id = $id;

        $user_account_id = $this->user_account_id; //auth()->user()->user_account_id;

        $user_account = UserAccount::find($user_account_id);

        $b_detail = $user_account->bookings_info->where('id', $booking_detail_id)->first();

        $bs = BookingSourceForm::where('channel_code',$b_detail->channel_code)->first();

        if($bs != null)
            $bs = $bs->name;
        else
            $bs = '';

        $b_payment = $user_account->transactions_init->where('booking_info_id', $booking_detail_id)->whereIn('type', ['C', 'M', 'R']);

        $b_logs = $user_account->transactions_detail->where('transaction_init_id', $booking_detail_id);

        $b_chat = $user_account->messages->where('booking_info_id', $booking_detail_id)->sortByDesc('id');


        $data = array('bs' => $bs ,'b_detail' => $b_detail, 'b_payment' => $b_payment, 'b_chat' => $b_chat, 'b_logs' => $b_logs, 'user_account' => $user_account);

        return  $data;
    }
    public function bookings_short_description($id)
    {

        $booking_detail_id = $id;
        $user_account_id = $this->user_account_id; //auth()->user()->user_account_id;

        $user_account = UserAccount::find($user_account_id);
        $paymentType = resolve(PaymentTypeMeta::class);
        $autoChargeTransType = array($paymentType->getBookingPaymentAutoCollectionFull(),
            $paymentType->getBookingPaymentAutoCollectionPartial1of2(),
            $paymentType->getBookingPaymentAutoCollectionPartial2of2(), );
        $b_paymentArr = [];

        $cc_info_id = 0;
        $bookingInfo = $user_account->bookings_info->where('id',$booking_detail_id)->first();
        $cc_info = $bookingInfo->cc_Infos->last();
        $ccLast4Digits = '';
        $style = false;

        //for all other types of booking we had stored 0 as booking_source_form
        if (CapabilityService::isAnyPaymentOrSecuritySupported($bookingInfo)) {
            if($bookingInfo->is_vc != 'BT') {
                if($cc_info != null && !empty($cc_info)){
                    if(($cc_info->customer_object->token != '')){
                        $ccLast4Digits = '**'.$cc_info->cc_last_4_digit;
                        $style = false;
                    }else{
                        if($cc_info->cc_last_4_digit != ''){
                            $ccLast4Digits = '**'.$cc_info->cc_last_4_digit;
                            $style = false;
                        }else{
                            $ccLast4Digits = 'Card Details Invalid';
                            $style = true;
                        }
                    }
                }else{
                    $ccLast4Digits = 'Processing..';
                }
            }
        }
        else
            $ccLast4Digits = 'Not Supported'; //if booking is not from supported channels the we don't need to show payment details        

        if(!empty($cc_info->id)){
            $cc_info_id = $cc_info->id;
        }else{
            $cc_info_id = 0;
        }

        $ccValidationType = $paymentType->getAuthTypeCCValidation();
        $securityIdArr = [$paymentType->getAuthTypeSecurityDamageValidation(), $paymentType->getSecurityDepositManualAuthorize()];
        $cc_auth = CreditCardAuthorization::where('cc_info_id',$cc_info_id)->get();

        $b_auth = $cc_auth->where('type', $ccValidationType)->first();

        if($b_auth == null) {
            $b_auth = 'khali';

        } elseif (!isset($b_auth->hold_amount)) {
            $b_auth = 'khali';
        }

        $b_sdd = $cc_auth->whereIn('type', $securityIdArr)->first();

        if($b_sdd == null) {
            $b_sdd = 'khali';

        } elseif (!isset($b_sdd->hold_amount)) {
            $b_sdd = 'khali';
        }

        $b_payment = $user_account->transactions_init->where('booking_info_id', $booking_detail_id)->whereIn('transaction_type', $autoChargeTransType);
        foreach ($b_payment as $key => $transactionInit) {
            $b_paymentArr[] = ['due_date' =>  $transactionInit->due_date, 'price'=>  $transactionInit->price, 'split' =>  $transactionInit->split, 'payment_status'=>  $transactionInit->payment_status,];
        }

        $total = $b_payment->sum('price');
        $total = ($total == 0 ? 'khali' : $total);

        $currency_code = $this->getCurrencySymbolByCurrencyCode($this->getCurrencyCode($bookingInfo));

        $data = array(
            'b_payment' => $b_paymentArr,
            'total' =>  $total,
            'b_auth' => $b_auth,
            'b_sdd' =>  $b_sdd ,
            'currency_code' => $currency_code,
            'ccLast4Digits' => $ccLast4Digits,
            'style' => $style,
            'time_zone'=> $bookingInfo->property_info->time_zone);
        return  $data;
    }


    /**
     * @return Collection
     */

    private function bookings_list()
    {

        set_sql_mode('ONLY_FULL_GROUP_BY');

        $booking_list = DB::table('booking_infos')
            ->join('property_infos', 'booking_infos.property_id', '=', 'property_infos.pms_property_id')
            ->join('booking_source_forms', 'booking_infos.channel_code', '=', 'booking_source_forms.channel_code')
            ->where('booking_infos.user_account_id', $this->user_account_id)->where('property_infos.user_account_id', $this->user_account_id)
            ->select(
                'booking_infos.id',
                'booking_infos.pms_booking_id',
                'booking_infos.guest_name',
                'property_infos.name',
                'pms_property_id',
                'property_infos.time_zone',
                'property_infos.currency_code',
                'booking_infos.user_account_id',
                'booking_infos.guest_email',
                'booking_infos.booking_time',
                'booking_infos.check_in_date',
                'booking_infos.check_out_date',
                'property_infos.pms_property_id',
                'booking_source_forms.name as bs_name',
                'booking_infos.is_vc',
                'booking_infos.guest_country',
                'booking_infos.pms_booking_status',
                'booking_infos.total_amount',
                'booking_infos.guest_currency_code',
                DB::raw('(Select hold_amount  from credit_card_authorizations where credit_card_authorizations.booking_info_id = booking_infos.id AND type IN (19,20) limit 1 ) as security_auth_amount'),
                DB::raw('(Select status  from credit_card_authorizations where credit_card_authorizations.booking_info_id = booking_infos.id AND type IN (19,20) limit 1 ) as security_auth'),
                DB::raw('(Select COUNT(id)  from transaction_inits where transaction_inits.booking_info_id =  booking_infos.id AND type = "C" AND payment_status = 1 ) as success'),
                DB::raw('(Select COUNT(id) from transaction_inits where transaction_inits.booking_info_id =  booking_infos.id AND type = "C" AND payment_status = 2 ) as schedule'),
                DB::raw('(Select COUNT(id)  from transaction_inits  where transaction_inits.booking_info_id =  booking_infos.id AND type = "C" AND payment_status IN (0, 4) ) as failed'),
                DB::raw('(Select customer_object from credit_card_infos where  credit_card_infos.booking_info_id = booking_infos.id order by id desc limit 1) as customer_object'),
                DB::raw('false as flag'),
                DB::raw('(Select COUNT(id)  from guest_images where guest_images.booking_id = booking_infos.id AND status IN (0, 1, 2)) as id_required'),
                DB::raw('(Select COUNT(id)  from guest_images where guest_images.booking_id = booking_infos.id AND status = 1) as id_verified'),
                DB::raw('(Select COUNT(id)  from guest_images where guest_images.booking_id = booking_infos.id AND status = 2) as id_rejected')
            )->orderBy('booking_infos.id', 'desc')->groupBy('booking_infos.pms_booking_id')->get();

        /* Addition of color FLAG for bookings that have some issues with payments */
        return $booking_list;
    }

    /**
     * No need to pass any parameter if all bookings are fetching,
     * Only in case of fetching or drawing PaymentGateway Effected Bookings pass desired arguments
     *
     * @param bool $gatewayChangeEffectedBookings
     * @param null $EffectedBookingsPropertyInfoId
     * @return \Illuminate\Http\JsonResponse
     */

    public function booking_data($gatewayChangeEffectedBookings = false, $EffectedBookingsPropertyInfoId = null)
    {
        $booking_list = $this->bookings_list();

        if (($gatewayChangeEffectedBookings == true) && ($EffectedBookingsPropertyInfoId != null)){

            $gatewayEffectedBookingsIds = $this->effected_bookings(UserAccount::find($this->user_account_id), $EffectedBookingsPropertyInfoId)->pluck('id')->toArray();
            $booking_list = $booking_list->whereIn('id', $gatewayEffectedBookingsIds);
        }

        foreach($booking_list as $k => $b_info) {

            $checkFlag = true;
            $needToCheckCC = true;

            if ($b_info->is_vc == BS_Generic::PS_BANK_TRANSFER) {
                $needToCheckCC  = ($b_info->security_auth_amount != null ? true : false);
                if (!$needToCheckCC){
                    $booking_list[$k]->flag = 0;
                    $checkFlag = false;
                }
            }

            if (($b_info->is_vc != BS_Generic::PS_VIRTUAL_CARD) && $needToCheckCC) {
                $customerObject = json_decode($b_info->customer_object);
                if (($customerObject == null) || $customerObject->token == '') {
                    $booking_list[$k]->flag = 4;
                    $checkFlag = false;
                }
            }

            if ($checkFlag) {

                if (($b_info->success == 0) && ($b_info->schedule == 0) && ($b_info->failed == 0)) {

                    $booking_list[$k]->flag = 0;
                } else if (($b_info->failed > $b_info->success) || ($b_info->failed > $b_info->schedule)) {

                    $booking_list[$k]->flag = 3;
                } else if (($b_info->success > $b_info->failed) || ($b_info->success >= $b_info->schedule)) {

                    $booking_list[$k]->flag = 1;
                } else if (($b_info->schedule > $b_info->success) && ($b_info->schedule > $b_info->failed)) {

                    $booking_list[$k]->flag = 2;
                } else{

                    $booking_list[$k]->flag = 0;
                }
            }

            $currency_code = ( (($booking_list[$k]->guest_currency_code != null) && (!empty($booking_list[$k]->guest_currency_code))) ? $booking_list[$k]->guest_currency_code : $booking_list[$k]->currency_code);
            $booking_list[$k]->guest_currency_code = $this->getCurrencySymbolByCurrencyCode($currency_code);

            unset($b_info->success);
            unset($b_info->schedule);
            unset($b_info->failed);
            unset($b_info->customer_object);
        }
        $d = new Datatables();
        $c = $d->collection($booking_list);
        return $c->make(true);
    }

    /**
     * @param $property_info_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function effectedBooking_data($property_info_id)
    {
        return $this->booking_data(true, $property_info_id);
    }


    /**
     * property_info_id => 0 for global paymentGateway changed.
     * property_info_id => $property_info_id for Local paymentGateway changed on specific property
     *
     * @param UserAccount $userAccount
     * @param $property_info_id
     * @return App/BooKingInfo Object
     */
    public function effectAble_bookings(UserAccount $userAccount, $property_info_id)
    {
        /* Bookings that will be effected after changing Payment Processor */
        if($property_info_id != 0)
            return $userAccount->bookings_info->where('property_id' , $userAccount->properties_info->where('id', $property_info_id)->first()->pms_property_id)->where('payment_gateway_effected', 0);
        else{
            return $userAccount->bookings_info->whereIn('property_id' , $userAccount->properties_info->where('use_pg_settings' , 0)->pluck('pms_property_id')->toArray())->where('payment_gateway_effected', 0);
        }

    }

    /**
     * $property_info_id = 0 for global paymentGatewat changed.
     * $property_info_id => property_info_id for Local paymentGatewat changed on specifc property
     *
     * @param UserAccount $userAccount
     * @param $property_info_id
     * @return App/BooKingInfo Object
     */

    /* Bookings effected after changing Payment Processor */
    public function effected_bookings(UserAccount $userAccount, $property_info_id)
    {
        if($property_info_id != 0)
            return $userAccount->bookings_info->where('property_id' , $userAccount->properties_info->where('id', $property_info_id)->first()->pms_property_id)->where('payment_gateway_effected', 1);
        else{
            return $userAccount->bookings_info->whereIn('property_id' , $userAccount->properties_info->where('use_pg_settings' , 0)->pluck('pms_property_id')->toArray())->where('payment_gateway_effected', 1);
        }
    }

    public function voidPGEffectedBookings(UserAccount $userAccount, $property_info_id, UserPaymentGateway $userPaymentGateway)
    {

        $bookingInfos = $this->effectAble_bookings($userAccount, $property_info_id);

        if($bookingInfos->count() > 0 && !is_null($bookingInfos)){

            $effected_booking_list = '<table>
                                        <tr>
                                            <th>PMS Booking ID</th>
                                            <th>Guest Email</th>
                                        </tr>';

            foreach($bookingInfos as $bookingInfo){

                $bookingInfo->payment_gateway_effected = 1;
                $bookingInfo->payment_gateway_object = json_encode($userPaymentGateway);
                $bookingInfo->save();

                $effected_booking_list .= '<tr>
                                               <td>' . $bookingInfo->pms_booking_id . '</td>
                                               <td>' . $bookingInfo->guest_email . '</td>
                                           </tr>';

                $transactionInits = $bookingInfo->transaction_init;
                foreach ($transactionInits as $transactionInit) {
                    $transactionInit->lets_process = 0;
                    $transactionInit->remarks = 'Lets process voided, Property Payment Processor Changed';
                    $transactionInit->save();
                }

                foreach ($bookingInfo->cc_Infos as  $cc_Info) {

                    foreach ($cc_Info->ccauth->whereIn('status' ,[0,4]) as  $auth) {
                        $auth->status = 3;
                        $auth->remarks = 'Voided, Property Payment Processor Changed';
                        $auth->save();
                    }
                }
            }

            $effected_booking_list .= '</table>';
            return $effected_booking_list;
        }
        return null;
    }


    /**
     * This function returns Currency Code, And takes 2 parameters, BookingInfo can't be null, but if PropertyInfo
     * Object is present then pass it also, otherwise leave it.
     *
     * @param BookingInfo $bookingInfo
     * @param PropertyInfo|null $propertyInfo
     * @return string Currency Code to use.
     */
    public function getCurrencyCode(BookingInfo $bookingInfo, PropertyInfo $propertyInfo = null) {

        if(isset($bookingInfo->guest_currency_code) && $bookingInfo->guest_currency_code != null && $bookingInfo->guest_currency_code != '') {
            return $bookingInfo->guest_currency_code;

        } elseif($propertyInfo != null) {
            return $propertyInfo->currency_code;

        } else {
            $property = PropertyInfo::where('user_account_id', $this->user_account_id)
                ->where('pms_property_id', $bookingInfo->property_id)
                ->where('pms_id', $bookingInfo->pms_id)
                ->first();
            if($property != null)
                return $property->currency_code;
            else
                return '';
        }

    }


    public function getCurrencySymbolByCurrencyCode($currencyCode)
    {
        return Bookings::CurrencySymbolByCurrencyCode($currencyCode);

    }

    public static function CurrencySymbolByCurrencyCode($currencyCode)
    {

        try{

            $locale= config('app.locale');
            $currency= $currencyCode;
            $fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
            $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
            return $symbol;

        }catch(\Exception $e){
            Log::error($e->getMessage(),['File'=>Bookings::class]);

        }
        return $currencyCode;
    }

    /**
     *  * This Helper function returns Time Zone And GMT Columns Values for Booking_info Entry
     * @param UserAccount $userAccount
     * @param PropertyInfo $propertyInfo
     * @param $bookingTimeGmt
     * @param $checkInDateLocal
     * @param $checkOutDateLocal
     * @param $modifiedTimeGmt
     * @return array
     */

    public function setBABookingInfoTimeZoneColm(UserAccount $userAccount, PropertyInfo $propertyInfo, $bookingTimeGmt,
                                                 $checkInDateLocal, $checkOutDateLocal, $modifiedTimeGmt) {

        try{
            $timeZone = (!is_null($propertyInfo->time_zone) ? $propertyInfo->time_zone : $userAccount->time_zone);
            return array('booking_time'=> $bookingTimeGmt, 'pms_booking_modified_time' => $modifiedTimeGmt,
                'check_in_date' => Carbon::parse($checkInDateLocal, $timeZone)->setTimezone('GMT')->toDateTimeString(),
                'check_out_date'=> Carbon::parse($checkOutDateLocal, $timeZone)->setTimezone('GMT')->toDateTimeString(),
                'property_time_zone' => $timeZone);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }


    /**
     * This Helper function returns check-in date time with property regarding Time Zone
     *
     * @param UserAccount $userAccount
     * @param PropertyInfo
     * @param String guestCommentCheckInDate
     * @return String
     */

    public function setVcDueDateWithTimeZone(UserAccount $userAccount, PropertyInfo $propertyInfo, $guestCommentCheckInDate){

        try{
            $timeZone = (!is_null($propertyInfo->time_zone) ? $propertyInfo->time_zone : $userAccount->time_zone);
            return  Carbon::parse($guestCommentCheckInDate, $timeZone)->setTimezone('GMT')->toDateTimeString();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * This Helper function convert local datetime with regarding Time Zone to GMT
     *
     * @param string $dateTimLocal
     * @param string $timeZone
     * @return string datetime
     */

    public function convertToGMT(string $dateTimLocal, string $timeZone){

        try{

            return Carbon::parse($dateTimLocal, $timeZone)->setTimezone('GMT')->toDateTimeString();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * This Helper function is for Booking Automation bookings to add Specific Hours to Local Hotel checkin-time
     *
     * @param string checkInDateTimeLocal
     * @return string datetime
     */

    public function addCheckInHours(string $checkInDateTimeLocal){
        try{

            return Carbon::parse($checkInDateTimeLocal)->addHours('+1')->toDateTimeString();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }


    /**
     * This Helper function is for Booking Automation bookings to add Specific Hours to Local Hotel checkOut-time
     *
     * @param string checkOutDateTimeLocal
     * @return string datetime
     */

    public function addCheckOutHours(string $checkOutDateTimeLocal){
        try{

            return Carbon::parse($checkOutDateTimeLocal)->addHours('+11')->toDateTimeString();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * This Helper function returns signed diff in hours regarding to param Time Zone
     * @param String timeZone
     * @return Signed Float
     */

    private function getDiffTimeZone($timeZone){

        // $hours = Carbon::createFromTimestamp(0, $timeZone)->offsetHours;/
        // Carbon::parse('2019-04-29 00:30:00', 'Asia/Karachi')->setTimezone('GMT')->toDateTimeString();

        $c = Carbon::parse(Carbon::now(), 'GMT')->setTimezone($timeZone);
        $hours = $c->offsetHours;

        $zone = sprintf('%0.2f', $hours);
        $str_arr = explode('.',$zone);
        $str_arr[1] = intval(($str_arr[1]/100)*60);
        $propertyTimeZone = implode('.', $str_arr);
        $propertyTimeZone = ($propertyTimeZone > -1 ? '+'.$propertyTimeZone : $propertyTimeZone);
        return ($propertyTimeZone < 0 ? str_replace('-','+',$propertyTimeZone) : str_replace('+','-',$propertyTimeZone));
    }


    /**
     * This Helper function returns DatetTime with property regarding Time Zone
     *
     * @param UserAccount $userAccount
     * @param PropertyInfo
     * @param String dateTimeToConvert
     * @return String
     */


    public function getPropertyTimezone(UserAccount $userAccount, PropertyInfo $propertyInfo = null){

        try{

            if($propertyInfo == null)
                if(empty($userAccount->time_zone))
                    return 'GMT';
                else
                    return $userAccount->time_zone;

            $timeZone = (!is_null($propertyInfo->time_zone) ? $propertyInfo->time_zone : $userAccount->time_zone);
            return $timeZone;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return 'GMT';
    }

    public function getPropertyTimezoneConvertedDatetime(UserAccount $userAccount, PropertyInfo $propertyInfo, string $dateTimeToConvert){

        $timeZone = (!is_null($propertyInfo->time_zone) ? $propertyInfo->time_zone : $userAccount->time_zone);
        return Carbon::parse($dateTimeToConvert, 'GMT')->setTimezone($timeZone)->toDateTimeString();
    }


    public function getBookingPaymentAndCCAuthCombined(Collection $transaction_init, Collection $cc_auth){
        $trans = $transaction_init->toArray();
        $auths = $cc_auth->toArray();
        $transNew = array();
        $count = 0;

        foreach($trans as $key => $tran){
            foreach($auths as $key2 => $auth){
                $transNew[$count]['id'] =  $auth['id'];
                $transNew[$count]['price'] =  $auth['hold_amount'];
                $transNew[$count]['transaction_type'] = $auth['type'];
                $transNew[$count]['status'] = $auth['status'];
                $transNew[$count]['due_date'] = $auth['due_date'];
                $transNew[$count]['captured'] = $auth['captured'];
                $transNew[$count]['isAuth'] = true;
                $transNew[$count]['payment_status'] = $auth['status'];
                $transNew[$count]['lets_process'] = -1;
                $transNew[$count]['type'] = -1;
                $transNew[$count]['client_remarks'] = '';
                $transNew[$count]['booking_info_id'] = $auth['booking_info_id'];
                $count++;
                unset($auths[$key2]);
            }
            $transNew[$count]['id'] =  $tran['id'];
            $transNew[$count]['price'] =  $tran['price'];
            $transNew[$count]['transaction_type'] = $tran['transaction_type'];
            $transNew[$count]['status'] = $tran['status'];
            $transNew[$count]['due_date'] = $tran['due_date'];
            $transNew[$count]['isAuth'] = false;
            $transNew[$count]['captured'] = 0;
            $transNew[$count]['payment_status'] = $tran['payment_status'];
            $transNew[$count]['lets_process'] = $tran['lets_process'];
            $transNew[$count]['type'] = $tran['type'];
            $transNew[$count]['client_remarks'] = $tran['client_remarks'];
            $transNew[$count]['booking_info_id'] = $tran['booking_info_id'];
            $count++;

        }
        foreach($auths as $key2 => $auth){
            $transNew[$count]['id'] =  $auth['id'];
            $transNew[$count]['price'] =  $auth['hold_amount'];
            $transNew[$count]['transaction_type'] = $auth['type'];
            $transNew[$count]['status'] = $auth['status'];
            $transNew[$count]['due_date'] = $auth['due_date'];
            $transNew[$count]['captured'] = $auth['captured'];
            $transNew[$count]['isAuth'] = true;
            $transNew[$count]['payment_status'] = $auth['status'];
            $transNew[$count]['lets_process'] = -1;
            $transNew[$count]['type'] = -1;
            $transNew[$count]['client_remarks'] = '';
            $transNew[$count]['booking_info_id'] = $auth['booking_info_id'];
            $count++;
        }
        return $transNew;
    }
    public function getBookingPaymentAndCCAuthLogs(Array $trans_logs, Array $cc_logs, Array $refund_logs){
        // dd(['t'=>$trans_logs, 'a'=>$cc_logs]);
        $combined = [];
        $count = 0;
        foreach($cc_logs as $key1 => $c_log){
            foreach($trans_logs as $key2 => $t_logs){
                $combined[$count]['id'] = $t_logs['id'];
                $combined[$count]['refer_id'] = $t_logs['transaction_init_id'];
                $combined[$count]['payment_gateway'] = $t_logs['payment_gateway'];
                $combined[$count]['booking_source'] = $t_logs['booking_source'];
                $combined[$count]['client_remarks'] = $t_logs['client_remarks'];
                $combined[$count]['payment_status'] = $t_logs['payment_status'];
                $combined[$count]['charge_ref_no'] = $t_logs['charge_ref_no'];
                $combined[$count]['amount'] = $t_logs['amount'];
                $combined[$count]['error_msg'] = $t_logs['error_msg'];
                $combined[$count]['created_at'] = $t_logs['created_at'];
                $combined[$count]['isAuth'] = false;
                $count++;
                unset($trans_logs[$key2]);
            }
            $combined[$count]['id'] = $c_log['id'];
            $combined[$count]['refer_id'] = $c_log['cc_auth_id'];
            $combined[$count]['payment_gateway'] = $c_log['payment_gateway'];
            $combined[$count]['booking_source'] = $c_log['booking_source'];
            $combined[$count]['client_remarks'] = $c_log['client_remarks'];
            $combined[$count]['payment_status'] = $c_log['payment_status'];
            $combined[$count]['charge_ref_no'] = $c_log['charge_ref_no'];
            $combined[$count]['amount'] = $c_log['amount'];
            $combined[$count]['error_msg'] = $c_log['error_msg'];
            $combined[$count]['created_at'] = $c_log['lated_at'];
            $combined[$count]['isAuth'] = true;
            $count++;

            foreach($refund_logs as $key4 => $r_log){

                $combined[$count]['id'] = $r_log['id'];
                $combined[$count]['refer_id'] = $r_log['transaction_init_id'];
                $combined[$count]['payment_gateway'] = $r_log['payment_gateway'];
                $combined[$count]['booking_source'] = $r_log['booking_source'];
                $combined[$count]['client_remarks'] = $r_log['client_remarks'];
                $combined[$count]['payment_status'] = $r_log['payment_status'];
                $combined[$count]['charge_ref_no'] = $r_log['charge_ref_no'];
                $combined[$count]['amount'] = $r_log['amount'];
                $combined[$count]['error_msg'] = '';
                $combined[$count]['created_at'] = $r_log['created_at'];
                $combined[$count]['isAuth'] = false;
                $count++;
                unset($refund_logs[$key4]);
            }
        }
        foreach($refund_logs as $key5 => $r_log){

            $combined[$count]['id'] = $r_log['id'];
            $combined[$count]['refer_id'] = $r_log['transaction_init_id'];
            $combined[$count]['payment_gateway'] = $r_log['payment_gateway'];
            $combined[$count]['booking_source'] = $r_log['booking_source'];
            $combined[$count]['client_remarks'] = $r_log['client_remarks'];
            $combined[$count]['payment_status'] = $r_log['payment_status'];
            $combined[$count]['charge_ref_no'] = $r_log['charge_ref_no'];
            $combined[$count]['amount'] = $r_log['amount'];
            $combined[$count]['error_msg'] = '';
            $combined[$count]['created_at'] = $r_log['created_at'];
            $combined[$count]['isAuth'] = false;
            $count++;
        }

        foreach($trans_logs as $key3 => $t_logs){
            $combined[$count]['id'] = $t_logs['id'];
            $combined[$count]['refer_id'] = $t_logs['transaction_init_id'];
            $combined[$count]['payment_gateway'] = $t_logs['payment_gateway'];
            $combined[$count]['booking_source'] = $t_logs['booking_source'];
            $combined[$count]['client_remarks'] = $t_logs['client_remarks'];
            $combined[$count]['payment_status'] = $t_logs['payment_status'];
            $combined[$count]['charge_ref_no'] = $t_logs['charge_ref_no'];
            $combined[$count]['amount'] = $t_logs['amount'];
            $combined[$count]['error_msg'] = $t_logs['error_msg'];
            $combined[$count]['created_at'] = $t_logs['created_at'];
            $combined[$count]['isAuth'] = false;
            $count++;
        }

        return $combined;
    }

    /**
     *
     * This function checks booking typeOfPaymentSource
     * if !BT then check its card in booking object if card not available in booking object
     * then check token available and get card from PMS.
     *
     * @param UserAccount $userAccount
     * @param PMS $pms
     * @param PmsOptions $pmsOption
     * @param Booking $booking
     * @return Card
     */
    public static function BA_GetCard(UserAccount $userAccount, PMS $pms, PmsOptions $pmsOption, Booking &$booking)
    {
        $card = new  Card();

        $card->address1 = $booking->guestAddress;
        $card->city = $booking->guestCity;
        $card->setCountry($booking->guestCountry);
        $card->postalCode = $booking->guestPostcode;
        $card->phone = empty($booking->guestPhone) ? $booking->guestMobile : $booking->guestPhone;

        if($card->phone != null)
            $card->phone = str_replace(' ', '', $card->phone);

        $card->type = config('db_const.credit_card_infos.type.empty');
        /**
         * @var $getCard BookingCard
         */
        $getCard = null;
        try{
            // Check Booking Type
            if ($booking->getTypeofPaymentSource() != BS_Generic::PS_BANK_TRANSFER) {
                if (($pmsOption->bookingToken != null) && ($pmsOption->bookingToken != '')) {
                    try{
                        //Get Card from PMS, Against Token Received with Booking notify Msg
                        $getCard = $pms->fetch_card_for_booking($pmsOption)[0]; /* function return Object inside an array */
                        $card->cardNumber =  $getCard->cardNumber;
                        $booking->cardNumber =  $getCard->cardNumber;
                        $booking->cardExpire = $getCard->cardExpire;
                        $booking->cardName =  $getCard->cardName;
                        $booking->cardType = $getCard->cardType;

                        if (($getCard->cardCvv != null) && ($getCard->cardCvv != '')) {
                            $card->cvvCode = $getCard->cardCvv;
                            $booking->cardCvv = $getCard->cardCvv;
                        } else if (($pmsOption->cardCvv != null) && ($pmsOption->cardCvv != '')) {
                            $booking->cardCvv = $pmsOption->cardCvv;
                            $card->cvvCode = $pmsOption->cardCvv;
                        } else {
                            $card->cvvCode = $booking->cardCvv;
                        }
                        $card->type = config('db_const.credit_card_infos.type.credit_card_found');
                    } catch (PmsExceptions $e) {
                        Log::error($e->getMessage(),
                            [
                                'file' => $e->getFile(),
                                'Line' => $e->getLine(),
                                'UserAccountId' => $userAccount->id,
                                'BookingPmsId' => $booking->id,
                                'Stack' => $e->getTraceAsString(),
                            ]);
                    }
                } else {
                    $card->cardNumber = $booking->cardNumber;
                    $card->cvvCode = $booking->cardCvv;
                    $card->type = config('db_const.credit_card_infos.type.token_not_received');
                    if(empty($booking->cardNumber))
                        $card->type = config('db_const.credit_card_infos.type.missing_card_number');
                }

                // Implode Or Explode Card Expires
                $cardExpire = $booking->getExpiryMonthAndYear();
                $card->firstName = $booking->getCardFirstName();
                $card->lastName = $booking->getCardLastName();

                /**
                 * BA added these Derived Expiry Month and Year parameter later own, so we are checking if they exist
                 * then we use them else we keep on using old pattern.
                 *
                 * At First Card details were found in Booking Details API.
                 * Then it was removed and a dedicated API was created by BA (But old BA account still get card details in Booking Details API)
                 * Then BA added derivedExpireMonth and derivedExpireYear parameters in new card API to streamline expiry month and year,
                 *      as earlier to these parameters card expiry varied according to booking source.
                 */
                if(!empty($getCard) && !empty($getCard->derivedExpireMonth) && !empty($getCard->derivedExpireYear)) {
                    $card->expiryYear = $getCard->derivedExpireYear;
                    $card->expiryMonth = $getCard->derivedExpireMonth;

                } else {
                    $card->expiryYear = $cardExpire['year'];
                    $card->expiryMonth = $cardExpire['month'];
                }

                $card->eMail = $booking->guestEmail;

                $card->metadata = ['Booking ID' => $booking->id, 'First Name' => $card->firstName, 'Last Name' => $card->lastName];
                $card->statement_descriptor = "B ID $booking->id";
                $gFullName = $booking->guestFirstName != null ? $booking->guestFirstName : '';
                $gFullName .= strlen($gFullName) > 0 ? ' ' : '';
                $gFullName .= $booking->guestLastName != null ? $booking->guestLastName : '';
                $card->general_description = "$gFullName   Booking ID $booking->id";
            }
        } catch (\Exception $e){
            Log::error($e->getMessage(), [
                'file'=>$e->getFile(),
                'Line'=>$e->getLine(),
                'UserAccountId' => $userAccount->id,
                'BookingPmsId' => $booking->id,
                'Stack'=>$e->getTraceAsString()]);
        }
        return $card;
    }

    /**
     *  set updating data regarding to user Preferences
     *
     * @param array $preferencesDataObject
     * @param $pms_property_id
     * @return Booking
     */

    public static function setPreferencesDataToUpdate(array $preferencesDataObject, $pms_property_id){

        /**
         * @var Booking
         */
        $bookingToUpdateData = new Booking();

        if (($preferencesDataObject != null) && (count($preferencesDataObject) > 0)) {

            $bookingToUpdateData->propertyId = $pms_property_id;             /* PMS Property ID  */
            $bookingToUpdateData->id = $preferencesDataObject['bookingID'];  /* pms_booking_id  */

            if (isset($preferencesDataObject['booking_status'])  && ($preferencesDataObject['booking_status'] != 'Unchanged')  && ($preferencesDataObject['booking_status'] != 'unchanged')) {
                $bookingToUpdateData->bookingStatus = $preferencesDataObject['booking_status'];
                $bookingToUpdateData->adjustBookingStatusForXmlTextToInteger();
            }

            if (isset($preferencesDataObject['guestTitle']) && ($preferencesDataObject['guestTitle'] != null))
                $bookingToUpdateData->guestTitle = $preferencesDataObject['guestTitle'];

            if (isset($preferencesDataObject['guestFirstName']) && ($preferencesDataObject['guestFirstName'] != null))
                $bookingToUpdateData->guestFirstName = $preferencesDataObject['guestFirstName'];

            if (isset($preferencesDataObject['guestLastName']) && ($preferencesDataObject['guestLastName'] != null))
                $bookingToUpdateData->guestLastName = $preferencesDataObject['guestLastName'];

            if (isset($preferencesDataObject['notes']) && ($preferencesDataObject['notes'] != null))
                $bookingToUpdateData->notes = $preferencesDataObject['notes'];

            if (isset($preferencesDataObject['flag_color']) && ($preferencesDataObject['flag_color'] != null))
                $bookingToUpdateData->flagColor = str_replace('#','',$preferencesDataObject['flag_color']);

            if (isset($preferencesDataObject['flag_text']) && ($preferencesDataObject['flag_text'] != null))
                $bookingToUpdateData->flagText = $preferencesDataObject['flag_text'];

            /**
             * If Reporting PMS for InValid Card on any first Customer Object creation failed / any Failed transaction / any first auth
             */

            if (isset($preferencesDataObject['bookingInvalidCard']) && ($preferencesDataObject['bookingInvalidCard'] == true))
                $bookingToUpdateData->bookingInvalidCard = true;

            if (isset($preferencesDataObject['preferenceFormId'])) {

                switch ($preferencesDataObject['preferenceFormId']) {
                    case config('db_const.user_preferences.preferences.PAYMENT_SUCCESS'):

                        if (isset($preferencesDataObject['invoiceArr']['invoice']) && (count($preferencesDataObject['invoiceArr']['invoice']) > 0)) {
                            $bookingToUpdateData->invoice = $preferencesDataObject['invoiceArr']['invoice'];
                            $bookingToUpdateData->infoItems = [$preferencesDataObject['invoiceArr']['infoItems']];
                        }
                        break;
                }
            }
        }
        return $bookingToUpdateData;
    }

    /**
     * Update PMS, regarding to System Pending jobs
     * return false if next job with this userAccount is not processable.
     * @param SystemJob $systemJob
     * @param $bookingToUpdateData
     * @return bool
     */
    public static function updatePMSPreferencesWithSystemJob(SystemJob $systemJob, $bookingToUpdateData){

        $status = SystemJob::STATUS_PENDING;

        try{

            $userAccount = UserAccount::find($systemJob->user_account_id);

            if (!Bookings::isUserAccountActiveToProceedSystemJob($userAccount, $systemJob)){
                return  false;
            }

            $bookingInfo = $userAccount->bookings_info->where('id', $systemJob->booking_info_id)->first();
            $propertyInfo = $userAccount->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();

            $pms = new PMS($userAccount);

            $pmsOptions = new PmsOptions();
            $pmsOptions->propertyID = $propertyInfo->pms_property_id;
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

            if (isset($bookingToUpdateData->bookingInvalidCard) && ($bookingToUpdateData->bookingInvalidCard == true))
                $pmsOptions->bookingInvalidCard = true;

            /**
             * Fetch Current Booking Details From PMS To verify Booking and to get previous notes to concat new Notes String
             */
            $bookingDetailsOnPMS = $pms->fetch_Booking_Details($pmsOptions);

            if (count($bookingDetailsOnPMS) > 0) {

                if(isset($bookingToUpdateData->notes) && ($bookingToUpdateData->notes != null))
                    $bookingToUpdateData->notes = $bookingDetailsOnPMS[0]->notes."\n \n".$bookingToUpdateData->notes;
                
                $response = $pms->update_booking($pmsOptions, $bookingToUpdateData);

                // Log::notice('Update PMS Response', ['response' => json_encode($response) ,'System_job' => json_encode($systemJob), 'File' => Bookings::class ]);

                if($pmsOptions->bookingInvalidCard) {
                    /**
                     * update booking Info / dn't Report Again.
                     */
                    $bookingInfo->is_pms_reported_for_invalid_card = 1;

                    $bookingInfo->card_invalid_report_time = empty($bookingInfo->card_invalid_report_time)
                        ? now()->toDateTimeString() : $bookingInfo->card_invalid_report_time;

                    $bookingInfo->save();
                }

                    /**
                     * On Job Completed Remove Records from DB
                     */
                    $status = SystemJob::STATUS_COMPLETED;

                    SystemJobDetail::where('system_job_id', $systemJob->id)->forceDelete();
                    $systemJob->forceDelete();
                    return true;

            } else {
                Bookings::systemJobDetailEntry($systemJob->id, '', 'Booking Fetching From PMS Results in Zero Record ');
            }

        }catch (PmsExceptions $e) {

            if ($e->getPMSCode() == PMS::ERROR_LIMIT_EXCEED)
                $errorCodeNeglectAble = PMS::ERROR_LIMIT_EXCEED;

            Bookings::systemJobDetailEntry($systemJob->id, $e->getTraceAsString(), $e->getMessage());
            Log::error( $e->getMessage(),['System Job ID ' => $systemJob->id]);
            report($e);

        }catch (\Exception $e) {

            //$errorCodeNeglectAble = 'CustomCodeError'; /* Commented because might be some json data fields error or any misshape will effect other queued system jobs */
            Bookings::systemJobDetailEntry($systemJob->id, $e->getTraceAsString(), $e->getMessage());
            Log::error( $e->getMessage(),['System Job ID ' => $systemJob->id, 'Trace'=> $e->getTraceAsString()]);
        }

        $ERROR_LIMIT_EXCEED = (isset($errorCodeNeglectAble) && (($errorCodeNeglectAble == 'CustomCodeError') || ($errorCodeNeglectAble == PMS::ERROR_LIMIT_EXCEED)));
        $status = (($status == SystemJob::STATUS_COMPLETED) ? $status : (((!$ERROR_LIMIT_EXCEED)  && ($systemJob->attempts >= (SystemJob::TOTAL_ATTEMPTS_PMS_PREFERENCES - 1))) ? SystemJob::STATUS_VOID : SystemJob::STATUS_PENDING ));

        $systemJob->due_date = \Carbon\Carbon::now()->addMinute(SystemJob::PMS_PREFERENCES_NEXT_ATTEMPT_AFTER_MINTS)->toDateTimeString();
        $systemJob->lets_process = (($status == SystemJob::STATUS_VOID) && (!$ERROR_LIMIT_EXCEED)  ? 0 : 1);
        $systemJob->status = $status;
        $systemJob->attempts = (!$ERROR_LIMIT_EXCEED ? $systemJob->attempts + 1 : $systemJob->attempts);
        $systemJob->save();

        //Mail to App Developers...
        if ($systemJob->status == SystemJob::STATUS_VOID) {
            $subject =  'PMS Preferences Updating failed while '.SystemJob::TOTAL_ATTEMPTS_PMS_PREFERENCES .' attempts, System_Job_id # '.$systemJob->id;
            $message = 'PMS Updating failed after '.SystemJob::TOTAL_ATTEMPTS_PMS_PREFERENCES .' attempts '.Bookings::class .' , System_Job_id # '.$systemJob->id;
            $anyJsonObject  = json_encode(SystemJob::with('system_job_details')->where('id',$systemJob->id)->first(), JSON_PRETTY_PRINT);
            /**
             * Mail to App Developers...
             */
            Bookings::sendMailToAppDevelopers($subject, $message, $anyJsonObject);
        }

        return ($ERROR_LIMIT_EXCEED === false);
    }

    /**
     * @param UserAccount $userAccount
     * @param PMS $pms
     * @param PmsOptions $pmsOptions
     */
    public static function getBookingNotesFromPMS(UserAccount $userAccount, PMS $pms, PmsOptions $pmsOptions){

    }


    /**
     * Add Detail Entry to system job details
     *
     * @param integer $system_job_id
     * @param string $exceptionObject
     * @param string $responseMsg
     */
    public static function systemJobDetailEntry($system_job_id, string $exceptionObject, string $responseMsg){

        SystemJobDetail::create([
            'system_job_id' => $system_job_id,
            'exception_object' => $exceptionObject,
            'response_msg' => $responseMsg,
        ]);
    }

    /**
     ** Get Current PMS Booking Status of a Booking from PMS
     * @param UserAccount $userAccount
     * @param $bookingInfoId
     * @return array|null
     * @throws PmsExceptions
     */

    public static function getPMSCurrentBookingStatus(UserAccount $userAccount, $bookingInfoId)
    {


        $bookingInfo = $userAccount->bookings_info->where('id', $bookingInfoId)->first();
        $propertyInfo =  $userAccount->properties_info->where('pms_property_id', $bookingInfo->property_id)->first();

        $pms = new PMS($userAccount);
        $pmsOptions = new PmsOptions();
        //$pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
        $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
        $pmsOptions->includeInfoItems = false;
        $pmsOptions->includeInvoice = false;
        $pmsOptions->includeCard = false;
        $pmsOptions->propertyID = $propertyInfo->pms_property_id;
        $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
        $pmsOptions->propertyKey = $propertyInfo->property_key;
        $resultFromXMLRequest = $pms->fetch_Booking_Details($pmsOptions);
        if(count($resultFromXMLRequest) > 0) {
            return array('status' => $resultFromXMLRequest[0]->bookingStatus);
        }

        return null;
    }

    /**
     * @param Booking $booking
     * @return bool
     */

    static function isNonRefundableBooking(Booking $booking)
    {
        return $booking->isNonRefundableBooking();
    }

    /**
     * @param UserAccount $userAccount
     * @param PropertyInfo $propertyInfo
     * @param Booking $booking
     * @param $bookingSourceFormId
     * @return false|mixed|string
     */

    static function getBACancellationSettingsByCheckingNonRefundableBooking(UserAccount $userAccount, PropertyInfo $propertyInfo, Booking $booking, $bookingSourceFormId)
    {
        $isNonRefundableBooking = $booking->isNonRefundableBooking();

        $propertyId = ( $propertyInfo->use_bs_settings == 1 ?  $propertyInfo->id : 0 );
        $cancellationPolicies = UserSettingsBridge::where([
            ['user_account_id', $userAccount->id],
            ['booking_source_form_id', $bookingSourceFormId],
            ['property_info_id', $propertyId],
            ['model_name',CancellationSetting::class]])->first();

        if (!is_null($cancellationPolicies) && ($cancellationPolicies->cancellation_setting != null)) {
            $cancellationPolicy = json_decode($cancellationPolicies->cancellation_setting->settings,true);
        } else {
            $cancellationPolicy = json_decode(CancellationAmountType::CANCELLATION_SETTINGS_DEFAULT_VALUES, true);
        }

        $cancellationPolicy['isNonRefundable'] = $isNonRefundableBooking;
        return  json_encode($cancellationPolicy);
    }

    /**
     * @param $modelName
     * @param $dispachDiscription
     * @return mixed
     */
    static function getPreQueuedSystemJobs($modelName, $dispatchDescription){
        return SystemJob::where('model_name', $modelName)->where('dispatch_description', $dispatchDescription)->where('lets_process' , 1)->where('status', SystemJob::STATUS_PENDING)->get();
    }

    /**
     * Send Email to App Developers.
     * @param $emailSubject
     * @param $emailMessage
     * @param $anyJsonData
     */

    static  function sendMailToAppDevelopers($emailSubject, $emailMessage, $anyJsonData){
        try {
            Mail::to(config('db_const.app_developers.emails.to'))->cc(
                config('db_const.app_developers.emails.cc'))->send(
                new GenericEmail(
                    array(
                        'subject' => $emailSubject,
                        'markdown' => 'emails.network_error_email_markdown',
                        'noReply' => true,
                        'message' => $emailMessage,
                        'any_json_object' => $anyJsonData
                    )
                )
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => Bookings::class, 'Stack' => $e->getTraceAsString()]);
        }
    }


    /**
     * Update System Job on Failure
     * @param SystemJob $preQueuedJobs
     * @param $totalAcceptableAttempts
     * @param $nextAttemptsMints
     */
    static function preQueuedJobsStatusUpdateOnFailure(SystemJob $preQueuedJobs, $totalAcceptableAttempts, $nextAttemptsMints){
        $status = ($preQueuedJobs->attempts >= ($totalAcceptableAttempts - 1) ? SystemJob::STATUS_VOID : SystemJob::STATUS_PENDING);
        $preQueuedJobs->due_date = \Carbon\Carbon::now()->addMinute($nextAttemptsMints)->toDateTimeString();
        $preQueuedJobs->lets_process = ($status == SystemJob::STATUS_VOID ? 0 : 1);
        $preQueuedJobs->status = $status;
        $preQueuedJobs->attempts = $preQueuedJobs->attempts + 1;
        $preQueuedJobs->save();
        return $preQueuedJobs;
    }

    /**
     * @param SystemJob $preQueuedJob
     * @param $bookingInfoId
     * @return int
     * @throws \Exception
     */

    static function preQueuedJobsStatusUpdateOnSuccess(SystemJob $preQueuedJob, $bookingInfoId)
    {
        /*On Job Completed Remove Records from DB */
        SystemJobDetail::where('system_job_id', $preQueuedJob->id)->delete();
        $preQueuedJob->delete();
        return SystemJob::STATUS_COMPLETED;


        /*if (($bookingInfoId != null) && ($bookingInfoId == 0))
            $preQueuedJobs->booking_info_id = $bookingInfoId;

        $preQueuedJobs->lets_process = 0;
        $preQueuedJobs->status = SystemJob::STATUS_COMPLETED;
        $preQueuedJobs->attempts = $preQueuedJobs->attempts + 1;
        $preQueuedJobs->save();
        return $preQueuedJobs;
        */

    }


    /**
     * @param $userAccountId
     * @param $bookingInfoId
     * @param $pmsBookingId
     * @param $pmsPropertyId
     * @param $modelName
     * @param $modelId
     * @param $dispatchDescription
     * @param $dueDate
     * @param $jsonData
     * @param int $attempts
     * @param int $status
     * @param int $letsProcess
     * @return mixed
     */

    static function addNewSystemJobEntry($userAccountId, $bookingInfoId, $pmsBookingId, $pmsPropertyId, $modelName, $modelId, $dispatchDescription, $dueDate, $jsonData, $attempts = 0, $status = SystemJob::STATUS_PENDING, $letsProcess = 1)
    {
        $systemJob = SystemJob::create([
            'user_account_id' => $userAccountId,
            'booking_info_id' => $bookingInfoId,
            'pms_booking_id' => $pmsBookingId,
            'pms_property_id' => $pmsPropertyId,
            'model_name' => $modelName,
            'model_id' => $modelId,
            'dispatch_description' => $dispatchDescription,
            'due_date' => $dueDate,
            'json_data' => $jsonData,
            'attempts' => $attempts,
            'status' => $status,
            'lets_process' => $letsProcess]);
        return $systemJob;
    }

    /***
     * @param UserAccount $userAccount
     * @param $pmsBookingId
     * @param $bookingChannelCode
     * @param $pmsPropertyId
     * @param $bookingStatus
     * @param PmsExceptions $e
     * @param int $bookingInfoId
     */

    public function addFetchingBookingDetailsAPICallFailedSystemJobsEntry(UserAccount $userAccount, $pmsBookingId, $bookingChannelCode, $pmsPropertyId, $bookingStatus, PmsExceptions $e, $bookingInfoId = 0)
    {
        try {

            $jsonData = json_encode(['pms_booking_id' => $pmsBookingId, 'channel_code' => $bookingChannelCode, 'pms_property_id' => $pmsPropertyId, 'booking_status' => $bookingStatus, 'exception_message' => $e->getMessage()]);

            switch ($e->getPMSCode()) {

                case PMS::ERROR_LIMIT_EXCEED : // Usage limit Exceeds Exception

                    $ModelNameAndDispatchDescriptionArr = Bookings::getSystemJobModelNameAndDispatchDescriptionByBookingStatus($bookingStatus);

                    if (count($ModelNameAndDispatchDescriptionArr) > 0) {

                        $systemJob = Bookings::getPreQueuedSystemJobs($ModelNameAndDispatchDescriptionArr['modelName'], $ModelNameAndDispatchDescriptionArr['dispatchDescription']);
                        $systemJob = $systemJob->where('pms_booking_id', $pmsBookingId)->where('pms_property_id', $pmsPropertyId)->where('user_account_id', $userAccount->id)->first();

                        if (!is_null($systemJob)) {

                            $systemJob = Bookings::preQueuedJobsStatusUpdateOnFailure($systemJob, SystemJob::TOTAL_ATTEMPTS_PMS_LIMIT_EXCEED, SystemJob::PMS_LIMIT_EXCEED_NEXT_ATTEMPT_AFTER_MINTS); // Update SystemJob Status/lets_process
                            Bookings::systemJobDetailEntry($systemJob->id, $e->getTraceAsString(), $e->getMessage()); // Add Details

                            // Mail to App Developers & notify Event for Client...

                            if (($systemJob->status == SystemJob::STATUS_VOID) && ($systemJob->attempts >= SystemJob::TOTAL_ATTEMPTS_PMS_LIMIT_EXCEED)) {

                                $subject = 'PMS Usage Limit Exceeds failed while ' . SystemJob::TOTAL_ATTEMPTS_PMS_LIMIT_EXCEED . ' attempts, System_Job_id # ' . $systemJob->id;
                                $message = 'PMS Usage Limit Exceeds failed after ' . SystemJob::TOTAL_ATTEMPTS_PMS_LIMIT_EXCEED . ' attempts ' . Bookings::class . ' , System_Job_id # ' . $systemJob->id;
                                $anyJsonObject = json_encode(SystemJob::with('system_job_details')->where('id', $systemJob->id)->first(), JSON_PRETTY_PRINT);
                                Bookings::sendMailToAppDevelopers($subject, $message, $anyJsonObject);

                                //send email to notify client
                                event(new EmailEvent(config('db_const.emails.heads.booking_fetch_failed.type'), $userAccount->id, [ 'errorCode' => $e->getPMSCode(), 'exceptionMsg' => $e->getMessage(),'exceptionType' => config('db_const.booking_fetching_failed.exception_type.pms_exception'), 'pms_booking_id' => $pmsBookingId ]));
                            }
                        } else {

                            $dueDate = now()->addMinute(SystemJob::PMS_LIMIT_EXCEED_NEXT_ATTEMPT_AFTER_MINTS)->toDateTimeString();
                            $systemJob = Bookings::addNewSystemJobEntry($userAccount->id, $bookingInfoId, $pmsBookingId, $pmsPropertyId, $ModelNameAndDispatchDescriptionArr['modelName'], 0, $ModelNameAndDispatchDescriptionArr['dispatchDescription'], $dueDate, $jsonData, 1);
                            Bookings::systemJobDetailEntry($systemJob->id, $e->getTraceAsString(), $e->getMessage()); // Add Details
                        }
                    }

                    break;
            } // Exception Code Switch
        } catch (\Exception $e){
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * @param $userAccountId
     * @param $pmsBookingId
     * @param $pmsPropertyId
     * @param $bookingStatus
     * @param int $bookingInfoId
     */

    public function UpdateSystemJobOnSuccess($userAccountId, $pmsBookingId,  $pmsPropertyId, $bookingStatus, $bookingInfoId = 0)
    {

        try {

            $ModelNameAndDispatchDescriptionArr = Bookings::getSystemJobModelNameAndDispatchDescriptionByBookingStatus($bookingStatus);

            if (count($ModelNameAndDispatchDescriptionArr) > 0) {

                $systemJob = SystemJob::where('model_name', $ModelNameAndDispatchDescriptionArr['modelName'])->where('dispatch_description', $ModelNameAndDispatchDescriptionArr['dispatchDescription'])
                    ->where('lets_process', 1)
                    ->where('status', SystemJob::STATUS_PENDING)
                    ->where('pms_booking_id', $pmsBookingId)
                    ->where('pms_property_id', $pmsPropertyId)
                    ->where('user_account_id', $userAccountId)->first();

                if (!is_null($systemJob)) {
                    Bookings::preQueuedJobsStatusUpdateOnSuccess($systemJob, $bookingInfoId);
                    /*Bookings::systemJobDetailEntry($systemJob->id, '', 'Job Completed Successfully'); //Add Details */
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * new | modify | cancel Booking Status as received from ApiRequest ,
     * BAGetCard Booking Status when getCard Api Call Failed.
     * @param $bookingStatus
     * @return array
     */
    static function getSystemJobModelNameAndDispatchDescriptionByBookingStatus($bookingStatus)
    {
        $result = [];

        switch ($bookingStatus) {
            case 'new' : // Case For New Booking request received from Api Request => (BANewBookingJob::class)
                $result['modelName'] = SystemJob::PMS_LIMIT_EXCEED_NEW_BOOKING_MODEL_NAME;
                $result['dispatchDescription'] = SystemJob::PMS_LIMIT_EXCEED_NEW_BOOKING_DESCRIPTION;
                break;

            case 'modify' : // Case For Modify Booking request received from Api Request => (BAModifyBookingJob::class)
                $result['modelName'] = SystemJob::PMS_LIMIT_EXCEED_MODIFY_BOOKING_MODEL_NAME;
                $result['dispatchDescription'] = SystemJob::PMS_LIMIT_EXCEED_MODIFY_BOOKING_DESCRIPTION;
                break;

            case 'cancel' : // Case For Cancel Booking request received from Api Request => (BACancelBookingJob::class)
                $result['modelName'] = SystemJob::PMS_LIMIT_EXCEED_CANCEL_BOOKING_MODEL_NAME;
                $result['dispatchDescription'] = SystemJob::PMS_LIMIT_EXCEED_CANCEL_BOOKING_DESCRIPTION;
                break;

            case 'BAGetCard' : // Case For GetCard API Call Fail due to Limit Exceed...
                $result['modelName'] = SystemJob::PMS_LIMIT_EXCEED_GET_CARD_BOOKING_MODEL_NAME;
                $result['dispatchDescription'] = SystemJob::PMS_LIMIT_EXCEED_GET_CARD_DESCRIPTION;
                break;

        } // Booking Status Switch Cases
        return $result;
    }

    /**
     * Reports invalid card at BookingAutomation and add notes<br>
     * @param BookingInfo $bookingInfo
     * @param UserAccount $userAccount
     * @param PropertyInfo $propertyInfo
     */
    public function reportInvalidCard(BookingInfo $bookingInfo, UserAccount $userAccount, PropertyInfo $propertyInfo) {

        try {

            $pmsOptions = new PmsOptions();
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $pmsOptions->bookingInvalidCard = true;
            $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
            $pmsOptions->propertyKey = $propertyInfo->property_key;

            $booking = new Booking();
            $booking->id = $bookingInfo->pms_booking_id;

            $pms = new PMS($userAccount);
            $response = $pms->update_booking($pmsOptions, $booking);

            /**
             * NOTE:
             * To add notes on BA uncomment below code.
             */
            /*
                        $responseMessage = '';

                        if (isset($response['success'])) {

                            if (is_array($response['bookingcomInvalidCard'])) {
                                $responseMessage = $response['bookingcomInvalidCard'][0];

                            } else {
                                $responseMessage = $response['bookingcomInvalidCard'];
                            }

                        } else {
                            $responseMessage = 'The attempt to mark credit card invalid failed on Booking.com Extranet';
                        }

                        $data = json_decode($bookingInfo->full_response, true);
                        $bookingResponse = new Booking();
                        foreach ($data as $key => $value)
                            $bookingResponse->{$key} = $value;

                        $note = empty($bookingResponse->notes) ? '' : $bookingResponse->notes . ' '; // Getting Previous Notes if any exists
                        $note .= 'ChargeAutomation Msg: Invalid Credit card reported to Booking.com. Booking.com Msg: ';
                        $note .= $responseMessage;
                        $note .= ' Dated: ' . date("Y-m-d H:i:s");

                        $booking->notes = $note;

                        $pmsOptions->bookingInvalidCard = null;

                        $pms = new PMS($userAccount);
                        $pms->update_booking($pmsOptions, $booking);
            */

        } catch (PmsExceptions $e) {
            Log::notice($e->getMessage(), [
                'BookingId' => $bookingInfo->pms_booking_id,
                'UserAccountID' => $userAccount->id,
                'File' => Bookings::class,
                'Function' => __FUNCTION__
            ]);

        } catch (\Exception $e) {
            Log::notice($e->getMessage(), [
                'BookingId' => $bookingInfo->pms_booking_id,
                'UserAccountID' => $userAccount->id,
                'File' => Bookings::class,
                'Function' => __FUNCTION__
            ]);
        }

    }


    /**
     *  Check account status and update system job lets process if not active
     * @param UserAccount $userAccount
     * @param SystemJob $systemJob
     * @return bool
     */
    public static function isUserAccountActiveToProceedSystemJob(UserAccount $userAccount, SystemJob $systemJob){

        if($userAccount->status != 1){
            SystemJob::where('user_account_id', $userAccount->id)->update([
                'status' => SystemJob::STATUS_VOID,
                'lets_process' => 0]);
            Bookings::systemJobDetailEntry($systemJob->id, '', 'User Account not Active On CA');

            return  false;
        }
        return true;
    }


    /**
     * @param BookingInfo $bookingInfo
     */
    public static function BA_reportInvalidCardForBDCChannel(BookingInfo $bookingInfo) {

        if (($bookingInfo->channel_code == BS_BookingCom::BA_CHANNEL_CODE)
            && ($bookingInfo->is_vc == BS_Generic::PS_CREDIT_CARD)
            && ($bookingInfo->is_pms_reported_for_invalid_card == 0)) {
            /**
             * Dispatch Job to Report PMS
             */
            ReportPMSInvalidCardJob::dispatch($bookingInfo)->onQueue('ba_syc_bookings');
        }
    }

    /**
     * @param $property_info_id
     * @param UserPaymentGateway $userPaymentGateway
     */
    static function booking_effects($property_info_id , UserPaymentGateway $userPaymentGateway){
        $userAccount = auth()->user()->user_account;
        event(new NewGatewaySelectEvent($userAccount, $property_info_id, $userPaymentGateway));
    }

    /**
     * fetch_Booking_Details with json and XML too to avail info Items.
     *
     * @param PropertyInfo $propertyInfo
     * @param UserAccount $userAccount
     * @param $bookingId
     * @return array|string
     */

    public function fetch_Booking_Details(PropertyInfo $propertyInfo, UserAccount $userAccount, $bookingId) {

        $this->last_error = null;

        try {

            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;

            $result = $pms->fetch_Booking_Details($pmsOptions);

            if(count($result) == 0)
                return [];

            /*
             * NOTE: calling again BookingAutomation API with JSON type request to fetch
             * infoItems, which are not present in XML type request
             */
            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $pmsOptions->propertyID = $propertyInfo->pms_property_id;
            $resultFromJsonRequest = $pms->fetch_Booking_Details($pmsOptions);

            for($j = 0; $j < count($resultFromJsonRequest); $j++) {
                for($x = 0; $x < count($result); $x++) {
                    if($resultFromJsonRequest[$j]->id == $result[$x]->id) {
                        $result[$x]->infoItems = $resultFromJsonRequest[$j]->infoItems;
                        $result[$x]->currencyCode = $resultFromJsonRequest[$j]->currencyCode;
                        continue;
                    }
                }
            }
            return $result;
        } catch (PmsExceptions $e) {
            $this->last_error = $e->getMessage();
            Log::error($e->getMessage(), ['File'=> __FILE__, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'pms_code' => $e->getPMSCode()]);
            return null;
        } catch (\Exception $e) {
            $this->last_error = $e->getMessage();
            Log::error($e->getMessage(), ['File'=> __FILE__, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'Stack'=>$e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * @param $booking_info
     * @return mixed
     */
    public static function isChatActive($booking_info)
    {
        if (!array_key_exists($booking_info->channel_code, self::$bookingSourceChatStatus))
            self::$bookingSourceChatStatus[$booking_info->pms_id][$booking_info->channel_code] =
                filter_var($booking_info->clientGeneralPreferencesInstance
                    ->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'),
                        $booking_info->bookingSourceForm), FILTER_VALIDATE_BOOLEAN);

        return self::$bookingSourceChatStatus[$booking_info->pms_id][$booking_info->channel_code];
    }
}
