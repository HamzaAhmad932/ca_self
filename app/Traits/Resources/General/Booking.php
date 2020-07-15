<?php

namespace App\Traits\Resources\General;

use App\BookingInfo;
use App\CaCapability;
use App\CreditCardAuthorization;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Services\CapabilityService;
use App\System\PMS\BookingSources\BS_Generic;
use App\TransactionInit;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;

trait Booking
{

    protected $property_timezone;

    protected function getPaymentStatus($booking)
    {
       return self::getPayments($booking, $this->symbol, $this->property_timezone);
    }


    public static function getPayments($booking, $currency_symbol, $property_timezone)
    {

        $all_box = [];
        $accepted = [TransactionInit::PAYMENT_STATUS_SUCCESS]; //accepted payment status
        $fail = [TransactionInit::PAYMENT_STATUS_FAIL, TransactionInit::PAYMENT_STATUS_REATTEMPT]; //failed payment status
        $schedule = [TransactionInit::PAYMENT_STATUS_PENDING]; //schedule payment status


        $charge_transactions = $booking->transaction_init
            ->whereIn('type', [
                TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND,
                TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE,
                TransactionInit::TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE,
                TransactionInit::TRANSACTION_TYPE_ADDITIONAL_CHARGE,
                TransactionInit::TRANSACTION_TYPE_CHARGE,
                TransactionInit::TRANSACTION_TYPE_REFUND
            ]);//'C','M', 'R', 'S', 'SR', 'CS'


        foreach ($charge_transactions as $t) {


            if (in_array($t->payment_status, $accepted)) {

                $payment_status = Lang::get('client/booking.booking_list.transaction_status.accepted');

            } elseif (in_array($t->payment_status, $fail)) {

                $payment_status = Lang::get('client/booking.booking_list.transaction_status.declined');

            } elseif (in_array($t->payment_status, $schedule)) {

                $payment_status = Lang::get('client/booking.booking_list.transaction_status.scheduled');
                $payment_status['date'] = 'for ' . Carbon::parse($t->due_date)->timezone($property_timezone)->format('M d');

            } else {

                $key = $t->payment_status;
                if ($t->payment_status == '') {
                    $key = 'init';
                }
                $payment_status = Lang::get('client/booking.booking_list.transaction_status.' . $key);
            }

            $payment_status['id'] = $t->id;
//            $payment_status['title'] = Lang::get('client/booking');
            $payment_status['title'] = Lang::get('client/transaction_types.transaction_type.' . $t->transaction_type . '.title');
            $payment_status['amount'] = $currency_symbol . number_format($t->price, 2);
            $date_initial = in_array($t->payment_status, $schedule) ? 'for ' : 'on ';
            $payment_status['date'] = $date_initial . Carbon::parse($t->due_date)->timezone($property_timezone)->format('M d');// h:i a
            array_push($all_box, $payment_status); //push the single payment box into all boxes
            unset($payment_status);
        }

        //$auth = $this->getAuthPaymentStatus($booking->credit_card_authorization);
        $auth = self::getAuthPaymentStatus($booking->credit_card_authorization, $currency_symbol, $property_timezone, TRUE);
        $sd_auth = $auth['sd_auth'];
        $cc_auth = $auth['cc_auth'];
        return array_merge($cc_auth, $all_box, $sd_auth);
    }

    public static function getTotalCapturedAmount($booking)
    {
        $captured_amount = 0;
        $captured_amount = $booking->transaction_init
            ->where('type', TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE)
            ->where('payment_status', TransactionInit::PAYMENT_STATUS_SUCCESS)
            ->sum('price');//'CS'

        return $captured_amount;
    }

    public static function getTotalCapturedRefundAmount($booking)
    {
        $refund_amount = 0;
        $refund_amount = $booking->transaction_init
            ->where('type', TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND)
            ->where('payment_status', TransactionInit::PAYMENT_STATUS_SUCCESS)
            ->sum('price');//'CS'

        return $refund_amount;
    }

    public static function getAuthPaymentStatus($cc_auths, $currency_symbol, $property_timezone, $auth_separate = FALSE)
    {
        $all_box = $sd_auth = $cc_auth = [];
        $accepted = [1]; //accepted payment status
        $fail = [5, 7]; //failed payment status (reattempt)
        $schedule = [0, 4]; //schedule payment status
        $sd_authorization = [
            config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth'),
            config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth')
        ];
        $cc_authorization = [
            config('db_const.credit_card_authorizations.type.credit_card_auto_authorize'),
            config('db_const.credit_card_authorizations.type.credit_card_manual_authorize')
        ];

        foreach ($cc_auths as $auth) {
            $credit_card_infos = $auth->ccinfo;
            $due_date = Carbon::parse($auth->due_date);
            if (in_array($auth->status, $accepted)) {

                $payment_status = Lang::get('client/booking.booking_list.auth_status.accepted');
            } elseif (in_array($auth->status, $fail)) {

                $payment_status = Lang::get('client/booking.booking_list.auth_status.declined');
            } elseif ($due_date >= now()->toDateString() && in_array($auth->status, $schedule)) {

                $payment_status = Lang::get('client/booking.booking_list.auth_status.scheduled');
            } elseif (($due_date < now()->toDateString() && ($auth->status != CreditCardAuthorization::STATUS_ATTEMPTED) && $credit_card_infos->cc_last_4_digit != null) || ($credit_card_infos->cc_last_4_digit != null && $auth->status == CreditCardAuthorization::STATUS_FAILED)) {

                $payment_status = Lang::get('client/booking.booking_list.auth_status.failed');
            } else {

                $key = $auth->status;
                if ($auth->status == '') {
                    $key = 'init';
                }
                $payment_status = Lang::get('client/booking.booking_list.auth_status.' . $key);
            }

            $payment_status['id'] = $auth->id;
            $payment_status['title'] = Lang::get('client/transaction_types.transaction_type.' . $auth->type . '.title');
            $payment_status['amount'] = $currency_symbol . number_format($auth->hold_amount, 2);
            $dueDate = ($auth->status == CreditCardAuthorization::STATUS_VOID) ? $auth->updated_at : $auth->due_date;
            $date_initial = in_array($auth->status, $schedule) ? 'for ' : 'on ';
            $payment_status['date'] = $date_initial . Carbon::parse($dueDate)->timezone($property_timezone)->format('M d');
            $payment_status['captured'] = $auth->captured;

            if ($auth_separate) {
                if (in_array($auth->type, $sd_authorization)) {
                    array_push($sd_auth, $payment_status);
                } elseif (in_array($auth->type, $cc_authorization)) {
                    array_push($cc_auth, $payment_status);
                }
            } else {
                array_push($all_box, $payment_status);
            }
            unset($payment_status);
        }
        if ($auth_separate) {
            $all_box = ['sd_auth' => $sd_auth, 'cc_auth' => $cc_auth];
        }
        return $all_box;
    }

    protected function getPaymentSummary($transactions)
    {

        $payment_summary = new \stdClass();
        $payment_summary->show_refund = false;

        /**
         * DEPRECATED Code for Payment summary section
         */
        /*
        //Auths
        $payment_summary->auth_success = $this->symbol.$auths->where('status', 1)->sum('hold_amount');
        $payment_summary->auth_failed = $this->symbol.$auths->whereIn('status', [5, 7])->sum('hold_amount');
        $payment_summary->auth_scheduled = $this->symbol.$auths->whereIn('status', [0, 4, 10])->sum('hold_amount');
        //Charges
        $payment_summary->charge_success = $this->symbol.$transactions->whereIn('type', ['C', 'M', 'CS'])->where('payment_status', 1)->sum('price');
        $payment_summary->charge_failed = $this->symbol.$transactions->whereIn('type', ['C', 'M', 'CS'])->whereIn('payment_status', [0, 4])->sum('price');
        $payment_summary->charge_scheduled = $this->symbol.$transactions->whereIn('type', ['C', 'M', 'CS'])->whereIn('payment_status', [2, 5])->sum('price');
        */

        $total_charges = $transactions->where('type', 'C')->sum('price');
        $extras_charges_detail = $transactions->whereIn('type', ['M', 'CS', 'R', 'SR'])->where('payment_status', 1);
        $success_charges = $transactions->where('type', 'C')->where('payment_status', 1)->sum('price');
        $extra_charges = $transactions->whereIn('type', ['M', 'CS'])->where('payment_status', 1)->sum('price');
        $refunded_charges = $transactions->whereIn('type', ['R', 'SR'])->where('payment_status', 1)->sum('price');
        $ext_charges = $extra_charges - $refunded_charges;
        $sub_total = ($total_charges + $extra_charges) - $refunded_charges;
        $marked_as_paid = $transactions->where('payment_status', TransactionInit::PAYMENT_MARKED_AS_PAID)->sum('price');
        $paid = $success_charges + $extra_charges - $refunded_charges + $marked_as_paid;
        $amount_due = $sub_total - $paid;

        if ($paid > 0) {
            $payment_summary->show_refund = true;
        }

        $payment_summary->charges = $this->symbol . number_format($total_charges, 2);
        $payment_summary->extras = number_format($ext_charges, 2) < 0 ? '-' . $this->symbol . number_format(abs($ext_charges), 2) : $this->symbol . number_format($ext_charges, 2);
        $payment_summary->refund = $this->symbol . number_format($refunded_charges, 2);
        $payment_summary->sub_total = $this->symbol . number_format($sub_total, 2);
        $payment_summary->paid = $this->symbol . number_format($paid, 2);
        $payment_summary->amount_due = $this->symbol . number_format($amount_due, 2);
        $payment_summary->total_marked_as_paid = $this->symbol . number_format($marked_as_paid, 2);

        $refunds = ['R', 'SR'];
        $payment_summary->extras_details = $extras_charges_detail->map(function ($ext, $i) use ($refunds) {
            $extra = new \stdClass();
            $extra->label = $ext->client_remarks;
            if (in_array($ext->type, $refunds)) {
                $extra->label = $ext->client_remarks . ' (Refund)';
                $extra->price = '-' . $this->symbol . number_format($ext->price, 2);
            } else {
                $extra->label = $ext->client_remarks;
                $extra->price = $this->symbol . number_format($ext->price, 2);
            }
            return $extra;
        });

        return $payment_summary;
    }

    protected function getPaymentActivityLog()
    {

        // These lines of code add the amount column in transaction detail from transaction init table
        //because transaction_detail table don't have amount column
        $transaction_logs = $this->transaction_init->where('transaction_type', '!=', 23)->whereIn('type', [
            TransactionInit::TRANSACTION_TYPE_CHARGE,
            TransactionInit::TRANSACTION_TYPE_REFUND,
            TransactionInit::TRANSACTION_TYPE_ADDITIONAL_CHARGE,
            TransactionInit::TRANSACTION_TYPE_ADDITIONAL_SECURITY_DAMAGE_CHARGE,
            TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND,
            TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_CAPTURE])->map(function ($v, $i) {
            return $v->transactions_detail->map(function ($d, $j) use ($v) {
                $d->setAttribute('is_auth', false);
                $d->setAttribute('t_type', Lang::get('client/transaction_types.transaction_type.' . $v->transaction_type . '.title'));
                $d->setAttribute('amount', $v->price);
                return $d;
            });
        })->flatten();
        //end

        $refund_log = $this->transaction_init->where('transaction_type', '=', 23)
            ->whereIn('type', [TransactionInit::TRANSACTION_TYPE_REFUND])->map(function ($v, $i) {
            return $v->refund_detail->map(function ($d, $j) use ($v) {
                $d->setAttribute('is_auth', false);
                $d->setAttribute('t_type', Lang::get('client/transaction_types.transaction_type.' . $v->transaction_type . '.title'));
                $d->setAttribute('amount', $d->amount);
                return $d;
            });
        })->flatten();
       // dd($refund_log);


        $transaction_logs = $transaction_logs->merge($refund_log);

        // These lines of code add the cc_info details in authorization detail from credit card auth table
        //because the authorization_details table don't have ccinfo details (cc_info_id)
        $auth_logs = $this->credit_card_authorization->map(function ($v, $i) {

            return $v->authorization_details->map(function ($a, $j) use ($v) {
                $a->setAttribute('is_auth', true);
                $a->setAttribute('t_type', Lang::get('client/transaction_types.transaction_type.' . $v->type . '.title'));
                $a->setAttribute('cc_last_4_digit', $v->ccinfo->cc_last_4_digit);
                return $a;
            });
        })->flatten();
        //end

        $logs = $transaction_logs->merge($auth_logs)->sortByDesc('created_at');


        $filtered = $logs->map(function ($log, $i) {
            $type = 'Payment';
            $desc_payment = 'of ' . $this->symbol . number_format($log->amount, 2);
            $desc_cc = '';
            if (!empty($log->ccinfo)) {
                $desc_cc .= 'on card ending with **' . $log->ccinfo->cc_last_4_digit;
            } else if (!empty($log->cc_last_4_digit)) {
                $desc_cc .= 'on card ending with **' . $log->cc_last_4_digit;
            } else {
                if (!empty($log->error_msg)) {
                    $desc_payment = '';
                    $type = '';
                    $desc_cc = $log->error_msg;
                } else {
                    $desc_cc = 'is refunded';
                }
            }


            $refined = new \stdClass();
            if (!$log->is_auth) {
                $refined->id = $log->transaction_init_id;

            } else {
                $refined->id = $log->cc_auth_id;
                if ($desc_payment !== '') {
                    $type = 'Auth';
                }
            }
            if($log->payment_status == TransactionInit::PAYMENT_STATUS_SUCCESS) {
                $refined->p_status = 'Payment Accepted';
                $badge_class=TransactionInit::PAYMENT_STATUS_SUCCESS;
            }
            else{
                $refined->p_status = 'Payment Declined';
                $badge_class=TransactionInit::PAYMENT_STATUS_FAIL;
            }
            $refined->event_date = Carbon::parse($log->created_at)->timezone($this->property_timezone)->format('M d, Y h:i a');
            $refined->desc_payment = $type . ' ' . $desc_payment;
            $refined->desc_cc = $log->client_remarks != '' ? $log->client_remarks : $refined->desc_payment . ' ' . $desc_cc;
            $refined->class = config('db_const.credit_card_authorizations.activity_log_badge.' . $badge_class);
            $refined->status_msg = $log->error_msg;
            $refined->t_type = $log->t_type;
            $refined->attempted = empty($log->user_id) ? 'Auto' : 'Manual';

            return $refined;
        });

        return $filtered->values()->all();
    }

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

    protected function checkPriorityFlagForTransactions($booking_info)
    {
        $object = Lang::get('client/booking.booking_list.payment_status.init');

        if ($booking_info->is_process_able == BookingInfo::PAYMENT_GATEWAY_INACTIVE) {
            $object = Lang::get('client/booking.booking_list.payment_status.gateway_unverified');
            $object['url'] = route('viewPMS_SetupStep3');
            return $object;
        }

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


        if (strtoupper($is_vc) == BS_Generic::PS_BANK_TRANSFER) { // BT not required Payments
            $object = Lang::get('client/booking.booking_list.payment_status.not_required');

        } elseif ($cc_info->count() > 0 && ($is_vc == BS_Generic::PS_CREDIT_CARD || $is_vc == BS_Generic::PS_VIRTUAL_CARD) && empty($cc_info_detail->auth_token) && $cc_info_detail->attempts != 0 && !empty($system_usage_card)) {

            $object = Lang::get('client/booking.booking_list.payment_status.invalid_card');

        } elseif ($cc_info->count() == 0 || (empty($cc_info_detail->system_usage) && empty($cc_info_detail->customer_object)) || (!empty($cc_info_detail->system_usage) && empty($system_usage_card))) {

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
            $object['message'] = $this->symbol . number_format($failed_transaction->price, 2) . ' is declined due to ' . $failed_transaction_detail->error_msg;

        } elseif ((($success > 0 && $scheduled == 0 && $failed > 0 && $pms_booking_status != 0) && ($balance > 0)) || (($success == 0 && $scheduled > 0 && $failed > 0 && $pms_booking_status != 0))) {
            //Partial paid, Balance is Declined
            $object = Lang::get('client/booking.booking_list.payment_status.declined');
            $object['message'] = $this->symbol . number_format($failed_transaction->price, 2) . ' is declined due to ' . $failed_transaction_detail->error_msg;

        } elseif (($success > 0 && $scheduled == 0 && $failed == 0 && $pms_booking_status != 0) && $balance == 0) {
            //Fully Paid
            $object = Lang::get('client/booking.booking_list.payment_status.paid');

        } elseif ((($success > 0) && ($scheduled > 0) && ($failed == 0) && ($pms_booking_status != 0)) && ($balance > 0)) {
            //Partial paid, Balance is scheduled
            $object = Lang::get('client/booking.booking_list.payment_status.scheduled');
            $object['message'] = $this->symbol . number_format($scheduled_transaction->price, 2) . ' scheduled to be processed on ' . Carbon::parse($scheduled_transaction->due_date)->format('d M Y');

        } elseif ((($success == 0) && ($scheduled > 0) && ($failed == 0)) && ($balance > 0)) {
            //Fully Scheduled
            $object = Lang::get('client/booking.booking_list.payment_status.scheduled');
            $object['message'] = $this->symbol . number_format($scheduled_transaction->price, 2) . ' scheduled to be processed on ' . Carbon::parse($scheduled_transaction->due_date)->format('d M Y');

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
    protected function getOthersTypeBookingFlag($booking_info)
    {
        $transactions = $booking_info->transaction_init_charged;
        $auto_payment_type_meta = $transactions->whereIn('transaction_type', $this->getAutoPaymentTypeMeta())->count();
        $capabilities = $this->getCapabilities($booking_info);

        $object = Lang::get('client/booking.booking_list.payment_status.init');

        if (!$capabilities[CaCapability::AUTO_PAYMENTS] && !$capabilities[CaCapability::SECURITY_DEPOSIT]) {

            $object = Lang::get('client/booking.booking_list.payment_status.not_supported');
        } elseif ($auto_payment_type_meta == 0) {
            //User did not enable auto-payment collection (currently it says “Not Applicable)
            $object = Lang::get('client/booking.booking_list.payment_status.not_enabled');
            $object['message'] = $booking_info->is_process_able == 2 ? 'Payment gateway is not enable' : 'Payment rules are not enabled for this booking source';
            $object['url'] = $booking_info->is_process_able == 2 ? route('viewPMS_SetupStep3') : route('viewPMS_SetupStep2');
        }

        return $object;
    }

    protected function getDepositStatus($s_auth)
    {

        $credit_card_infos = $s_auth->ccinfo;
        $due_date = Carbon::parse($s_auth->due_date);
        $diff = $due_date->diffInDays(now());
        $due_date = $due_date->format('Y-m-d');
        $deposit_obj = new \stdClass();

        if (!empty($s_auth)) {
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
            } elseif (($due_date >= now()->toDateString() && in_array($s_auth->status, $scheduled))) {
                //Before due date card on file
                //Before due date no card on file
                $deposit_obj->deposit_status = 'Scheduled';
                $deposit_obj->class = 'text-warning';
            } elseif ($s_auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING && $due_date < now()->toDateString()) {
                //After due date, not funded, card on file
                $deposit_obj->deposit_status = 'Authorization Failed';
                $deposit_obj->class = 'text-danger';
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
        } else {
            $deposit_obj->deposit_status = '';
            $deposit_obj->class = '';
        }

        return $deposit_obj;
    }

    /**
     * DEPRECATED
     * @param $guest_images
     * @return \stdClass
     *  This method display the guest images status on the booking list
     */
    protected function checkPriorityForGuestImagesStatus($guest_images)
    {

        $approved = $guest_images->where('status', 1)->count();
        $rejected = $guest_images->where('status', 2)->count();
        $uploaded = $guest_images->where('status', 0)->count();

        $id_status = new \stdClass();
        if ($approved != 0 or $rejected != 0 or $uploaded != 0) {
            if ($approved > 0) {
                $id_status->message = 'ID Accepted';
                $id_status->class = 'text-success';
            } elseif ($rejected > 0) {
                $id_status->message = 'ID Rejected';
                $id_status->class = 'text-danger';
            } else {
                $id_status->message = 'ID Uploaded';
                $id_status->class = 'text-warning';
            }
        } else {
            $id_status->message = 'ID Required';
            $id_status->class = 'text-danger';
        }

        return $id_status;
    }

    protected function guestPreCheckinStatus($booking_info)
    {

        $pre_checkin_status = new \stdClass();
        if ($booking_info->pre_checkin_status == '1') {
            $pre_checkin_status->message = 'Pre-checkin completed';
            $pre_checkin_status->class = 'text-success';
        } else {
            $pre_checkin_status->message = 'Pre-checkin incomplete';
            $pre_checkin_status->class = 'text-danger';
        }
        return $pre_checkin_status;
    }

    public function guestData($booking)
    {

        $guest = new \stdClass();
        $pre_checkin_attempted = /*$booking->pre_checkin_status != 0 &&*/ !empty($booking->guest_data);
        if ($pre_checkin_attempted) {
            $guest->arrival_time = !empty($booking->guest_data->arrivaltime) ? Carbon::parse($booking->guest_data->arrivaltime)->format('g:i a') : '--';
            $adults = !empty($booking->guest_data->adults) ? $booking->guest_data->adults : '--';
            $children = !empty($booking->guest_data->childern) ? '(' . $booking->guest_data->childern . ')' : '(0)';
            $guest->guest_count = $adults.$children;

        } else {

            //$guest->arrival_time = Carbon::parse($booking->check_in_date)->timezone($this->property_timezone)->format('g:i a');
            $guest->arrival_time = '--';
            $guest->guest_count = '--';
        }

        return $guest;
    }

    public static function calculateStayNights($full_response, $property_timezone = null)
    {

        $decoded_booking_data = json_decode($full_response, true);

        if (!empty($decoded_booking_data['numNight'])) {
            return $decoded_booking_data['numNight'];
        }
        $first_night = Carbon::parse($decoded_booking_data['firstNight']);
        $last_night = Carbon::parse($decoded_booking_data['lastNight']);

        return $first_night->diffInDays($last_night);
    }

    public static function calculateStayNightsFromPmsBooking(\App\System\PMS\Models\Booking $booking)
    {

        if (!empty($booking->numNight)) {
            return $booking->numNight;
        }
        $first_night = Carbon::parse($booking->firstNight);
        $last_night = Carbon::parse($booking->lastNight);

        return $first_night->diffInDays($last_night);
    }

    public function getBookingStatus($status)
    {

        $status_name = array_search($status, config('db_const.booking_info.pms_booking_status'));
        return $status_name === false ? '' : $status_name;

    }

    public function getRoomInfo($booking_info)
    {

        $unit = Unit::where([
            'pms_room_id' => $booking_info->room_id,
            'property_info_id' => $booking_info->property_info->id,
            'unit_no' => $booking_info->unit_id
        ])->first();
        $room_info = new \stdClass();
        $room_info->id = !empty($booking_info->room_info) ? $booking_info->room_info->id : '';
        $room_info->pms_room_id = !empty($booking_info->room_info) ? $booking_info->room_info->pms_room_id : '';
        $room_info->room_type = !empty($booking_info->room_info) ? $booking_info->room_info->name : '';
        $room_info->unit_name = '';
        $room_info->sub_heading = !empty($booking_info->room_info) ? $booking_info->room_info->name : '';
        if (!empty($unit)) {
            $room_info->unit_name = ' - ' . $unit->unit_name;
            $room_info->sub_heading = !empty($booking_info->room_info) ? $booking_info->room_info->name . '-' . $unit->unit_name : '';
        }

        return $room_info;
    }

    /**
     * @param $booking_info
     * @return mixed
     */
    public function getAllChargedAmountSum($booking_info)
    {
        return $booking_info->transaction_init->whereNotIn(TransactionInit::COLUMN_TYPE,
            [TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND, TransactionInit::TRANSACTION_TYPE_REFUND])
            ->where(TransactionInit::COLUMN_PAYMENT_STATUS, TransactionInit::PAYMENT_STATUS_SUCCESS)
            ->sum(TransactionInit::COLUMN_PRICE);
    }

    public function getAllMarkedAsPaidAmountSum($booking_info)
    {
        return $booking_info->transaction_init->whereNotIn(TransactionInit::COLUMN_TYPE,
            [TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND, TransactionInit::TRANSACTION_TYPE_REFUND])
            ->where(TransactionInit::COLUMN_PAYMENT_STATUS, TransactionInit::PAYMENT_MARKED_AS_PAID)
            ->sum(TransactionInit::COLUMN_PRICE);
    }

    /**
     * @param $booking_info
     * @return mixed
     */
    public function getAllRefundedAmountSum($booking_info)
    {
        return $booking_info->transaction_init->whereIn(TransactionInit::COLUMN_TYPE,
            [TransactionInit::TRANSACTION_TYPE_SECURITY_DAMAGE_DEPOSIT_REFUND, TransactionInit::TRANSACTION_TYPE_REFUND])
            ->where(TransactionInit::COLUMN_PAYMENT_STATUS, TransactionInit::PAYMENT_STATUS_SUCCESS)
            ->sum(TransactionInit::COLUMN_PRICE);
    }

    /**
     * @param $booking_info
     * @return bool
     */
    public function isGuestExperienceChatActive()
    {
        $booking_info = $this->resource;

        if (!isset(self::$bookingChannelsChatStatus[$booking_info->pms_id][$booking_info->channel_code])) {
            self::$bookingChannelsChatStatus[$booking_info->pms_id][$booking_info->channel_code] = filter_var
            (
                $booking_info->clientGeneralPreferencesInstance
                    ->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'),
                        $booking_info->bookingSourceForm),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        return self::$bookingChannelsChatStatus[$booking_info->pms_id][$booking_info->channel_code] ?: false;
    }

    /**
     * @return bool
     */
    public function isGuestExperienceEmailActive()
    {
        $booking_info = $this->resource;

        if (!isset(self::$bookingChannelsGuestEmailStatus[$booking_info->pms_id][$booking_info->channel_code])) {
            self::$bookingChannelsGuestEmailStatus[$booking_info->pms_id][$booking_info->channel_code] = filter_var
            (
                $booking_info->clientGeneralPreferencesInstance
                    ->isActiveStatus(config('db_const.general_preferences_form.emailToGuest'),
                        $booking_info->bookingSourceForm),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        return self::$bookingChannelsGuestEmailStatus[$booking_info->pms_id][$booking_info->channel_code] ?: false;
    }

    /**
     * @return bool
     */
    public function isGuestExperienceDocumentsActive()
    {
        $booking_info = $this->resource;

        if (!isset(self::$bookingChannelsDocumentRequiredStatus[$booking_info->pms_id][$booking_info->channel_code])) {
            self::$bookingChannelsDocumentRequiredStatus[$booking_info->pms_id][$booking_info->channel_code] = filter_var
            (
                ($booking_info->clientGeneralPreferencesInstance
                        ->isActiveStatus(config('db_const.general_preferences_form.requiredPassportScan'),
                            $booking_info->bookingSourceForm)
                    ||
                    $booking_info->clientGeneralPreferencesInstance
                        ->isActiveStatus(config('db_const.general_preferences_form.requiredCreditCardScan'),
                            $booking_info->bookingSourceForm)),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        return self::$bookingChannelsDocumentRequiredStatus[$booking_info->pms_id][$booking_info->channel_code] ?: false;
    }


    /**
     * @param $booking_info
     * @return bool
     */
    public function getCapabilities($booking_info)
    {
        if (!isset(self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code])) {
            self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code] =
                CapabilityService::allCapabilities($booking_info);
        }
        return self::$bookingsChannelsCapabilities[$booking_info->pms_id][$booking_info->channel_code] ?: false;
    }

    public function getPaymentGateway()
    {

        $pg = new PaymentGateways();
        return !empty($pg->getPropertyPaymentGatewayFromBooking($this->resource));
    }

    public function upsellOrders()
    {
        $this->resource->upsellOrders = $this->resource->upsellOrders->where('status',
            config('db_const.upsell_order.status.paid.value'));

        $purchased_types = [];
        $total_purchased_items = 0;

        foreach ($this->resource->upsellOrders as $order) {

            $total_purchased_items += $order->upsellOrderDetails->count();

            $types = $order->upsellOrderDetails->transform(function ($instance) {
                return $instance->upsell->upsellType->title;
            });

            $types = array_unique($types->toArray());
            $purchased_types = array_merge($purchased_types, $types);
        }

        return [
            'total_upsell_items' => $total_purchased_items,
            'purchased_types' => implode(', ', $purchased_types),
            'total_amount' => $this->symbol . $this->resource->upsellOrders->sum('final_amount'),
            'total_commission_fee' => $this->symbol . $this->resource->upsellOrders->sum('commission_fee'),
        ];
    }

    /**
     * Load Upsell Types.
     */
//    public function getUpsellTypes()
//    {
//        if (empty(self::$upsellTypes)) {
//            $upsell_repo = new UpsellRepository();
//            self::$upsellTypes = $upsell_repo->getUpsellTypes();
//        }
//    }
}
