<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\BookingInfo;
use App\User;
use App\UserAccount;

function getRandomUserAccountAndUser()
{
    $user_account_id = UserAccount::all()->random()->id;
    $user_id = User::where('user_account_id', $user_account_id)->pluck('id')->first();

    if (!empty($user_id)) {
        return ['user_account_id' => $user_account_id, 'user_id' => $user_id];
    } else {
        return getRandomUserAccountAndUser();
    }
}

/*$factory->define(BookingInfo::class, function (Faker $faker) {
    $guest_title = ['Prof.', 'Ms.', 'Mrs.', 'Dr.', 'Miss', 'Mr.'];
    $currency_code = ['USD', 'AZN', 'GBP', 'THB', 'AED', 'MZN', 'KRW', 'NZD', 'EUR'];
    $time_zone = ['America/Toronto', 'Asia/Tokyo', 'America/Vancouver', 'Asia/Kolkata', 'America/Vancouver', 'Asia/Karachi', 'Australia/Sydney', 'Asia/Kolkata', 'America/Vancouver'];
    $user = getRandomUserAccountAndUser();
    $pms_booking_id = BookingInfo::orderByRaw('CAST(pms_booking_id AS SIGNED INTEGER) DESC')->pluck('pms_booking_id')->first() == null ? 1 : BookingInfo::orderByRaw('CAST(pms_booking_id AS SIGNED INTEGER) DESC')->pluck('pms_booking_id')->first();

    return [
        'pms_booking_id' => $faker->unique()->numberBetween($pms_booking_id+1, $pms_booking_id+2284),
        'master_id' => -1,
        'bs_booking_id' => 'setBooking JSON',
        'user_id' => 39,
        'user_account_id' => $user['user_account_id'],
        'pms_id' => 1,
        'channel_code' => $user['user_id'],
        'property_id' => mt_rand(10000,99999),
        'property_info_id' => mt_rand(10000,99999),
        'room_id' => mt_rand(10000,99999),
        'guest_email' => $faker->unique()->safeEmail,
        'guest_title' => $guest_title[rand(0, 5)],
        'guest_phone' => mt_rand(1000000000,9999999999),
        'guest_name' => $faker->name,
        'guest_last_name' => $faker->name,
        'guest_zip_code' =>  mt_rand(1000,9999),
        'guest_post_code' =>  mt_rand(1000,9999),
        'guest_country' => $faker->country,
        'num_adults' => rand(1, 4),
        'guest_address' => $faker->address,
        'guest_currency_code' => $currency_code[rand(0,8)],
        'booking_time' => now()->subMonth(2),
        'pms_booking_modified_time' => now()->subMonth(2),
        'check_in_date' => now()->subMonth(2),
        'check_out_date' => now()->subMonth(1),
        'pms_booking_status' => rand(0, 1),
        'total_amount' => 10000,
        'booking_older_than_24_hours' => rand(0, 1),
        'is_vc' => 'CC',
        'is_manual' => rand(0, 1),
        'record_source' => rand(1, 2),
        'full_response' => '{"id":"17468888","roomId":"178255","unitId":"1","bookingStatus":1,"firstNight":"2020-02-28","lastNight":"2020-03-02","guestTitle":"Prof.","guestFirstName":"Gilbert","guestLastName":"Harber","guestEmail":"rsb94510@gmail.com","guestPhone":"(757) 285-5243 x8578","guestMobile":"(757) 285-5243 x8578","guestFax":"","guestAddress":"594 Hintz Parks Apt. 604East Jadenside, MS 80454-4032","guestCity":"North Art","guestPostcode":"00342","guestCountry":"Papua New Guinea","notes":null,"flagColor":"ffff00","flagText":"Paid","bookingStatusCode":"0","price":"2941.00","currencyCode":"USD","bookingReferer":"ch:19","refererOriginal":"setBooking JSON","bookingTime":"2020-02-28 04:18:02","bookingModifyTime":"2020-02-29 11:00:10","guestComments":"","guestArrivalTime":null,"numNight":"3","invoice":[{"id":"25866885","description":"Total Price","status":"1","quantity":"1","price":"2941.00","vatRate":"0.00","type":"199","action":""},{"id":"25866926","description":"Payment  ID  : ch_1GH0MXDRRAarpUctImmxm4EG","status":"1","quantity":"-1","price":"2941.00","vatRate":"0.00","type":"200","action":""}],"invoiceNumber":null,"invoiceDate":null,"apiMessage":"","message":null,"masterId":"","numberOfAdults":"2","channelReference":"","groupBookings":[],"propertyId":"77572","bookingIp":"142.93.148.152","channelCode":"19","action":"","balancePrice":2941,"cardType":null,"cardName":null,"cardNumber":null,"cardExpire":null,"cardCvv":null,"hostComments":" Aborting this transaction because its Paid (Partially Paid) on BookingAutomation.\n \nChargeAutomation.com Msg \nVerification Documents Uploaded Fail","infoItems":[{"code":"Payment Charged Succ","text":"Payment transaction Amount USD  2941 Charged on Fri 28 Feb 2020 09:52 ,   with Stripe"}]}',
        'is_process_able' => rand(0, 1),
        'cancellation_settings' => '{"status":true,"afterBooking":0,"afterBookingStatus":false,"beforeCheckIn":0,"beforeCheckInStatus":true,"rules":[{"canFee":0,"is_cancelled":0,"is_cancelled_value":0}],"isNonRefundable":false}',
        'payment_gateway_effected' => 0,
        'property_time_zone' => $time_zone[rand(0,8)],
        'document_status_updated_on_pms' => rand(0, 1),
        'is_pms_reported_for_invalid_card' => rand(0, 1),
        'created_at' => now()->subMonth(2),
        'updated_at' => now()->subMonth(2),
        'pre_checkin_status' => 1,
        'guestMobile' => mt_rand(1000000000,9999999999),
        'guestCity' => $faker->city,
        'flagColor' => '74E207',
        'flagText' => 'Paid',
        'bookingStatusCode' => 0,
        'price' => 10000,
        'bookingReferer' => 'ch:14',
        'bookingIp' => '142.93.148.152',
        'host_comments' => Str::random(10),
        'unit_id' => 1,
    ];
});*/
