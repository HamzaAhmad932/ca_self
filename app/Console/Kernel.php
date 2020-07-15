<?php

namespace App\Console;

use App\Jobs\SyncBookingJob;
use App\Jobs\Auth\BA\AuthJob;
use App\Jobs\Auth\BA\ReAuthJob;
use App\Jobs\UpsellMarketingJob;
use App\Jobs\Refund\AutoRefundJob;
use Illuminate\Support\Facades\Log;
use App\Jobs\ReTryForCustomerObject;
use App\Jobs\PMSPreferencesUpdateJob;
use App\Jobs\OpenExchangeRatesSyncJob;
use App\Jobs\RemoveOldBookingRecordsJob;
use App\Jobs\VCCustomerObjectCreationJob;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\SearchFailed_BDC_CC_bookingsJob;
use App\Jobs\SyncProperties\BASyncPropertyJob;
use App\Jobs\GuestDocumentsCheckToUpdatePMSJob;
use App\Jobs\RemindGuestToCompletePreCheckinWizard;
use App\Jobs\HandlePMSUsageLimitExceededExceptionJob;
use App\Jobs\Charge\BA\ReAttemptTransactionChargeJob;
use App\Jobs\Charge\BA\FirstAttemptTransactionChargeJob;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\DatabaseCleanJobs\CheckAndCleanDatabaseTables;
use App\Jobs\DatabaseCleanJobs\SiteminderDeleteRawBookingData;
use App\Jobs\StripeCommissionBilling\BillingDetailsRequiredReminderJob;
use App\Jobs\StripeCommissionBilling\SyncUserCommissionBillingSubscriptionJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {

        try {
            $this->ba_vc_customer_object_creation($schedule);
            $this->ba_charge($schedule);
            $this->ba_auth($schedule);
            $this->ba_re_auth($schedule);
            $this->ba_sync_bookings($schedule);
            $this->ba_sync_properties($schedule);
            $this->cancelEmailSendOn_CC_Tran_failure($schedule);
            $this->ba_re_attempt_failed_charges($schedule);
            $this->cc_customer_object_retry($schedule);
            $this->ba_auto_refund_job($schedule);
            $this->pms_preferences_update($schedule);
            $this->pms_limit_exceed_handle($schedule);
            $this->guest_booking_documents_uploaded_check_to_update_pms($schedule);
            $this->remind_guest_to_complete_pre_checkin_wizard($schedule);
            //$this->remind_guest_on_subscription_trial_ends($schedule);
            $this->OpenExchangeRatesSyncJob($schedule);
            $this->SyncUserCommissionBillingSubscriptionJob($schedule);
            $this->clean_database_tables($schedule);
            $this->clean_siteminders_raw_booking_table($schedule);
            $this->UpsellMarketingJob($schedule);
            $this->RemoveOldBookingRecordsJob($schedule);
            // $this->runDummyJob($schedule);
//            $this->createTestBookingsAndRoomsJob($schedule);

            // Backups database dump every 4 hours for live app only
            if( config('app.env') == "production" &&  config('app.debug') === false ) {
                $schedule->command('backup:run --only-db')->cron('0 */4 * * *');
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
               'file' => 'Kernel',
               'stack' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    private function ba_vc_customer_object_creation(Schedule $schedule) {

        $scheduleTime =  ((config('app.env') === 'local' || config('app.debug') == true)) ? 'everyFiveMinutes'
            : 'everyMinute';

        $schedule->job(VCCustomerObjectCreationJob::class, 'ba_charge')
            ->$scheduleTime()
            ->name('vc_customer_object_create')
            ->withoutOverlapping();
    }

    private function ba_charge(Schedule $schedule) {

        $schedule->job(FirstAttemptTransactionChargeJob::class, 'ba_charge')
            ->everyMinute()
            ->name('ba_charge')
            ->withoutOverlapping();

    }

    private function ba_sync_bookings(Schedule $schedule) {

        $schedule->job(SyncBookingJob::class, 'ba_syc_bookings')
            ->everyFiveMinutes()
            ->name('ba_sync_bookings')
            ->withoutOverlapping();
    }

    private function clean_database_tables(Schedule $schedule) {
        //Run this job 2 times daily at 1:00AM and 1:00PM
        $schedule->job(CheckAndCleanDatabaseTables::class, 'clean_database_tables')
            ->twiceDaily(1, 13)
            ->name('clean_database_tables')
            ->withoutOverlapping();
    }

    private function clean_siteminders_raw_booking_table(Schedule $schedule) {
        //will run daily at 1AM GMT
        $schedule->job(SiteminderDeleteRawBookingData::class, 'clean_database_tables')
            ->dailyAt('01:00')
            ->name('clean_siteminders_raw_booking_table')
            ->withoutOverlapping();
    }

    private function ba_re_auth(Schedule $schedule) {

        $schedule->job(ReAuthJob::class, 'reauth')
            ->everyFiveMinutes()
            ->name('reauth')
            ->withoutOverlapping();
    }

    private function ba_auth(Schedule $schedule) {

        $schedule->job(AuthJob::class, 'reauth')
            ->everyFiveMinutes()
            ->name('reauth')
            ->withoutOverlapping();
    }

    private function ba_sync_properties(Schedule $schedule) {

        $schedule->job(BASyncPropertyJob::class, 'ba_sync_properties')
            ->everyThirtyMinutes()
            ->name('ba_sync_properties')
            ->withoutOverlapping();
    }

    private function cancelEmailSendOn_CC_Tran_failure(Schedule $schedule) {

        $schedule->job(SearchFailed_BDC_CC_bookingsJob::class, 'ba_sync_properties')
            ->everyFiveMinutes()
            ->name('search_failed_BDC_CC_bookings_job')
            ->withoutOverlapping();
    }

    private function ba_re_attempt_failed_charges(Schedule $schedule) {

        $schedule->job(ReAttemptTransactionChargeJob::class, 'reattempt')
            ->everyFiveMinutes()
            ->name('reattempt')
            ->withoutOverlapping();
    }

    private function cc_customer_object_retry(Schedule $schedule) {

        $scheduleTime =  ((config('app.env') === 'local' || config('app.debug') == true)) ? 'everyFiveMinutes'
            : 'everyMinute';

        $schedule->job(ReTryForCustomerObject::class, 'reattempt')
            ->$scheduleTime()
            ->name('ReTryForCustomerObject')
            ->withoutOverlapping();
    }

    private function ba_auto_refund_job(Schedule $schedule) {

        $schedule->job(AutoRefundJob::class, 'auto_refund')
            ->everyFiveMinutes()
            ->name('auto_refund')
            ->withoutOverlapping();
    }

    private function pms_preferences_update(Schedule $schedule) {

        $schedule->job(PMSPreferencesUpdateJob::class, 'ba_syc_bookings')
            ->everyThirtyMinutes()
            ->name('pms_pref_update')
            ->withoutOverlapping();
    }
    private function pms_limit_exceed_handle(Schedule $schedule) {

        $schedule->job(HandlePMSUsageLimitExceededExceptionJob::class, 'ba_syc_bookings')
            ->everyThirtyMinutes()
            ->name('pms_limit_exceed_handle')
            ->withoutOverlapping();
    }

    private function guest_booking_documents_uploaded_check_to_update_pms(Schedule $schedule) {

        $schedule->job(GuestDocumentsCheckToUpdatePMSJob::class, 'ba_syc_bookings')
            ->everyThirtyMinutes()
            ->name('booking_documents_uploaded_check')
            ->withoutOverlapping();
    }

    private function remind_guest_to_complete_pre_checkin_wizard(Schedule $schedule) {

        $schedule->job(RemindGuestToCompletePreCheckinWizard::class, 'ba_sync_properties')
//            ->cron('0 */3 * * *')
            ->everyFiveMinutes()
            ->name('RemindGuestToCompletePreCheckinWizard')
            ->withoutOverlapping();
    }

    private function remind_guest_on_subscription_trial_ends(Schedule $schedule) {

        $schedule->job(BillingDetailsRequiredReminderJob::class, 'ba_sync_properties')
//            ->twiceDaily(1,13)
            ->everyFifteenMinutes()
            ->name('SubscriptionTrialReminderEmailJob')
            ->withoutOverlapping();
    }

    private function OpenExchangeRatesSyncJob(Schedule $schedule) {
        $schedule->job(OpenExchangeRatesSyncJob::class, 'ba_sync_properties')
            ->hourly()
            ->name('OpenExchangeRatesSyncJob')
            ->withoutOverlapping();
    }
    private function SyncUserCommissionBillingSubscriptionJob(Schedule $schedule) {
        $schedule->job(SyncUserCommissionBillingSubscriptionJob::class, 'ba_sync_properties')
            ->everyFifteenMinutes()
            ->name('SyncUserCommissionBillingSubscriptionJob')
            ->withoutOverlapping();
    }

    private function UpsellMarketingJob(Schedule $schedule) {
        $schedule->job(UpsellMarketingJob::class, 'ba_sync_properties')
            ->daily()
            ->name('UpsellMarketingJob')
            ->withoutOverlapping();
    }

    private function RemoveOldBookingRecordsJob(Schedule $schedule) {
        $schedule->job(RemoveOldBookingRecordsJob::class, 'ba_sync_properties')
            ->dailyAt('07:00')->name('RemoveOldBookingRecordsJob')->withoutOverlapping();
    }

    private function runDummyJob(Schedule $schedule) {
//        if(config('app.url') == 'https://testapptor1a.chargeautomation.com') {
//            $schedule->job(DummyJob::class, 'reauth')
//    //                ->cron('*/2 * * * *')
//                    ->everyMinute()
//                    ->name('DummyJob')
//                    ->withoutOverlapping();
//        }
    }

//    private function createTestBookingsAndRoomsJob(Schedule $schedule)
//    {
//        $schedule->job(CreateTestBookingsAndRoomsJob::class, 'clean_database_tables')
//            ->name('CreateTestBookingsAndRoomsJob')
//            ->everyFiveMinutes()
//            ->withoutOverlapping();
//    }
}
