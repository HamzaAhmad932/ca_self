<?php

namespace App\Jobs;

use App\Jobs\SyncBooking\BASyncBookingJob;
use App\Jobs\SyncBooking\SyncBookingTrait;
use App\UserAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SyncBookingTrait;

    /**
     * @var bool
     */
    private $custom_dispatch;
    /**
     * @var int |null
     */
    private $user_account_id;


    /**
     * SyncBookingJob constructor.
     * @param bool $custom_dispatch
     * @param int |null $user_account_id
     */
    public function __construct(bool $custom_dispatch = false, int $user_account_id = null)
    {
        self::onQueue('ba_syc_bookings');

        $this->user_account_id = $user_account_id;
        $this->custom_dispatch = $custom_dispatch;

        /*Log::info(__CLASS__ . ' Dispatched',
            [
                'custom' => $custom_dispatch,
                'user_account_id' => $user_account_id
            ]
        );*/

    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        /**
         * @var $user_account UserAccount
         */
        $this->init_booking_job_helper();

        $user_accounts = $this->userAccounts();

        foreach ($user_accounts as $user_account) {

            $this->syncBookings($user_account);
            $this->updateLastSync($user_account);

            unset($user_account);
        }
    }


    /**
     * @param UserAccount $user_account
     * dispatch Sync Booking Job Property wise and for only those properties whose
     * new bookings are available on PMS and Valid to sync
     */
    private function syncBookings(UserAccount $user_account)
    {
        // Filter properties whose new bookings available on PMS.
        $properties = $user_account->activeProperties->whereIn('pms_property_id',
            $this->properties($user_account)
        );

        $after_minutes = 0;
        foreach ($properties as $index => $property) {

            BASyncBookingJob::dispatch($user_account, $property)
                ->onQueue('ba_syc_bookings')
                ->delay(now()->addMinutes($after_minutes)->toDateTimeString());

            $after_minutes += 5;
        }
    }

}
