<?php

namespace App\Listeners;

use App\BookingInfo;
use App\CreditCardInfo;
use App\Events\PropertyConnectStatusChangeEvent;
use App\Jobs\BACancelBookingJob;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\System\PaymentGateway\Models\Transaction;
use App\TransactionDetail;
use App\TransactionInit;
use App\UserAccount;
use App\UserPaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResumeTransactionInitsWhenPropertyIsEnabled extends WhenPropertyIsEnabled implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;

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

                $propertyInfos = PropertyInfo::whereIn(($event->isPMSPropertyIds ? 'pms_property_id' : 'id'), $event->ids)->get();

                foreach ($propertyInfos as $propertyInfo) {

                    // Pause Pending and Re-attempt Trans on Property disabled | Resume Trans on Property Enabled
                    $transactions = $event->is_active
                        ? $propertyInfo->paused_transaction_inits
                        : $propertyInfo->pending_and_reattempt_transaction_inits;

                    /**
                     * @var $tran TransactionInit
                     */
                    foreach ($transactions as $tran) {

                        if (!$event->is_active) {
                            // Pause Pending and Re-attempt Trans on Property disabled
                            $tran->update(['payment_status' => TransactionInit::PAYMENT_STATUS_PAUSED, 'lets_process' => 0]);

                            $this->createTransactionDetailLog(
                                $tran,
                                'Paused due to property disabled.',
                                TransactionInit::PAYMENT_STATUS_PAUSED
                            );

                        } else {
                            /**
                             * @var $bookingInfo BookingInfo
                             */
                            $bookingInfo = $tran->booking_info;

                            if ($this->isCanceledOnPMS($propertyInfo, $bookingInfo->pms_booking_id) && !$this->isCanceledInDatabase($bookingInfo)) {

                                BACancelBookingJob::dispatch(
                                    $propertyInfo->user_account,
                                    $bookingInfo->pms_booking_id,
                                    $bookingInfo->channel_code,
                                    $propertyInfo->pms_property_id,
                                    'cancel',
                                    false)->onQueue('ba_cancel_bookings');

                            } else {
                                $tran->payment_status = TransactionInit::PAYMENT_STATUS_PENDING;
                                $tran->lets_process = 1;
                                $tran->attempt = 0;
                                $tran->save();

                                $this->createTransactionDetailLog(
                                    $tran,
                                    'Enabling transaction after property enabled.',
                                    TransactionInit::PAYMENT_STATUS_PENDING);
                            }
                        }
                    }

                }

            }
            
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__]);
        }

    }

    private function createTransactionDetailLog(TransactionInit $tran, string $message, $status) {

        $transDetail = new TransactionDetail();
        $transDetail->transaction_init_id = $tran->id;
        $transDetail->user_account_id = $tran->user_account_id;
        $transDetail->payment_status = $status;
        $transDetail->error_msg = $message;
        $transDetail->client_remarks = $message;
        $transDetail->save();
        return $transDetail;
    }

}
