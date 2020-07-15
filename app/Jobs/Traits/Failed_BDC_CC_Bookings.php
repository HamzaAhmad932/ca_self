<?php

namespace App\Jobs\Traits;

use App\BookingInfo;
use App\Events\Emails\EmailEvent;
use App\Repositories\NotificationAlerts;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CreditCardAuthorization;

/**
 *
 * @author mmammar
 */
trait Failed_BDC_CC_Bookings {
    
    public $hours48 = (48 * 60) * 60; // making seconds
    public $hours24 = (24 * 60) * 60; // making seconds
    public $hours12 = (12 * 60) * 60; // making seconds
    public $hours02 = ( 2 * 60) * 60; // making seconds
        
    public function getBookingDataDB() {

        $sql = "SELECT 
    bi.id AS bi_id, bi.user_account_id AS u_a_id, bi.user_id, bi.pms_booking_id, bi.guest_title, bi.guest_name, 
    bi.guest_last_name, bi.guest_phone, bi.property_id AS pms_property_id,bi.check_in_date, bi.check_out_date, 
    bi.booking_time, bi.card_invalid_report_time, bi.updated_at AS bi_updated_at, 

    ti.id AS ti_id, ti.split, ti.payment_status AS ti_payment_status, 
    pi.property_email, pi.name AS pi_name, pi.logo AS pi_logo, pi.time_zone, 
    ua.email AS user_account_email, ua.name AS user_account_name, 
    
    cca.status AS cca_status, cca.type AS cca_type, 
    
    cci.auth_token, cci.status AS cci_status

    FROM booking_infos AS bi

    LEFT JOIN transaction_inits AS ti ON ti.booking_info_id = bi.id
    INNER JOIN property_infos AS pi ON pi.pms_property_id = bi.property_id
    INNER JOIN user_accounts AS ua ON ua.id = bi.user_account_id
    LEFT JOIN credit_card_authorizations AS cca ON cca.booking_info_id = bi.id
    INNER JOIN credit_card_infos AS cci ON cci.id = (select id from credit_card_infos as cc where cc.booking_info_id = bi.id ORDER BY cc.id desc LIMIT 1)

    WHERE 
    bi.cancel_email_sent = 0 AND 
    bi.manual_canceled = 0 AND 
    bi.is_vc = 'CC' AND 
    bi.channel_code = 19 AND 
    bi.cancellationTime IS NULL AND 
    bi.card_invalid_report_time IS NOT NULL AND 
    bi.is_pms_reported_for_invalid_card = 1 AND 
    pi.user_account_id = bi.user_account_id AND 
    
    DATE(NOW() - INTERVAL 3 DAY) <= DATE(bi.card_invalid_report_time) 

    ORDER BY ti.split ASC";

        return DB::select($sql);

    }

   /** 
    * 
    * Please have a look at the following BDC terms defined at BDC for in-depth understanding.
    * 
    * canceling guest bookings in the case of invalid credit cards
    * 
    * If you don’t receive updated credit card details within 24 hours, or the guest provides 
    * invalid credit card details again, you can cancel the booking through the Reservations tab on 
    * the Extranet or on the Reservation details screen in the Pulse app. You can also cancel bookings 
    * until 3 pm (local time) on the day of arrival.
    * 
    * For bookings made within 48 hours of check-in, the customer will have 12 hours 
    * (or until 3 pm, whichever is earlier) to update these details (instead of the usual 24 hours) If the card is invalid.
    * 
    * The customer is always given at least 2 hours to update these details, 
    * e.g. if the booking is made after 2 pm on the day of arrival.
    * 
    * For last-minute bookings of 10 or more room rights, 
    * partners can cancel 2 hours after marking the credit as invalid.
    * 
    * Note: For some reservations, we’ll proactively ask the customer to provide new details. 
    * You won’t need to request these yourself or report the invalid credit card. 
    * If no new details are provided, you can decide how to continue with the booking.
    * 
    * We send a daily email showing your most recently received and updated credit card details. 
    * You can see an overview of invalid credit card statuses by using the "updated" and "pending" 
    * filters in the Reservations tab.
    * 
    */
    public function shouldSendMailByCheckingTimeLogic(Carbon $bookingTime, Carbon $checkInTime, Carbon $reportTime, Carbon $now) {

        // Checking if check-in is after 48 hours from booking time
        $give24Hours = $checkInTime->diffInSeconds($bookingTime) >= $this->hours48;
        $hoursFromTimeOfReport = $reportTime->diffInSeconds($now);
        $isCheckInToday = $checkInTime->isSameDay($now);
        $isSameDayBooking = $checkInTime->isSameDay($bookingTime);

        if($checkInTime->isBefore($bookingTime)) {

            $newCheckInTime = new Carbon();
            $newCheckInTime->setDateFrom($checkInTime);
            $newCheckInTime->setTimezone($checkInTime->getTimezone());
            $newCheckInTime->setTime(23, 59, 59);

            // This means card is being reported on next day. so we send email immediately
            if($newCheckInTime->isBefore($reportTime))
                return true;

            if($hoursFromTimeOfReport < $this->hours02 && $now->isBefore($newCheckInTime))
                return false;
            
//            if($checkInTime->diffInHours($bookingTime) >= 2)
//                return true;
//            else
//                return false;

        } elseif($isSameDayBooking && $bookingTime->hour >= 13) {

            if ($hoursFromTimeOfReport < $this->hours02)
                return false;

        } else {

            if ($give24Hours) {

                if(!($isCheckInToday && $now->hour >= 15) && $hoursFromTimeOfReport < $this->hours24)
                    return false;

            } else {

                if (!($isCheckInToday && $now->hour >= 15) && $hoursFromTimeOfReport < $this->hours12)
                    return false;

            }
        }
        return true;
    }
    
    public function shouldSendMailByCheckingTimeLogicTest(Carbon $bookingTime, Carbon $checkInTime, Carbon $reportTime, Carbon $now) {

        // Checking if check-in is after 48 hours from booking time
        $give24Hours = $checkInTime->diffInSeconds($bookingTime) >= $this->hours48;
        $hoursFromTimeOfReport = $reportTime->diffInSeconds($now);
        $isCheckInToday = $checkInTime->isSameDay($now);
        $isSameDayBooking = $checkInTime->isSameDay($bookingTime);

        if($checkInTime->isBefore($bookingTime)) {

            $newCheckInTime = new Carbon();
            $newCheckInTime->setDateFrom($checkInTime);
            $newCheckInTime->setTimezone($checkInTime->getTimezone());
            $newCheckInTime->setTime(23, 59, 59);

            // This means card is being reported on next day. so we send email immediately
            if($newCheckInTime->isBefore($reportTime))
                return true;

            if($hoursFromTimeOfReport < $this->hours02 && $now->isBefore($newCheckInTime)) {
                return '2HB<sup>1st</sup> remaining hours are ' . number_format(((abs($hoursFromTimeOfReport - $this->hours02)/60)/60), 2);
            }

//            if($checkInTime->diffInHours($bookingTime) >= 2)
//                return true;
//            else
//                return '2HB<sup>1st</sup> remaining hours are ' . number_format(((abs($hoursFromTimeOfReport - $this->hours02)/60)/60), 2);

        } elseif($isSameDayBooking && $bookingTime->hour >= 13) {

            if ($hoursFromTimeOfReport < $this->hours02)
                return '2HB<sup>2nd</sup> remaining hours are ' . number_format(((abs($hoursFromTimeOfReport - $this->hours02)/60)/60), 2);

        } else {

            if ($give24Hours) {

                if(!($isCheckInToday && $now->hour >= 15) && $hoursFromTimeOfReport < $this->hours24)
                    return '24HB remaining hours are ' . number_format(((abs($hoursFromTimeOfReport - $this->hours24)/60)/60), 2);

            } else {
                
                if (!($isCheckInToday && $now->hour >= 15) && $hoursFromTimeOfReport < $this->hours12) 
                    return '12HB remaining hours are ' . number_format(((abs($hoursFromTimeOfReport - $this->hours12)/60)/60), 2);

            }
        }
        return true;
    }

    public function checkByTransactionAndOtherStatus(array $rows, \stdClass $row, int $oneOfTwo, int $twoOfTwo, int $oneOfOne) {
        
        try {

            if ($row->cca_status == CreditCardAuthorization::STATUS_FAILED || $row->cca_status == CreditCardAuthorization::STATUS_REATTEMPT) {
                return true;
            }

            // When booking had Single Transaction
            if ($row->split == $oneOfOne && ($row->ti_payment_status == 0 || $row->ti_payment_status == 4)) {
                return true;
            }

            // When booking have Multiple Transactions
            $searchForOtherSplit = $row->split == $oneOfTwo ? $twoOfTwo : $oneOfTwo;
            $bi_id = $row->bi_id;

            $isOtherRecordExists = false;
            foreach ($rows as $subRow) {
                if ($subRow->split == $searchForOtherSplit && $bi_id == $subRow->bi_id) {
                    $isOtherRecordExists = true;
                    break;
                }
            }

            if ($isOtherRecordExists) {
                return true;
            }
            
            if(empty($row->auth_token))
                return true;
            
        } catch(\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Stack' => $e->getTraceAsString()
            ]);
        }

        return false;

    }

    public function sendEmail(\stdClass $booking) {

        try {

            event(new EmailEvent(config('db_const.emails.heads.payment_passed_due_date.type'), $booking->bi_id ));
            return true;

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File'=>SearchFailed_BDC_CC_bookingsJob::class, 
                'Function' => __FUNCTION__, 
                'Stack'=>$e->getTraceAsString()]);
        }

        return false;
    }

    public function updateBookingInfo(\stdClass $booking) {

        try {

            $bookingInfo = BookingInfo::where('id', $booking->bi_id)->first();
            $bookingInfo->cancel_email_sent = 1;
            $bookingInfo->save();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File'=>SearchFailed_BDC_CC_bookingsJob::class, 
                'Function' => __FUNCTION__, 
                'Stack'=>$e->getTraceAsString()]);
        }
    }

    public function createNotification(\stdClass $booking) {

        try {

            //create alert for the same to show notification
            //use common repo to create alert
            $notificationRepo = new NotificationAlerts($booking->user_id, $booking->u_a_id);
            $notificationRepo->create($booking->bi_id, 0, 'payment_past_due', $booking->pms_booking_id, 1);

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File'=>SearchFailed_BDC_CC_bookingsJob::class, 
                'Function' => __FUNCTION__, 
                'Stack'=>$e->getTraceAsString()]);
        }
    }

    
}
