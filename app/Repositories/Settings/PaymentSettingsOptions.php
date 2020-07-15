<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 27-Dec-18
 * Time: 4:47 PM
 */

namespace App\Repositories\Settings;


/**
 * Class PaymentSettingsOptions
 * @package App\Repositories\Settings
 * @property $guest_name
 * @property $property_info_id
 * @property $user_account_id
 * @property $booking_id
 * @property $booking_source_id
 * @property $checkInDate
 * @property $checkOutDate
 * @property $bookingTime
 * @property $totalAmount
 * @property $timeZone
 * @property $cancellationTime
 * @property $isNonRefundable
 */
class PaymentSettingsOptions
{
    public $property_info_id;
    public $user_account_id;
    public $booking_id;
    public $booking_source_id;
    public $checkInDate;
    public $checkOutDate;
    public $bookingTime;
    public $totalAmount;
    public $timeZone = null;
    public $cancellationTime; //for cancel booking
    public $isNonRefundable = false; //Default False | True If Booking is Non-refundable

}