<?php

namespace App\Listeners;

use App\AuthorizationDetails;
use App\BookingInfo;
use App\CreditCardAuthorization;
use App\Events\PropertyConnectStatusChangeEvent;
use App\PropertyInfo;
use App\Repositories\Settings\PaymentTypeMeta;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ResumeAuthWhenPropertyIsEnabled extends WhenPropertyIsEnabled implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PropertyConnectStatusChangeEvent
     */
    private $event;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param  PropertyConnectStatusChangeEvent  $event
     * @return void
     */
    public function handle(PropertyConnectStatusChangeEvent $event) {

        try {

            if (!empty($event->ids)) {

                $this->event = $event;

                $propertyInfos = PropertyInfo::whereIn(($event->isPMSPropertyIds ? 'pms_property_id' : 'id'), $event->ids)->get();

                $pt = new PaymentTypeMeta();

                $sdAuthAuto = $pt->getSecurityDepositAutoAuthorize();
                $sdAuthManual = $pt->getSecurityDepositManualAuthorize();
                $ccAuthAuto = $pt->getAuthTypeCCValidation();

                foreach ($propertyInfos as $propertyInfo) {

                    $authorizations = $event->is_active
                        ? $propertyInfo->paused_authorizations
                        : $propertyInfo->pending_and_reattempt_authorizations;

                    foreach ($authorizations as $authorization) {

                        switch ($authorization->type) {
                            case $sdAuthAuto:
                            case $sdAuthManual:
                                $this->handleSecurityAuth($authorization, $propertyInfo);
                                break;
                            case $ccAuthAuto:
                                $this->handlePaymentAuth($authorization, $propertyInfo);
                                break;
                        }

                    }
                }

            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Stack' => $e->getTraceAsString()
            ]);
        }
    }

    private function handleSecurityAuth(CreditCardAuthorization $ccAuth, PropertyInfo $propertyInfo) {

        /**
         * @var $bookingInfo BookingInfo
         */
        $bookingInfo = $ccAuth->booking_info;

        $check_out_date = Carbon::parse($bookingInfo->check_out_date, 'GMT');
        $message = '';
        $status = 0;

        if(!$check_out_date->isPast() && (!$this->isCanceledInDatabase($bookingInfo) || !$this->isCanceledOnPMS($propertyInfo, $bookingInfo->pms_booking_id))) {
            if (in_array($ccAuth->status, [CreditCardAuthorization::STATUS_PENDING, CreditCardAuthorization::STATUS_REATTEMPT])) {
                $message = 'Authorization Paused due to property disabled';
                $status = CreditCardAuthorization::STATUS_PAUSED;
                $ccAuth->status = $status;

            } elseif ($ccAuth->status == CreditCardAuthorization::STATUS_PAUSED) {
                $message = 'Authorization Enabled, due to property Enabled';
                $status = CreditCardAuthorization::STATUS_PENDING;
                $ccAuth->status = $status;
            }

        } else {
            $message = 'Property '.($this->event->is_active ? 'Enabled' : 'Disabled').' after due date of auth.';
            $status = CreditCardAuthorization::STATUS_VOID;
            $ccAuth->status = $status;
        }

        $ccAuth->save();
        $this->insertAuthLog($ccAuth, $message, $status);

    }

    private function handlePaymentAuth(CreditCardAuthorization $ccAuth, PropertyInfo $propertyInfo) {

        /**
         * @var $bookingInfo BookingInfo
         */
        $bookingInfo = $ccAuth->booking_info;

        $check_out_date = Carbon::parse($bookingInfo->check_out_date, 'GMT');
        $message = '';
        $status = 0;

        if(!$check_out_date->isPast()
            && (!$this->isCanceledInDatabase($bookingInfo) || !$this->isCanceledOnPMS($propertyInfo, $bookingInfo->pms_booking_id))
            && !$this->isAnyTransactionInPastOrSameDay($bookingInfo)) {

            if (in_array($ccAuth->status, [CreditCardAuthorization::STATUS_PENDING, CreditCardAuthorization::STATUS_REATTEMPT])) {
                $message = 'Authorization Paused due to property disabled';
                $status = CreditCardAuthorization::STATUS_PAUSED;
                $ccAuth->status = $status;

            } elseif ($ccAuth->status == CreditCardAuthorization::STATUS_PAUSED) {
                $message = 'Authorization Enabled, due to property Enabled';
                $status = CreditCardAuthorization::STATUS_PENDING;
                $ccAuth->status = $status;
            }

        } else {
            $message = 'Property '.($this->event->is_active ? 'Enabled' : 'Disabled').' after due date of auth.';
            $status = CreditCardAuthorization::STATUS_VOID;
            $ccAuth->status = $status;
        }

        $ccAuth->save();
        $this->insertAuthLog($ccAuth, $message, $status);

    }

    private function isAnyTransactionInPastOrSameDay(BookingInfo $bookingInfo) {

        $transaction_inits = $bookingInfo->transaction_init;
        $flag = false;

        foreach($transaction_inits as $tran) {
            $dueDate = Carbon::parse($tran->due_date, 'GMT');
            if($dueDate->isPast() || $dueDate->isSameDay())
                $flag = true;
        }


        return $flag;
    }

    private function insertAuthLog(CreditCardAuthorization $ccAuth, string $message, int $status) {

        try {

            $ccAD = new AuthorizationDetails();
            $ccAD->cc_auth_id = $ccAuth->id;
            $ccAD->user_account_id = $ccAuth->user_account_id;
            $ccAD->payment_status = $status;
            $ccAD->amount = $ccAuth->hold_amount;
            $ccAD->client_remarks = $message;
            $ccAD->error_msg = $message;
            $ccAD->save();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Function' => __FUNCTION__, 'Stack'=>$e->getTraceAsString()]);
        }

    }

}
