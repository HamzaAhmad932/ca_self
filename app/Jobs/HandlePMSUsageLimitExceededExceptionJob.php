<?php

namespace App\Jobs;

use App\Repositories\Bookings\Bookings;
use App\System\PMS\exceptions\PmsExceptions;
use App\SystemJob;
use App\UserAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class HandlePMSUsageLimitExceededExceptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var SystemJob[]|\Illuminate\Database\Eloquent\Collection
     */
    private $systemJobs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_sql_mode('ONLY_FULL_GROUP_BY');
        $modelNamesArr = [SystemJob::PMS_LIMIT_EXCEED_NEW_BOOKING_MODEL_NAME, SystemJob::PMS_LIMIT_EXCEED_MODIFY_BOOKING_MODEL_NAME, SystemJob::PMS_LIMIT_EXCEED_CANCEL_BOOKING_MODEL_NAME, SystemJob::PMS_LIMIT_EXCEED_GET_CARD_BOOKING_MODEL_NAME];
        $this->systemJobs = SystemJob::whereIn('model_name', $modelNamesArr)
                                        ->where('lets_process' , 1)
                                        ->where('status', SystemJob::STATUS_PENDING)
                                        ->where('due_date' ,'<',  now()->toDateTimeString())->groupBy('user_account_id')->limit(10)->orderBy('id')->get();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->HandlePMSUsageLimitExceeded();
    }



    private function HandlePMSUsageLimitExceeded()
    {

        foreach ($this->systemJobs as $key => $systemJob) {

            /**
             * if User Account not active
             */
            if ($systemJob->user_account->status != 1){
                SystemJob::where('user_account_id', $systemJob->user_account->id)->update([
                    'status' => SystemJob::STATUS_VOID,
                    'lets_process' => 0]);
                Bookings::systemJobDetailEntry($systemJob->id, '', 'User Account not Active On CA');
                unset($systemJob);
                continue;
            }

            /**
             * decode json getting object data to update on pms
             */
            $systemJobJsonDataDecoded = json_decode($systemJob->json_data);

            if (($systemJob->model_name == SystemJob::PMS_LIMIT_EXCEED_NEW_BOOKING_MODEL_NAME) && ($systemJob->dispatch_description == SystemJob::PMS_LIMIT_EXCEED_NEW_BOOKING_DESCRIPTION)) {

                BANewBookingJobNew::dispatch(UserAccount::find($systemJob->user_account_id), $systemJob->pms_booking_id, $systemJobJsonDataDecoded->channel_code, $systemJob->pms_property_id, $systemJobJsonDataDecoded->booking_status)
                    ->delay(now()->addSeconds(15))
                    ->onQueue('ba_new_bookings'); // Dispatch BANewBookingJob

            } elseif (($systemJob->model_name == SystemJob::PMS_LIMIT_EXCEED_CANCEL_BOOKING_MODEL_NAME) && ($systemJob->dispatch_description == SystemJob::PMS_LIMIT_EXCEED_CANCEL_BOOKING_DESCRIPTION)) {

                BACancelBookingJob::
                dispatch(UserAccount::find($systemJob->user_account_id), $systemJob->pms_booking_id, $systemJobJsonDataDecoded->channel_code, $systemJob->pms_property_id, $systemJobJsonDataDecoded->booking_status)
                    ->onQueue('ba_cancel_bookings'); // Dispatch BACancelBookingJob

            } elseif (($systemJob->model_name == SystemJob::PMS_LIMIT_EXCEED_MODIFY_BOOKING_MODEL_NAME) && ($systemJob->dispatch_description == SystemJob::PMS_LIMIT_EXCEED_MODIFY_BOOKING_DESCRIPTION)) {

                BAModifyBookingJob::
                dispatch(UserAccount::find($systemJob->user_account_id), $systemJob->pms_booking_id, $systemJobJsonDataDecoded->channel_code, $systemJob->pms_property_id, $systemJobJsonDataDecoded->booking_status)
                    ->onQueue('ba_modify_bookings');

            } elseif (($systemJob->model_name == SystemJob::PMS_LIMIT_EXCEED_GET_CARD_BOOKING_MODEL_NAME) && ($systemJob->dispatch_description == SystemJob::PMS_LIMIT_EXCEED_GET_CARD_DESCRIPTION)) {
                Log::critical('GetCard API Failed');
                // TODO  Implement GetCard Failed.
            }

            unset($systemJob);
        }
    }

}
