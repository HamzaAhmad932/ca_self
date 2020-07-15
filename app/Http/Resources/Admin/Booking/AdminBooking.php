<?php

namespace App\Http\Resources\Admin\Booking;

use App\BookingInfo;
use App\CaCapability;
use App\CreditCardAuthorization;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Services\CapabilityService;
use App\TransactionInit;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;

trait AdminBooking{

    protected $property_timezone;

    protected function getAutoPaymentTypeMeta()
    {
        if (empty(self::$paymentTypeMetaAuto['full_transaction']) && empty(self::$paymentTypeMetaAuto['one_of_two_transaction']) && empty(self::$paymentTypeMetaAuto['two_of_two_transaction'])) {
            $payment_type_meta = new PaymentTypeMeta();
            self::$paymentTypeMetaAuto['full_transaction'] = $payment_type_meta->getBookingPaymentAutoCollectionFull();
            self::$paymentTypeMetaAuto['one_of_two_transaction'] = $payment_type_meta->getBookingPaymentAutoCollectionPartial1of2();
            self::$paymentTypeMetaAuto['two_of_two_transaction'] = $payment_type_meta->getBookingPaymentAutoCollectionPartial2of2();
        }

        return self::$paymentTypeMetaAuto;
    }

    protected function checkPriorityFlagForTransactions()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;

        $is_vc = $booking_info->is_vc;
        $cc_info = $booking_info->cc_Infos;
        $transaction_init = $booking_info->transaction_init_charged;
        $pms_booking_status = $booking_info->pms_booking_status;
        $total_amount = $transaction_init->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->sum('price');
        $charged_amount = $transaction_init->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_SUCCESS, TransactionInit::PAYMENT_MARKED_AS_PAID])
            ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)
            ->sum('price');
        $balance = $total_amount - $charged_amount;

        $success = $transaction_init->where('payment_status', TransactionInit::PAYMENT_STATUS_SUCCESS)->count();
        $scheduled = $transaction_init->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_PENDING, TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL])->count();
        $failed = $transaction_init->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_FAIL, TransactionInit::PAYMENT_STATUS_REATTEMPT])->count();
        $voided = $transaction_init->where('payment_status', TransactionInit::PAYMENT_STATUS_VOID)->count();
        $paused = $transaction_init->where('payment_status', TransactionInit::PAYMENT_STATUS_PAUSED)->count();
        $aborted = $transaction_init->where('payment_status', TransactionInit::PAYMENT_STATUS_ABORTED)->count();
        $manuallyVoided = $transaction_init->where('payment_status', TransactionInit::PAYMENT_STATUS_MANUALLY_VOID)->count();
        $markedAsPaid = $transaction_init->where('payment_status', TransactionInit::PAYMENT_MARKED_AS_PAID)->count();
        $auto_payment_type_meta = $transaction_init->whereIn('transaction_type', $this->getAutoPaymentTypeMeta())->count();
        $scheduled_transaction = $transaction_init->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_PENDING, TransactionInit::PAYMENT_STATUS_WAITING_APPROVAL])->first();
        $failed_transaction = $transaction_init->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_FAIL, TransactionInit::PAYMENT_STATUS_REATTEMPT])->first();
        $failed_transaction_detail = $failed_transaction != null ? $failed_transaction->transactions_detail->last() : $failed_transaction;

        $cc_info_detail = '';

        if ($cc_info->count() > 0) {
            $cc_info_detail = $is_vc == "VC" ? $cc_info->where('is_vc', 1)->first() : $cc_info->last();
            $cc_info_system_usage = $cc_info_detail->system_usage != '' ? json_decode(decrypt($cc_info_detail->system_usage), true) : '';
            $system_usage_card = $cc_info_system_usage != '' ? $cc_info_system_usage['cardNumber'] : '';
        }


        $object = Lang::get('client/booking.booking_list.payment_status.init');

        if ($cc_info->count() > 0 && ($is_vc == "CC" || $is_vc == "VC") && empty($cc_info_detail->auth_token) && $cc_info_detail->attempts != 0 && !empty($system_usage_card)) {

            $object = Lang::get('client/booking.booking_list.payment_status.invalid_card');

        } elseif ($cc_info->count() == 0 || ( empty($cc_info_detail->system_usage) && empty($cc_info_detail->customer_object) ) || ( !empty($cc_info_detail->system_usage) && empty($system_usage_card) ) ) {

            $object = Lang::get('client/booking.booking_list.payment_status.card_missing');

        } elseif ($paused > 0) {
            //payment status pasused
            $object = Lang::get('client/booking.booking_list.payment_status.paused');

        } elseif ($aborted > 0) {
            //payment status Aborted
            $object = Lang::get('client/booking.booking_list.payment_status.abort');

        } elseif ($manuallyVoided > 0 && $scheduled == 0 && $pms_booking_status != 0) {

            $object = Lang::get('client/booking.booking_list.payment_status.void');

        } elseif ($markedAsPaid > 0 && $scheduled == 0 && $pms_booking_status != 0) {

            $object = Lang::get('client/booking.booking_list.payment_status.mark_as_paid');

        } elseif ((($success == 0) && ($scheduled == 0) && ($failed > 0) && ($pms_booking_status != 0)) && ($balance > 0)) {
            //Fully Declined
            $object = Lang::get('client/booking.booking_list.payment_status.declined');
            $object['message'] = $this->symbol.number_format($failed_transaction->price, 2) . ' is declined due to ' . $failed_transaction_detail->error_msg;

        } elseif ((($success > 0 && $scheduled == 0 && $failed > 0 && $pms_booking_status != 0) && ($balance > 0)) || (($success == 0 && $scheduled > 0 && $failed > 0 && $pms_booking_status != 0))) {
            //Partial paid, Balance is Declined
            $object = Lang::get('client/booking.booking_list.payment_status.declined');
            $object['message'] = $this->symbol.number_format($failed_transaction->price, 2) . ' is declined due to ' . $failed_transaction_detail->error_msg;

        } elseif (($success > 0 && $scheduled == 0 && $failed == 0 && $pms_booking_status != 0) && $balance == 0) {
            //Fully Paid
            $object = Lang::get('client/booking.booking_list.payment_status.paid');

        } elseif ((($success > 0) && ($scheduled > 0) && ($failed == 0) && ($pms_booking_status != 0)) && ($balance > 0)) {
            //Partial paid, Balance is scheduled
            $object = Lang::get('client/booking.booking_list.payment_status.scheduled');
            $object['message'] = $this->symbol.number_format($scheduled_transaction->price, 2) . ' scheduled to be processed on ' . Carbon::parse($scheduled_transaction->due_date)->format('d M Y');

        } elseif ((($success == 0) && ($scheduled > 0) && ($failed == 0)) && ($balance > 0)) {
            //Fully Scheduled
            $object = Lang::get('client/booking.booking_list.payment_status.scheduled');
            $object['message'] = $this->symbol.number_format($scheduled_transaction->price, 2) . ' scheduled to be processed on ' . Carbon::parse($scheduled_transaction->due_date)->format('d M Y');

        } elseif ($pms_booking_status == 0 || $voided > 0) {
            //Booking Cancelled
            $object = Lang::get('client/booking.booking_list.payment_status.void');

        } elseif ($auto_payment_type_meta == 0 && $cc_info->count() > 0) {
            //User did not enable auto-payment collection (currently it says “Not Applicable)
            $object = Lang::get('client/booking.booking_list.payment_status.not_enabled');
            $object['message'] = $booking_info->is_process_able == 2 ? 'Payment gateway is not enable' : 'Payment rules are not enabled for this booking source';
            $object['url'] = $booking_info->is_process_able == 2 ? route('viewPMS_SetupStep3') : route('viewPMS_SetupStep2');
        }

        return $object;
    }

    /*
     * When booking is not supported
     * Not supported means we are only fetched for guest experience
     * No need to process payment
    */
    protected function getOthersTypeBookingFlag()
    {
        $booking_info = $this->resource;
        $transactions = $booking_info->transaction_init_charged;
        $auto_payment_type_meta = $transactions->whereIn('transaction_type', $this->getAutoPaymentTypeMeta())->count();
        $capabilities = $this->getCapabilities();

        $object = Lang::get('client/booking.booking_list.payment_status.init');

        if ( !$capabilities[CaCapability::AUTO_PAYMENTS] && !$capabilities[CaCapability::SECURITY_DEPOSIT] ) {

            $object = Lang::get('client/booking.booking_list.payment_status.not_supported');
        } elseif ( $auto_payment_type_meta == 0 ) {
            //User did not enable auto-payment collection (currently it says “Not Applicable)
            $object = Lang::get('client/booking.booking_list.payment_status.not_enabled');
            $object['message'] = $booking_info->is_process_able == 2 ? 'Payment gateway is not enable' : 'Payment rules are not enabled for this booking source';
            $object['url'] = $booking_info->is_process_able == 2 ? route('viewPMS_SetupStep3') : route('viewPMS_SetupStep2');
        }

        return $object;
    }

    protected function getDepositStatus($s_auth){

        $credit_card_infos = $s_auth->ccinfo;
        $due_date = Carbon::parse($s_auth->due_date);
        $diff = $due_date->diffInDays(now());
        $due_date = $due_date->format('Y-m-d');
        $deposit_obj = new \stdClass();

        if(!empty($s_auth)) {
            $scheduled = [CreditCardAuthorization::STATUS_PENDING, CreditCardAuthorization::STATUS_MANUAL_PENDING];
            if ($s_auth->captured == 1) {
                //User fully captures the amount
                $deposit_obj->deposit_status = 'Captured';
                $deposit_obj->class = 'text-success';
            } elseif ($s_auth->status == CreditCardAuthorization::STATUS_ATTEMPTED) {
                $deposit_obj->deposit_status = 'Authorized';
                $deposit_obj->class = 'text-success';
            } elseif ($s_auth->status == CreditCardAuthorization::STATUS_VOID) {
                $deposit_obj->deposit_status = 'Void';
                $deposit_obj->class = 'text-warning';
            } elseif (($due_date >= now()->toDateString() && in_array($s_auth->status, $scheduled)) || ($s_auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING)) {
                //Before due date card on file
                //Before due date no card on file
                $deposit_obj->deposit_status = 'Scheduled';
                $deposit_obj->class = 'text-warning';
            } elseif (($due_date < now()->toDateString() && ($s_auth->status != CreditCardAuthorization::STATUS_ATTEMPTED) && $credit_card_infos->cc_last_4_digit == null) || ($credit_card_infos->cc_last_4_digit == null && $s_auth->status == CreditCardAuthorization::STATUS_FAILED)) {
                //After due date, not funded, no card
                $deposit_obj->deposit_status = 'Card Unavailable';
                $deposit_obj->class = 'text-danger';
            } elseif (($due_date < now()->toDateString() && ($s_auth->status != CreditCardAuthorization::STATUS_ATTEMPTED) && $credit_card_infos->cc_last_4_digit != null) || ($credit_card_infos->cc_last_4_digit != null && $s_auth->status == CreditCardAuthorization::STATUS_FAILED)) {
                //After due date, not funded, card on file
                $deposit_obj->deposit_status = 'Authorization Failed';
                $deposit_obj->class = 'text-danger';
            } elseif ($diff >= 7) {
                //After 7 days, hold is automatically released by payment gateway
                $deposit_obj->deposit_status = 'Auto Released';
                $deposit_obj->class = 'text-success';
            } elseif ($s_auth->manually_released == 1) {
                //User releases the hold/authorized
                $deposit_obj->deposit_status = 'Manually Released';
                $deposit_obj->class = 'text-success';
            } else {
                $deposit_obj->deposit_status = '';
                $deposit_obj->class = '';
            }
        }else{
            $deposit_obj->deposit_status = '';
            $deposit_obj->class = '';
        }

        return $deposit_obj;
    }

    public function guestData($booking){

        $guest = new \stdClass();
        $pre_checkin_attempted = $booking->pre_checkin_status != 0 && !empty($booking->guest_data);
        if($pre_checkin_attempted){
            $guest->arrival_time = !empty($booking->guest_data->arrivaltime) ? Carbon::parse($booking->guest_data->arrivaltime)->format('g:i a') : '--';
            $guest->guest_count = !empty($booking->guest_data->adults) ? $booking->guest_data->adults.'('.$booking->guest_data->childern.')' : '--';
        }else{

            //$guest->arrival_time = Carbon::parse($booking->check_in_date)->timezone($this->property_timezone)->format('g:i a');
            $guest->arrival_time = '--';
            $guest->guest_count = '--';
        }

        return $guest;
    }

    public function getBookingStatus($status){

        $status_name = array_search ( $status, config('db_const.booking_info.pms_booking_status'));
        return $status_name === false ? '' : $status_name;

    }

    public function getRoomInfo($booking_info){

        $unit = Unit::where([
            'pms_room_id'=> $booking_info->room_id,
            'property_info_id'=> $booking_info->property_info->id,
            'unit_no'=> $booking_info->unit_id
        ])->first();
        $room_info = new \stdClass();
        $room_info->id = $booking_info->room_info->id;
        $room_info->pms_room_id = $booking_info->room_info->pms_room_id;
        $room_info->room_type = $booking_info->room_info->name;
        $room_info->unit_name = '';
        if(!empty($unit)){
            $room_info->unit_name = ' - '.$unit->unit_name;
        }

        return $room_info;
    }

    /**
     * @param $booking_info
     * @return bool
     */
    public function getCapabilities()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;
        if (!isset(self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code])) {
            self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code] =
                CapabilityService::allCapabilities($booking_info);
        }
        return self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code]?: false;
    }

    public function getPaymentType()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;
        $payment_type = '';

        if($booking_info->is_vc == 'CC') {
            $payment_type = 'Credit Card';
        } else if($booking_info->is_vc == 'VC') {
            $payment_type = 'Virtual Card';
        } else if($booking_info->is_vc == 'BT') {
            $payment_type = 'Bank Transfer';
        }

        return $payment_type;
    }

    public function getPropertyDetails()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;

        if (!empty($booking_info->property_info->propertyInfoAudits)) {
            $booking_info->property_info->propertyInfoAudits->where('created_at', '>=', Carbon::parse($booking_info->created_at)->toDateTimeString())
                ->where('created_at', '<=', Carbon::parse($booking_info->check_out_date)->toDateTimeString());
        }

        return $booking_info->property_info;
    }

    public function getCreditCardInfoDetails()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;
        $cc_info_count = $booking_info->cc_infos->count();
        for ($i = 0; $i < $cc_info_count; $i++) {

            $booking_info->cc_infos[$i]->system_usage = !empty($booking_info->cc_infos[$i]->system_usage) ? json_decode(decrypt($booking_info->cc_infos[$i]->system_usage), true) : 'System Usage does not exist';
            $booking_info->cc_infos[$i]->customer_object_data = '<strong>Email: </strong>' . $booking_info->cc_infos[$i]->customer_object->email . '<br />'
                . '<strong>Card Type: </strong>' . $booking_info->cc_infos[$i]->customer_object->card_type . '<br />'
                . '<strong>State: </strong>' . $booking_info->cc_infos[$i]->customer_object->state . '<br />'
                . '<strong>Status: </strong>' . $booking_info->cc_infos[$i]->customer_object->status;

            if (!empty($booking_info->cc_infos[$i]->creditCardInfoAudits)) {

                $booking_info->cc_infos[$i]->creditCardInfoAudits->where('created_at', '>=', Carbon::parse($booking_info->created_at)->toDateTimeString())
                    ->where('created_at', '<=', Carbon::parse($booking_info->check_out_date)->toDateTimeString());
            }

        }

        return $booking_info->cc_infos;
    }

    public function getCreditCardAuthorizationDetails()
    {
        /**
         * @var  $booking_info BookingInfo
         */
        $booking_info = $this->resource;
        $credit_card_authorization_count = $booking_info->credit_card_authorization->count();

        for ($i = 0; $i < $credit_card_authorization_count; $i++) {

            if (!empty($booking_info->credit_card_authorization[$i]->transaction_obj)) {
                $booking_info->credit_card_authorization[$i]->transaction_obj_data = '<strong>Type: </strong>' . $booking_info->credit_card_authorization[$i]->transaction_obj->type . '<br />'
                    . '<strong>State: </strong>' . $booking_info->credit_card_authorization[$i]->transaction_obj->state . '<br />'
                    . '<strong>Currency Code: </strong>' . $booking_info->credit_card_authorization[$i]->transaction_obj->currency_code . '<br />'
                    . '<strong>Message: </strong>' . $booking_info->credit_card_authorization[$i]->transaction_obj->type;
            }

            $booking_info->credit_card_authorization[$i]->hold_amount = $this->symbol . $booking_info->credit_card_authorization[$i]->hold_amount;
            $booking_info->credit_card_authorization[$i]->is_auto_re_auth = $this->checkAutoReAuth($booking_info->credit_card_authorization[$i]->is_auto_re_auth);
            $booking_info->credit_card_authorization[$i]->type = $this->checkAutoReAuth($booking_info->credit_card_authorization[$i]->type);
            $booking_info->credit_card_authorization[$i]->due_date = Carbon::parse($booking_info->credit_card_authorization[$i]->due_date)->timezone($booking_info->property_info->time_zone)->format('M d Y H:i:s');
            $booking_info->credit_card_authorization[$i]->status = $this->getCreditCardAuthorizationStatus($booking_info->credit_card_authorization[$i]->status);
            $booking_info->credit_card_authorization[$i]->captured = $this->checkCreditCardAuthorizationCaptured($booking_info->credit_card_authorization[$i]->captured);
            $booking_info->credit_card_authorization[$i]->decline_email_sent = $this->getDeclineEmailSendStatus($booking_info->credit_card_authorization[$i]->decline_email_sent);
            $booking_info->credit_card_authorization[$i]->in_processing = $this->getCreditCardAuthorizationProcessingStatus($booking_info->credit_card_authorization[$i]->in_processing);
            $booking_info->credit_card_authorization[$i]->manually_released = $this->checkManuallyReleased($booking_info->credit_card_authorization[$i]->manually_released);

            if (!empty($booking_info->credit_card_authorization[$i]->authorization_details)) {

                for ($j = 0; $j < $booking_info->credit_card_authorization[$i]->authorization_details->count(); $j++) {

                    if (!empty($booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response)) {
                        $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response = json_decode($booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response);
                        $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response_object = '<strong>Type: </strong>' . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response->type . '<br />'
                            . '<strong>State: </strong>' . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response->state . '<br />'
                            . '<strong>Currency Code: </strong>' . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response->currency_code . '<br />'
                            . '<strong>Description: </strong>' . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response->description . '<br />'
                            . '<strong>Message: </strong>' . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_processor_response->message;
                    }

                    $booking_info->credit_card_authorization[$i]->authorization_details[$j]->amount = $this->symbol . $booking_info->credit_card_authorization[$i]->authorization_details[$j]->amount;
                    $booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_status = $this->getAuthorizationDetailPaymentStatus($booking_info->credit_card_authorization[$i]->authorization_details[$j]->payment_status);

                }

            }

        }

        return $booking_info->credit_card_authorization;
    }

    public function checkAutoReAuth($re_auth)
    {
        if ($re_auth == 0) {
            return "No";
        } elseif ($re_auth == 1) {
            return "Yes";
        }
    }

    public function getCreditCardAuthorizationStatus($status)
    {
        $authorization_status_obj = new \stdClass();

        if ($status == CreditCardAuthorization::STATUS_PENDING) {
            $authorization_status_obj->authorization_status = 'Pending';
            $authorization_status_obj->class = 'text-warning';
        } elseif ($status == CreditCardAuthorization::STATUS_ATTEMPTED) {
            $authorization_status_obj->authorization_status = 'Attempted';
            $authorization_status_obj->class = 'text-success';
        } elseif ($status == CreditCardAuthorization::STATUS_VOID) {
            $authorization_status_obj->authorization_status = 'Void';
            $authorization_status_obj->class = 'text-danger';
        } elseif ($status == CreditCardAuthorization::STATUS_MANUAL_PENDING) {
            $authorization_status_obj->authorization_status = 'Manual Pending';
            $authorization_status_obj->class = 'text-warning';
        } elseif ($status == CreditCardAuthorization::STATUS_FAILED) {
            $authorization_status_obj->authorization_status = 'Failed';
            $authorization_status_obj->class = 'text-danger';
        } elseif ($status == CreditCardAuthorization::STATUS_CHARGED) {
            $authorization_status_obj->authorization_status = 'Charged';
            $authorization_status_obj->class = 'text-success';
        } elseif ($status == CreditCardAuthorization::STATUS_REATTEMPT) {
            $authorization_status_obj->authorization_status = 'Reattempt';
            $authorization_status_obj->class = 'text-warning';
        } elseif ($status == CreditCardAuthorization::STATUS_WAITING_APPROVAL) {
            $authorization_status_obj->authorization_status = 'Waiting Approval';
            $authorization_status_obj->class = 'text-warning';
        } elseif ($status == CreditCardAuthorization::STATUS_PAUSED) {
            $authorization_status_obj->authorization_status = 'Paused';
            $authorization_status_obj->class = 'text-danger';
        }

        return $authorization_status_obj;
    }

    public function checkCreditCardAuthorizationCaptured($captured)
    {
        if ($captured == 0) {
            return 'No';
        } elseif ($captured == 1) {
            return 'Yes';
        }
    }

    public function getDeclineEmailSendStatus($decline_email_sent)
    {
        if ($decline_email_sent == 0) {
            return 'Send';
        } elseif ($decline_email_sent == 1) {
            return 'Not Send';
        }
    }

    public function getCreditCardAuthorizationProcessingStatus($in_processing)
    {
        if ($in_processing == 0) {
            return 'Entry process available';
        } elseif ($in_processing == 1) {
            return 'Entry in process by job';
        } elseif ($in_processing == 2) {
            return 'Entry in process Manually';
        } elseif ($in_processing == 3) {
            return 'Entry in process by hook';
        }
    }

    public function checkManuallyReleased($manually_released)
    {
        if ($manually_released == 0) {
            return 'Default value';
        } elseif ($manually_released == 1) {
            return 'Auth Manually Released';
        }
    }

    public function getAuthorizationDetailPaymentStatus($payment_status)
    {
        if ($payment_status == 0) {
            return 'Failed';
        } elseif ($payment_status == 1) {
            return 'Success';
        }
    }

    public function getBookingType($is_vc)
    {
        $booking_type = '';

        if($is_vc == 'CC') {
            $booking_type = 'Credit Card';
        } else if($is_vc == 'VC') {
            $booking_type = 'Virtual Card';
        } else if($is_vc == 'BT') {
            $booking_type = 'Bank Transfer';
        }

        return $booking_type;
    }

}
