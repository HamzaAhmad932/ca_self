<?php

namespace App\Jobs;


use App\Repositories\Settings\PaymentTypeMeta;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\Traits\Failed_BDC_CC_Bookings;


class SearchFailed_BDC_CC_bookingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Failed_BDC_CC_Bookings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try {
            
            $bookings = $this->filterBookingsForEmail();

            foreach ($bookings as $booking) {
                
                if($this->sendEmail($booking))
                    $this->updateBookingInfo ($booking);
                
                $this->createNotification($booking);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>SearchFailed_BDC_CC_bookingsJob::class, 'Stack'=>$e->getTraceAsString()]);
        }

    }
    
    public function filterBookingsForEmail() {
        
        $rows = $this->getBookingDataDB();

        $p = new PaymentTypeMeta();

        $oneOfTwo = $p->getBookingPaymentAutoCollectionPartial1of2();
        $twoOfTwo = $p->getBookingPaymentAutoCollectionPartial2of2();
        $oneOfOne = $p->getBookingPaymentAutoCollectionFull();

        $bookings = [];

        foreach ($rows as $row) {

            try {

                $tz = $row->time_zone;
                $now = now()->setTimezone($tz);
                $bookingTime = Carbon::parse($row->booking_time, 'GMT')->setTimezone($tz);
                $checkInTime = Carbon::parse($row->check_in_date, 'GMT')->setTimezone($tz);
                $reportTime = Carbon::parse($row->card_invalid_report_time, 'GMT')->setTimezone($tz);

                $isTimeToSendEmail = $this->shouldSendMailByCheckingTimeLogic($bookingTime, $checkInTime, $reportTime, $now);
                if(!$isTimeToSendEmail)
                    continue;

                $hasFailedStatus = $this->checkByTransactionAndOtherStatus($rows, $row, $oneOfTwo, $twoOfTwo, $oneOfOne);
                if($hasFailedStatus)
                    if (!key_exists($row->bi_id, $bookings))
                        $bookings[$row->bi_id] = $row;

            } catch (\Exception $e) {

                Log::error($e->getMessage(), [
                    'File'=>SearchFailed_BDC_CC_bookingsJob::class, 
                    'Function' => __FUNCTION__,
                    'Stack'=>$e->getTraceAsString()]);

            }

        }
        
        return $bookings;
        
    }


}
