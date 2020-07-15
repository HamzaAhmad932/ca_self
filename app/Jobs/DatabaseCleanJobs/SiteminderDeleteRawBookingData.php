<?php

namespace App\Jobs\DatabaseCleanJobs;

use App\Siteminder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SiteminderDeleteRawBookingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*
         * delete all records where checkin date is 2 months older from now
         */
        $delete = Siteminder::where('check_in_date', '<=', Carbon::now()->subDays(60))->delete();

        if($delete){
            Log::notice('Siteminders raw bookings with 2 month old checkin date are deleted.', [
                'table_name' => "siteminders"
            ]);
        }
    }

}
