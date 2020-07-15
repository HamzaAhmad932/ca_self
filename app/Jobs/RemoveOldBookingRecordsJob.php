<?php

namespace App\Jobs;

use App\BookingInfo;
use App\BookingInfoDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class RemoveOldBookingRecordsJob implements ShouldQueue
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
        $two_months_ago = now()->subMonths(2)->toDateTimeString();

        $records = BookingInfoDetail::whereIn('booking_info_id',
            BookingInfo::select('id')
                ->where('check_out_date', '<' , $two_months_ago)
                ->pluck('id')
        )->delete();

        Log::notice( 'Booking Infos having checkout date less than '. $two_months_ago .' details Removed ',
            ['records' => $records]
        );
    }
}
