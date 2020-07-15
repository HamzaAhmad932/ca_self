<?php


namespace App\Repositories\TransactionInit;

use App\BookingInfo;
use App\System\PMS\BookingSources\BS_Generic;
use App\TransactionInit;
use Carbon\Carbon;

class TransactionInitRepository //extends BaseRepository implements RepositoryInterface
{
    /**
     * @var string|null
     */
    protected $tenantKey;
    /**
     * @var string|null
     */
    protected $tenantValue;

    /**
     * TransactionInitRepository constructor.
     * @param string|null $tenantKey
     * @param string|null $tenantValue
     */
//    public function __construct(string $tenantKey = null, string $tenantValue = null)
//    {
//        $this->tenantKey   = $tenantKey;
//        $this->tenantValue = $tenantValue;
//        $this->setModelName(TransactionInit::class);
//    }

    /**
     * @param $paymentStatus
     * @return bool
     */
    public static function isTransactionStatusValidToMarkAsPaid($paymentStatus)
    {
        $paymentStatuses = [TransactionInit::PAYMENT_MARKED_AS_PAID, TransactionInit::PAYMENT_STATUS_MANUALLY_VOID,
            TransactionInit::PAYMENT_STATUS_VOID, TransactionInit::PAYMENT_STATUS_SUCCESS];
        return !in_array($paymentStatus, $paymentStatuses);
    }

    /**
     * @param $paymentStatus
     * @return bool
     */
    public static function isTransactionStatusValidToManuallyVoid($paymentStatus)
    {
        $paymentStatuses = [TransactionInit::PAYMENT_MARKED_AS_PAID, TransactionInit::PAYMENT_STATUS_MANUALLY_VOID,
            TransactionInit::PAYMENT_STATUS_VOID, TransactionInit::PAYMENT_STATUS_SUCCESS];
        return !in_array($paymentStatus, $paymentStatuses);
    }

    /**
     * Transaction_inits record update on card's customer object creation fail.
     * is_manually_updated => false if card updated from CA Panel else if $is_manually_updated => true if Card updated
     * by PMS Request.
     * @param BookingInfo $booking_info
     * @param bool $is_manually_updated
     */
    public static function updateTransactionInitsOnCustomerFail(BookingInfo $booking_info, $is_manually_updated = true)
    {

        switch ($booking_info->is_vc) {

            case BS_Generic::PS_CREDIT_CARD:

                $booking_info->transaction_init()
                    ->whereIn('payment_status', TransactionInit::PAYMENT_STATUS_REATTEMPT)
                    ->where('type', 'C')
                    ->update(
                        [
                            'attempt' => 1,
                            'is_modified' => 1,
                            'payment_status' => TransactionInit::PAYMENT_STATUS_REATTEMPT,
                        ]
                    );
                break;

            case BS_Generic::PS_VIRTUAL_CARD:

                if (!$is_manually_updated) {

                    $booking_info->transaction_init()
                        ->where('type', 'C')
                        ->whereIn('payment_status',
                            [
                                TransactionInit::PAYMENT_STATUS_FAIL,
                                TransactionInit::PAYMENT_STATUS_REATTEMPT
                            ]
                        )->update(
                            [
                                'payment_status' => TransactionInit::PAYMENT_STATUS_PENDING,
                                'attempt' => 0,
                                'is_modified' => 1,
                                'lets_process' => 0,
                                'next_attempt_time' => Carbon::now()->toDateTimeString(),
                                'due_date' => Carbon::now()->toDateTimeString()
                            ]
                        );
                }
                break;

            case BS_Generic::PS_BANK_TRANSFER:
                // Transaction Init Record not available in BT Case.
                // No Need to update Transaction Inits.
                break;

        }
    }

    /**
     * Transaction_inits record update on successfully card's customer object creation
     * is_manually_updated => false if card updated from CA Panel else if $is_manually_updated => true if Card updated
     * by PMS Request.
     * @param BookingInfo $booking_info
     * @param bool $is_manually_updated
     */
    public static function updateTransactionInitsOnCustomerSuccess(BookingInfo $booking_info, $is_manually_updated = true)
    {

        switch ($booking_info->is_vc) {

            case BS_Generic::PS_CREDIT_CARD:

                $booking_info->transaction_init()
                    ->where('type', 'C')
                    ->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
                    ->update(
                        [
                            'is_modified' => 1,
                            'lets_process' => 1
                        ]
                    );

                $booking_info->transaction_init()
                    ->where('type', 'C')
                    ->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_REATTEMPT, TransactionInit::PAYMENT_STATUS_FAIL])
                    ->update(
                        [
                            'attempt' => 1,
                            'is_modified' => 1,
                            'lets_process' => 1,
                            'next_attempt_time' => now()->toDateTimeString(),
                            'payment_status' => TransactionInit::PAYMENT_STATUS_REATTEMPT

                        ]
                    );

                break;

            case BS_Generic::PS_VIRTUAL_CARD:

                if (!$is_manually_updated) {
                    $booking_info->transaction_init()->where('type', 'C')
                        ->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
                        ->update(
                            [
                                'attempt' => 0,
                                'is_modified' => 1,
                                'lets_process' => 1
                            ]
                        );

                    $booking_info->transaction_init()->where('type', 'C')
                        ->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_REATTEMPT, TransactionInit::PAYMENT_STATUS_PENDING])
                        ->update(
                            [
                                'attempt' => 1,
                                'is_modified' => 1,
                                'lets_process' => 1,
                                'next_attempt_time' => now()->toDateTimeString(),
                                'payment_status' => TransactionInit::PAYMENT_STATUS_REATTEMPT
                            ]
                        );
                }

                break;

            case BS_Generic::PS_BANK_TRANSFER:
                // Transaction Init Record not available in BT Case.
                // No Need to update Transaction Inits.
                break;

        }
    }
}