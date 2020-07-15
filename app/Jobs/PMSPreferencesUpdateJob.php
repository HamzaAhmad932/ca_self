<?php

namespace App\Jobs;

use App\BookingInfo;
use App\Listeners\PMSPreferencesListener;
use App\Repositories\Bookings\Bookings;
use App\System\PMS\exceptions\PmsExceptions;
use App\SystemJob;
use App\UserPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PMSPreferencesUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $systemJobs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->systemJobs = SystemJob::where('model_name' , SystemJob::PMS_PREFERENCES_MODEL_NAME)->
        where('dispatch_description', SystemJob::PMS_PREFERENCES_DESCRIPTION)->
        where('lets_process' , 1)->
        where('status', SystemJob::STATUS_PENDING)->
        where('due_date' ,'<',  now()->toDateTimeString())->limit(5)->get();

        if ($this->systemJobs->count() > 0)
            $this->updatePMSWithPreferences();
    }

    /**
     *
     * Update PMS Regarding to system jobs
     * which are Pending
     * and lets process = 1
     */

    private function updatePMSWithPreferences(){

        $sysJobId = 0;
        try{
            $preferencesSystemJob = $this->systemJobs;

            if (($preferencesSystemJob != null) && (count($preferencesSystemJob) != 0) ){

                $previousAttemptPendingArr = array();

                foreach ($preferencesSystemJob as $systemJob) {

                    $sysJobId = $systemJob->id;

                    if (in_array($systemJob->user_account_id, $previousAttemptPendingArr))
                        continue;

                    Log::notice('System Job '. json_encode($systemJob));

                    $pmsPropertyId = BookingInfo::find($systemJob->booking_info_id)->property_id; /* pms property id */

                    /**
                     * Set Preferences data on Booking Object
                     */
                    $bookingModelObject = Bookings::setPreferencesDataToUpdate(json_decode($systemJob->json_data, true), $pmsPropertyId);

                    /**
                    * UPDATE PMS*/
                    $jobStatus = Bookings::updatePMSPreferencesWithSystemJob($systemJob, $bookingModelObject);

                    /**
                     * Previous Job are Pending ? first to do that pending job then next job will be attempted to overcome the chance of any misshape
                     */

                    if ($jobStatus === false){
                        if (!in_array($systemJob->user_account_id, $previousAttemptPendingArr))
                            $previousAttemptPendingArr[] = $systemJob->user_account_id;
                    }
                    unset($systemJob);
                }
            }
        }catch (PmsExceptions $e) {
            Log::error($e->getMessage(), ['File'=>PMSPreferencesUpdateJob::class, 'SystemJobId' => $sysJobId]);
            report($e);
        }catch (\Exception $e) {
            Log::error($e->getMessage(), ['Line'=>$e->getLine(), 'File'=>PMSPreferencesUpdateJob::class, 'SystemJobId' => $sysJobId, 'Stack' => $e->getTraceAsString()]);
        }
    }
}
