<?php

namespace App\Listeners;

use App\PropertyInfo;
use App\Events\PMSPreferencesEvent;
use App\Repositories\Bookings\Bookings;
use App\System\PMS\PMS;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\exceptions\PmsExceptions;
use App\SystemJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use App\Repositories\Settings\ParsePreferencesWithTemplateVar;
use App\UserPreference;


class PMSPreferencesListener
{

    use Queueable;

    public  $tries = 1;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PMSPreferencesEvent  $event
     * @return void
     */

    public function handle(PMSPreferencesEvent $event)
    {
        try {

            //check if status is disable for this preference then exit
            $user_preference_setting = UserPreference::where('user_account_id', $event->userAccount->id)->where('preferences_form_id', $event->preferenceFormId)->first();
            if($user_preference_setting && $user_preference_setting->status == 0)
                return;

            $propertyInfo = PropertyInfo::where('pms_property_id', $event->bookingInfo->property_id)->where('user_account_id', $event->userAccount->id)->first();
            $parsePreferences = new ParsePreferencesWithTemplateVar($event->userAccount, $event->bookingInfo,  $propertyInfo, $event->transactionInitId, $event->preferenceFormId, $event->authorizationId);
            $dataObject = $parsePreferences->parseTemplate();

            if (($dataObject) && ($dataObject != null)) {
                $dataObject->preferenceFormId = $event->preferenceFormId;

                $preQueuedJobs = SystemJob::where('model_name' , SystemJob::PMS_PREFERENCES_MODEL_NAME)->
                where('dispatch_description', SystemJob::PMS_PREFERENCES_DESCRIPTION)->
                where('lets_process', 1)->
                where('booking_info_id', $event->bookingInfo->id)->
                where('status', SystemJob::STATUS_PENDING)->count();

                $systemJob = SystemJob::create([
                'user_account_id' => $event->userAccount->id,
                'booking_info_id' => $event->bookingInfo->id,
                'model_name' => SystemJob::PMS_PREFERENCES_MODEL_NAME,
                'model_id' => 0,
                'dispatch_description' => SystemJob::PMS_PREFERENCES_DESCRIPTION,
                'due_date' => now()->toDateTimeString(),
                'json_data' => json_encode($dataObject),
                'attempts' => 0,
                'status' => SystemJob::STATUS_PENDING,
                'lets_process' => 1]);

                if($preQueuedJobs == 0){
                    /**
                     * Set Preferences data on Booking Object */
                    $bookingModelObject = Bookings::setPreferencesDataToUpdate(json_decode($systemJob->json_data, true), $propertyInfo->pms_property_id);
                    /**
                     * UPDATE PMS  */
                    Bookings::updatePMSPreferencesWithSystemJob($systemJob, $bookingModelObject);
                }
                /* Else PMSPreferencesUpdateJob will handle this request as in queue */
            }
        }catch (PmsExceptions $e) {
            Log::error($e->getTraceAsString(), ['File'=>PMSPreferencesListener::class]);
            report($e);
        }catch (\Exception $e) {
            Log::error($e->getMessage(), ['Line'=>$e->getLine(), 'File'=>PMSPreferencesListener::class]);
        }
    }
}