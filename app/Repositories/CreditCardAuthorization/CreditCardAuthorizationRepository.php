<?php

namespace App\Repositories\CreditCardAuthorization;

use App\BookingInfo;
use App\TransactionInit;
use App\CreditCardAuthorization;
use App\System\PMS\BookingSources\BS_Generic;


class CreditCardAuthorizationRepository
{

    public static function updateCreditCardAuthOnCustomerSuccess(BookingInfo $booking_info, int $cc_info_id)
    {

        $auth = $booking_info->credit_card_authorization->whereIn('type', [
            config('db_const.credit_card_authorizations.type.credit_card_auto_authorize'),
            config('db_const.credit_card_authorizations.type.credit_card_manual_authorize'),
        ])->first();
        
        if(!empty($auth)) {

            $status = $auth->status;
            if ($auth->status == CreditCardAuthorization::STATUS_FAILED) {
                $status = CreditCardAuthorization::STATUS_REATTEMPT;
            } elseif ($auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING) {
                $status = CreditCardAuthorization::STATUS_PENDING;
            }

            // Change Manual Type to Auto to auto-process in CC ReAuth Job
            $auth_type = $auth->type == config('db_const.credit_card_authorizations.type.credit_card_manual_authorize') ? config('db_const.credit_card_authorizations.type.credit_card_auto_authorize') : $auth->type;

            // Change Next due date to due date if status failed or reAttempt.
            $auth_next_due_date = in_array($auth->status, [CreditCardAuthorization::STATUS_FAILED, CreditCardAuthorization::STATUS_REATTEMPT])
                ? $auth->due_date : $auth->next_due_date;

            //Void the CCauth if the booking payment due date is less than or equal to ccauth due date
            $condition = $auth->type == config('db_const.credit_card_authorizations.type.credit_card_auto_authorize')
                && $booking_info
                    ->transaction_init
                    ->where('due_date', '<=', now()->toDateTimeString())
                    ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)
                    ->count();
            if ($condition) {

                $status = !in_array($auth->status, [CreditCardAuthorization::STATUS_ATTEMPTED, CreditCardAuthorization::STATUS_CHARGED])
                    ? CreditCardAuthorization::STATUS_VOID : $status;
            }

            //Update CC auth entry
            $auth->update([
                'status' => $status,
                'type' => $auth_type,
                'is_auto_re_auth' => $condition ? 0 : $auth->is_auto_re_auth,
                'attempts' => 1,
                'cc_info_id' => $cc_info_id,
                'next_due_date' => $auth_next_due_date
            ]);
        }
    }

    public static function updateSecurityDamageDepositAuthOnCustomerSuccess(BookingInfo $booking_info, int $cc_info_id)
    {

        $auth = $booking_info->credit_card_authorization->whereIn('type', [
            config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth'),
            config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth'),
        ])->first();

        if(!empty($auth)) {

            $status = $auth->status;

            if ($auth->status == CreditCardAuthorization::STATUS_FAILED) {
                $status = CreditCardAuthorization::STATUS_REATTEMPT;
            } elseif ($auth->status == CreditCardAuthorization::STATUS_MANUAL_PENDING) {
                $status = CreditCardAuthorization::STATUS_PENDING;
            }

            // Change Manual Type to Auto to auto-process in CC ReAuth Job
            $auth_type = $auth->type == config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth')
                ? config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth')
                : $auth->type;

            // Change Next due date to due date if status failed or reAttempt.
            $auth_next_due_date = in_array($auth->status, [CreditCardAuthorization::STATUS_FAILED, CreditCardAuthorization::STATUS_REATTEMPT])
                ? $auth->due_date : $auth->next_due_date;

            //Update SD auth entry
            $auth->update([
                'status' => $status,
                'type' => $auth_type,
                'next_due_date' => $auth_next_due_date,
                'cc_info_id' => $cc_info_id,
                'attempts' => 1
            ]);

        }
    }
}