<?php

namespace App\Jobs;

use App\BookingInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GuestDocumentsCheckToUpdatePMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * GuestDocumentsCheckToUpdatePMSJob constructor.
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
        /**
         * Get All Limited Bookings whose documents are missing, and not uploaded within 24 Hours
         */
        $newBookings = BookingInfo::where('created_at' , '<', now()->addDay(-1)->toDateTimeString())->where('document_status_updated_on_pms', 0)->limit(20)->get();

        foreach ($newBookings as $bookingInfo){
            /**
             * Dispatch Job To Trigger Listener to Update PMS
             */
            GuestDocumentsStatusUpdateOnPMSJob::dispatch($bookingInfo, false);
            unset($bookingInfo);
        }
    }
}
