<?php

use Illuminate\Database\Seeder;
use App\GeneralPreferencesForm;

class GeneralPreferencesFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Need to run seeder.
        GeneralPreferencesForm::truncate();
        $file = 'db_const.general_preferences_form.';

        $generalPreferencesForms = [

            ['name' => config($file.'emailToGuest'), 'description' => 'Send email to guest to complete  pre-arrival information.', 'priority' => 1, 'status' => 1, 'deleted_at' => NULL],

            ['name' => config($file.'basicInfo'), 'description' => 'Guests will be prompted to enter their email & phone number while completing pre-checkin. This is the best way to get the guests’ actual email & phone number which maybe masked or unavailable with the booking.', 'priority' => 2, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'arrival'), 'description' => 'Guests will be prompted to enter their arrival time & method while completing pre-checkin. This can be helpful in co-ordinating checkin.', 'priority' => 3, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'verification'), 'description' => 'Enable or disable verification tab on Pre-Check-In page.', 'priority' => 4, 'status' => 0, 'deleted_at' => date("Y-m-d H:i:s")],

            ['name' => config($file.'requiredPassportScan'), 'description' => 'Guests will be prompted to upload/scan their passport or ID while completing pre-checkin.', 'priority' => 5, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'requiredCreditCardScan'), 'description' => 'Guests will be prompted to upload/scan their credit card while completing pre-checkin.', 'priority' => 6, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'guest-selfie') , 'description' => 'Guest will be prompted to take selfie picture while completing pre-checkin.', 'priority' => 7, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'upsell'), 'description' => 'Guest can purchase upsells while completing pre-checkin or after. You can manage your add-ons in the <a href="/client/v2/upsells">Upsell</a> menu.', 'priority' => 8, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'digitalSignaturePad'), 'description' => 'Guest will be required to digital sign while completing pre-checkin.', 'priority' => 9, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'termsAndCondition'), 'description' => 'Guest will be required to accept <a href="/client/v2/terms-and-conditions">Terms & Conditions</a> while completing pre-checkin.', 'priority' => 10, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'guideBooks'), 'description' => 'Guests can read checkin, check out, directions, wifi, address etc on their personalized guest portal. You can manage content in the <a href="/client/v2/guide-books">Guidebook</a> menu.', 'priority' => 11, 'status' => 0, 'deleted_at' => NULL],

            ['name' => config($file.'guestChatFeature'), 'description' => 'Guest can chat with you while completing pre-checkin or from the guest portal.', 'priority' => 12, 'status' => 0, 'deleted_at' => NULL],
        ];

        GeneralPreferencesForm::insert($generalPreferencesForms);
    }
}
