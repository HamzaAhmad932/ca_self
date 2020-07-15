<?php

namespace App\Jobs;

use App\BookingSourceForm;
use App\Events\Emails\EmailEvent;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\UpsellNotifyDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpsellMarketingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $email_key = config('db_const.emails.heads.upsell_marketing.type');
        $upsell_key = config('db_const.general_preferences_form.upsell');
        $bookings = $this->getBookingListWithUpsell();
        $booking_sources = BookingSourceForm::all();
        $emails_sent = array(); // Booking_info_ids whom upsell email already sent
        $instances = array();  // User GeneralSetting Instances with user_account_id key

        foreach ($bookings as $booking) {
            if (! in_array($booking->booking_info_id, $emails_sent)) {

                $booking_source = $booking_sources->where('channel_code', $booking->channel_code)
                    ->where('pms_form_id', $booking->pms_form_id)->first();

                $instances[$booking->user_account_id] = empty($instances[$booking->user_account_id])
                    ? new ClientGeneralPreferencesSettings($booking->user_account_id)
                    : $instances[$booking->user_account_id]; // Load new instance if not initialized before.

                $send_email = !empty($instances[$booking->user_account_id]->isActiveStatus($upsell_key, $booking_source));

                if ($send_email) { // Guest experience, can purchase Upsell status

                    $upsell_ids = $bookings->where('booking_info_id', $booking->booking_info_id)->pluck('upsell_id')->toArray();

                    event(new EmailEvent($email_key, $booking->booking_info_id, ['upsell_ids' => $upsell_ids]));

                    array_push($emails_sent, $booking->booking_info_id); // Avoid to send emails duplication
                    $this->addLogs($booking->booking_info_id, $upsell_ids);     // Add Detail Logs for entertained upsells
                    Log::info('Upsell Marketing Email sent', ['booking_info_id' => $booking->booking_info_id, 'upsells' => $upsell_ids]);
                }
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    private function getBookingListWithUpsell()
    {
        return collect(DB::select(DB::raw($this->getQueryString())));
    }



    /**
     * get Only those bookings against upsells attached and active
     * and notify guest to purchase before check-in days match with booking check-in date and booking not cancel
     * after query on string it return array containing booking_infos.id as booking_info_id, booking_infos.check_in_date,
     * booking_infos.user_account_id, booking_infos.pms_id as pms_form_id, booking_infos.channel_code,
     * upsells.id as upsell_id
     * @return string
     */
    private function getQueryString()
    {
        return "SELECT bk.id as booking_info_id, bk.check_in_date, bk.user_account_id, bk.pms_id as pms_form_id, bk.channel_code, up.id as upsell_id FROM booking_infos as bk                           
            INNER JOIN  room_infos as rm
            ON rm.property_info_id = bk.property_info_id and rm.pms_room_id = bk.room_id 
            INNER JOIN upsells as up
            ON up.user_account_id = bk.user_account_id and up.status = 1 and up.notify_guest > 0 and up.notify_guest = DATEDIFF(bk.check_in_date, NOW())
            and up.id NOT IN (select notify.upsell_id FROM upsell_notify_details as notify where notify.booking_info_id = bk.id)
            and up.id NOT IN (select order_d.upsell_id FROM upsell_order_details as order_d INNER JOIN  upsell_orders as up_order ON order_d.upsell_order_id = up_order.id 
            where up_order.booking_info_id = bk.id and up_order.status = 1)
            INNER JOIN  upsell_properties_bridges as bridge
            ON bridge.property_info_id = bk.property_info_id  and bridge.upsell_id = up.id
            and (bridge.room_info_ids is NULL or bridge.room_info_ids LIKE CONCAT('%\"',rm.id,'\"%'))
            and  bk.check_in_date > NOW() and bk.pms_booking_status != 0 and cancellationTime is  NULL order by bk.check_in_date ASC";
    }

    /**
     * @param $booking_info_id
     * @param $upsell_ids
     */
    private function addLogs($booking_info_id, $upsell_ids)
    {
        $now = now()->toDateTimeString();
        $logs = array();
        foreach ($upsell_ids as $upsell_id) {
            array_push($logs,
                [
                    'booking_info_id' => $booking_info_id,
                    'upsell_id' => $upsell_id,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (!empty($logs))
            UpsellNotifyDetail::insert($logs);
    }
}
