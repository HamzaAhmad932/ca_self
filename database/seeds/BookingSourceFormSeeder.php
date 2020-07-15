<?php

use App\BookingSourceForm;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/17/19
 * Time: 3:48 PM
 */

class BookingSourceFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // TYPE: '1=VC, 2=CC, 3=BankDraft, 4=VC CC, 5=VC CC BankDraft, 6=NotSupported'

        BookingSourceForm::truncate();

        $booking_sources = [
            ['logo' => 'booking-logo.png', 'name' => 'Booking.com', 'channel_code' => 19, 'type' => 5, 'pms_form_id' => 1, 'priority' => 1, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => 'agoda-logo.png', 'name' => 'Agoda', 'channel_code' => 17, 'type' => 1, 'pms_form_id' => 1, 'priority' => 3, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => 'expedia-logo.png', 'name' => 'Expedia', 'channel_code' => 14, 'type' => 5, 'pms_form_id' => 1, 'priority' => 2, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => 'ctrip-logo.png', 'name' => 'cTrip', 'channel_code' => 53, 'type' => 1, 'pms_form_id' => 1, 'priority' => 4, 'status' => '1', 'use_custom_settings' => 1],

            //new channels which do not support payments
            ['logo' => NULL, 'name' => 'Direct', 'channel_code' => 0, 'type' => 2, 'pms_form_id' => 1, 'priority' => 10, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => NULL, 'name' => 'Bookit', 'channel_code' => 2, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'NZAA', 'channel_code' => 3, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Laterooms', 'channel_code' => 8, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],

            //use internal channel_code 101 defined in DB_CONST file
            ['logo' => 'airbnb-logo.png', 'name' => 'Airbnb', 'channel_code' => 101, 'type' => 6, 'priority' => 5, 'pms_form_id' => 1, 'status' => '1', 'use_custom_settings' => 1],
            
            ['logo' => NULL, 'name' => 'Flipkey', 'channel_code' => 12, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Guestlink', 'channel_code' => 13, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Wimdu', 'channel_code' => 15, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'iCal Export', 'channel_code' => 16, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Travelocity', 'channel_code' => 18, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Tripadvisor', 'channel_code' => 20, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'iCal import 1', 'channel_code' => 21, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Budgetplaces', 'channel_code' => 22, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Tablethotels', 'channel_code' => 23, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Hostelworld', 'channel_code' => 24, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Visitscotland', 'channel_code' => 25, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Holidaylettings', 'channel_code' => 26, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Bedandbreakfast EU', 'channel_code' => 27, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'iCal import 2', 'channel_code' => 28, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'iCal import 3', 'channel_code' => 29, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => 'homeaway.png', 'name' => 'Homeaway XML', 'channel_code' => 30, 'type' => 2, 'pms_form_id' => 1, 'priority' => 7, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => NULL, 'name' => 'Bedandbreakfast NL', 'channel_code' => 31, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Atraveo', 'channel_code' => 32, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Feratel', 'channel_code' => 33, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Webrooms NZ', 'channel_code' => 34, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Lastminute', 'channel_code' => 35, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Hotelbeds', 'channel_code' => 36, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Housetrip', 'channel_code' => 37, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Nineflats', 'channel_code' => 38, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => 'homeaway.png', 'name' => 'Homeaway iCal', 'channel_code' => 40, 'type' => 2, 'pms_form_id' => 1, 'priority' => 8, 'status' => '1', 'use_custom_settings' => 1],
            ['logo' => NULL, 'name' => 'OTA', 'channel_code' => 42, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Trivago', 'channel_code' => 43, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Hostellinginternational', 'channel_code' => 44, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            // ['logo' => 'airbnb.png', 'name' => 'Airbnb XML', 'channel_code' => 46, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Tomas', 'channel_code' => 50, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Ostrovok', 'channel_code' => 51, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Bookeasy AU', 'channel_code' => 52, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Asiatravel', 'channel_code' => 54, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Tripadvisor Rentals', 'channel_code' => 55, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Traveloka', 'channel_code' => 56, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'HRS', 'channel_code' => 57, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],
            ['logo' => NULL, 'name' => 'Google', 'channel_code' => 58, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0],

            //All Channels code which are not defined will be treated as others.
            ['logo' => NULL, 'name' => 'Others', 'channel_code' => 999, 'type' => 6, 'pms_form_id' => 1, 'priority' => NULL, 'status' => '1', 'use_custom_settings' => 0]
        ];

        $ba_pms_form = \App\PmsForm::where('name', 'Booking Automation')->first();
        if(!empty($ba_pms_form)) {
            foreach ($booking_sources as $key => $booking_source) {
                BookingSourceForm::create([
                    'logo' => $booking_source['logo'],
                    'name' => $booking_source['name'],
                    'channel_code' => $booking_source['channel_code'],
                    'type' => $booking_source['type'],
                    'pms_form_id' => $ba_pms_form->id,
                    'priority' => $booking_source['priority'],
                    'use_custom_settings' => $booking_source['use_custom_settings'],
                    'status' => $booking_source['status']
                ]);
            }
        } else {
            echo 'Ba PMS Form not fount, cannot create booking sources for it. FIX it ASAP';
        }

        $beds24_pms_form = \App\PmsForm::where('name', 'Beds24')->first();
        if(!empty($beds24_pms_form)) {
            foreach ($booking_sources as $key => $booking_source) {
                BookingSourceForm::create([
                    'logo' => $booking_source['logo'],
                    'name' => $booking_source['name'],
                    'channel_code' => $booking_source['channel_code'],
                    'type' => $booking_source['type'],
                    'pms_form_id' => $beds24_pms_form->id,
                    'priority' => $booking_source['priority'],
                    'use_custom_settings' => $booking_source['use_custom_settings'],
                    'status' => $booking_source['status']
                ]);
            }
        } else {
            echo 'Beds24 PMS Form not fount, cannot create booking sources for it. FIX it ASAP';
        }

        $littleHotelier = \App\PmsForm::where('name', 'Little Hotelier')->first();
        if(!empty($littleHotelier)) {
            foreach ($booking_sources as $key => $booking_source) {
                BookingSourceForm::create([
                    'logo' => $booking_source['logo'],
                    'name' => $booking_source['name'],
                    'channel_code' => $booking_source['channel_code'],
                    'type' => $booking_source['type'],
                    'pms_form_id' => $littleHotelier->id,
                    'priority' => $booking_source['priority'],
                    'use_custom_settings' => $booking_source['use_custom_settings'],
                    'status' => $booking_source['status']
                ]);
            }
        } else {
            echo 'Little Hotelier PMS Form not found, cannot create booking sources for it. FIX it ASAP';
        }

    }
}