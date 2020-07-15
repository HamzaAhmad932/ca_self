<?php

namespace App\Jobs;

use App\BookingInfo;
use App\Events\Emails\EmailEvent;
use App\Events\RemindGuestToCompletePreCheckinWizardEvent;
use App\GuestData;
use App\Jobs\EmailJobs\EmailJob;
use App\Mail\GenericEmail;
use App\Repositories\Bookings\BookingRepository;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\BookingSources\BookingSources;
use function foo\func;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindGuestToCompletePreCheckinWizard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('ba_sync_properties');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bookingInfos = BookingInfo::where('pre_checkin_status', 0)
            ->where('pms_booking_status', '!=', 0)
            ->where('pre_checkin_email_status', '!=', 3)
            ->whereDate('pre_checkin_email_status_update', '!=', now()->toDateString())
            ->whereDate('booking_time', '!=', now()->toDateString())
            ->where(function($query){
                $query->whereBetween('check_in_date', [now()->addDays(1)->startOfDay()->toDateTimeString(), now()->addDays(1)->endOfDay()->toDateTimeString()])->
                orWhereBetween('check_in_date', [now()->addDays(5)->startOfDay()->toDateTimeString(), now()->addDays(5)->endOfDay()->toDateTimeString()])
                    ->orWhereBetween('check_in_date', [now()->addDays(10)->startOfDay()->toDateTimeString(), now()->addDays(10)->endOfDay()->toDateTimeString()]);
            })
            ->limit(10)
            ->get();

        $Today = Carbon::now();
        $bookingRepository = new BookingRepository();

        if (!empty($bookingInfos)) {
            foreach ($bookingInfos as $booking_info) {

                if (!empty($booking_info->guest_email) && $booking_info->guest_email != '' && $booking_info->guest_email != null) {

                    /*
                     * If booking is not from supported channels then we use others channel setting for email sending
                     */
                    //$booking_source_form_id = BookingSources::getBookingSourceFormIdForGuestExperience($booking_info->channel_code);

                    $notifySettings = new ClientGeneralPreferencesSettings($booking_info->user_account_id);
                    $notify = $notifySettings->isActiveStatus(config('db_const.general_preferences_form.emailToGuest'),
                        $booking_info->bookingSourceForm);

                    if ($notify) {

                        // send email to guest -- call the same email which we use to send for new booking
                        //event(new EmailEvent(config('db_const.emails.heads.new_booking.type'),$booking_info->id));

                        EmailJob::dispatch(config('db_const.emails.heads.new_booking.type'), 'guest', $booking_info->id)->onQueue('send_email');

                        $checkInDate = Carbon::parse($booking_info->check_in_date);

                        if ($checkInDate->diffInDays($Today) == 10) {
                            $booking_info->pre_checkin_email_status = 1;
                        } else if ($checkInDate->diffInDays($Today) == 5) {
                            $booking_info->pre_checkin_email_status = 2;
                        } else if ($checkInDate->diffInDays($Today) == 1) {
                            $booking_info->pre_checkin_email_status = 3;
                        }

                        $booking_info->pre_checkin_email_status_update = Carbon::now()->toDateTimeString();
                        $booking_info->save();
                    }

                }

            }
        }
    }
}
