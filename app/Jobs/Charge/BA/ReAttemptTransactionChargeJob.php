<?php

namespace App\Jobs\Charge\BA;

use App\BAModels\ReadyToReAttemptTransaction;
use App\Events\BAPropertyChangeEncounteredEvent;
use App\System\PMS\Models\Booking;
use App\TransactionInit;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReAttemptTransactionChargeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BAChargeJobsHelperTrait;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('reattempt');
        $this->init_helper();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var $record ReadyToReAttemptTransaction
         */

        /* Get //BA|Beds24 Transaction Records to charge */
        $records = ReadyToReAttemptTransaction::whereIn('pms_form_id', [1, 6])->get();
        $ids = $records->pluck('id')->toArray();

        /** Reserve Records, will not be available for manual attempt till in auto processing job */
        $this->setRecordsAvailableToProcess(TransactionInit::TRANSACTION_ADDED_IN_QUEUE_PROCESSING, $ids);

        foreach ($records as $record) {
            try {

                $this->current_record = [
                    'BookingInfoId' => $record->booking_info_id,
                    'Transaction_init' => $record->id
                ];


                // Get Booking Detail from PMS */
                $pms_booking = $this->fetch_Booking_Details_json_xml($record);

                if (!$pms_booking instanceof Booking) {

                    //check if booking property changed -- If changed then update new property inside event
                    if(event(new BAPropertyChangeEncounteredEvent($record->pms_booking_id ) )) {

                        continue; //don't create transaction logs because system will process this updated booking in next attempt
                    }

                    $this->setRecordToReAttempt($record, null, $pms_booking);
                    continue;
                }

                // Check PMS Balance amount & fix amount to round
                $pms_charged = $pms_booking->isValidToChargeByCheckingBalanceOnPMS($record->price);


                // If Already Paid on PMS | Balance Amount less than Transaction amount
                if ($this->isAlreadyPaidOnPMS($record, $pms_booking, $pms_charged))
                    continue;


                $amount = empty($pms_charged['isRoundError'])
                    ? $record->price
                    : $pms_charged['amountToCharge'];


                /** Set Card instance to charge */
                $card = $this->getCard($record, $amount);

                /** Cancel Auth before Charge Attempt*/
                $this->cancelAuthorization($record);

                /** Attempt to Charge Transaction */
                $this->chargeNow($record, $card, $pms_charged['isRoundError']);

            } catch (Exception $exception) {

                log_exception_by_exception_object($exception, $this->current_record);
            }

        }

        /** Set Available for Manually  Attempt */
        $this->setRecordsAvailableToProcess(TransactionInit::TRANSACTION_AVAILABLE_TO_PROCESS, $ids);
    }
}