<?php

namespace App\Jobs;

use App\BookingInfo;
use App\Events\PMSPreferencesEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GuestDocumentsStatusUpdateOnPMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var BookingInfo
     */
    private $bookingInfo;
    private $isSuccess;

    /**
     * GuestDocumentsStatusUpdateOnPMSJob constructor.
     * @param BookingInfo $bookingInfo
     * @param bool $isSuccess
     */

    public function __construct(BookingInfo $bookingInfo , $isSuccess = true)
    {
        $this->bookingInfo = $bookingInfo;
        $this->isSuccess   = $isSuccess;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $preferenceFormId = ($this->isSuccess ? config('db_const.user_preferences.preferences.VERIFICATION_DOCUMENTATION_UPLOADED_SUCCESSFULLY') : config('db_const.user_preferences.preferences.VERIFICATION_DOCUMENTATION_UPLOADED_FAIL'));
        /**
         * Update Preferences on PMS
         */
        event(new PMSPreferencesEvent($this->bookingInfo->user_account, $this->bookingInfo, 0, $preferenceFormId, 0));
        /**
         * Update Booking column document_status_updated_on_pms = 1
         */
        $this->bookingInfo->document_status_updated_on_pms = 1;
        $this->bookingInfo->save();
    }
}
