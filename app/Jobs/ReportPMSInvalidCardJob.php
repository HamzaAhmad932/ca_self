<?php

namespace App\Jobs;

use App\BookingInfo;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\exceptions\PmsExceptions;
use App\SystemJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReportPMSInvalidCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var BookingInfo
     */
    private $bookingInfo;

    /**
     * ReportPMSInvalidCardJob constructor.
     * @param BookingInfo $bookingInfo
     */
    public function __construct(BookingInfo $bookingInfo)
    {
        $this->bookingInfo = $bookingInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (($this->bookingInfo->channel_code == BS_BookingCom::BA_CHANNEL_CODE)
        && ($this->bookingInfo->is_vc == BS_Generic::PS_CREDIT_CARD)
        && ($this->bookingInfo->is_pms_reported_for_invalid_card == 0)) {

            /**
             * Report PMS For Invalid Card
             */
            $this->reportPMSForInvalidCard();
        } else{
            // Log::notice('Already Reported PMS for INVALID CARD', ['booking_info_id' => $this->bookingInfo->id, 'File' => ReportPMSInvalidCardJob::class]);
            return;
        }
    }


    /**
     * Report PMS InValidCard only fo CC Booking Of Booking.com channel or Add new System Job if any pre queued jobs are there for this booking
     */
    private function reportPMSForInvalidCard(){

        try {
            //Getting Property Timezone From Property Info
            $time_zone= PropertyInfo::where([['pms_property_id', $this->bookingInfo->property_id],['user_account_id', $this->bookingInfo->user_account_id]])->select('time_zone')->first()->time_zone;

            $localTimeNow = Carbon::parse(now(), 'GMT')->setTimezone($time_zone)->toDateTimeString(); //Convert to Local Hotel Time.

            $dataObject = array(
            "bookingInvalidCard" => true,
            "booking_status" => "Unchanged",
            "notes" => "ChargeAutomation.com Msg - " . $localTimeNow . "\nReported Booking.com Invalid Card",
            "bookingID" => $this->bookingInfo->pms_booking_id,
            "preferenceFormId" => 0 );

            $preQueuedJobs = SystemJob::where('model_name', SystemJob::PMS_PREFERENCES_MODEL_NAME)->
            where('dispatch_description', SystemJob::PMS_PREFERENCES_DESCRIPTION)->
            where('lets_process', 1)->
            where('booking_info_id', $this->bookingInfo->id)->
            where('status', SystemJob::STATUS_PENDING)->count();

            $systemJob = SystemJob::create([
            'user_account_id' => $this->bookingInfo->user_account->id,
            'booking_info_id' => $this->bookingInfo->id,
            'model_name' => SystemJob::PMS_PREFERENCES_MODEL_NAME,
            'model_id' => 0,
            'dispatch_description' => SystemJob::PMS_PREFERENCES_DESCRIPTION,
            'due_date' => now()->toDateTimeString(),
            'json_data' => json_encode($dataObject),
            'attempts' => 0,
            'status' => SystemJob::STATUS_PENDING,
            'lets_process' => 1]);

            /**
             * update booking Info / dn't Report Again.
             */
            $this->bookingInfo->is_pms_reported_for_invalid_card = 1;
            $this->bookingInfo->save();

            if ($preQueuedJobs == 0) {
                /**
                 * Set Preferences data on Booking Object */
                $bookingModelObject = Bookings::setPreferencesDataToUpdate($dataObject, $this->bookingInfo->property_id);
                /**
                 * UPDATE PMS  */
                Bookings::updatePMSPreferencesWithSystemJob($systemJob, $bookingModelObject);
            }
            /* Else PMSPreferencesUpdateJob will handle this request as in queue */

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['StackTrace' =>$e->getTraceAsString(), 'File'=>ReportPMSInvalidCardJob::class]);
        }

    }
}
