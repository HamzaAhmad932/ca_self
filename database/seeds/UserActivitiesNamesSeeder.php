<?php

use Illuminate\Database\Seeder;
use App\Activity;


class UserActivitiesNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        //empty table first - This is useful when we do modifications and refresh seeder
        Activity::truncate();

        /*
         ******Preference Type*******
         *1 - Account Notifications
         *2 - Guest Notifications
         *3 - Payment Activity Notifications
        */
        $file = 'db_const.user_notify_Settings.';
        $userActivities = [
            [ 'name' => config($file.'accountStatus'), 'desc' => 'Send notification when a change is made to account settings', 'preference_type' => 1, 'status' => 1 ],
            [ 'name' => config($file.'newTeamMember'), 'desc' => 'Send notification when a new team member joins', 'preference_type' => 1, 'status' => 1 ],
            [ 'name' => config($file.'paymentSuccessful'), 'desc' => 'Send notification for every successful payment charge', 'preference_type' => 3, 'status' => 1 ],
            [ 'name' => config($file.'paymentDecline'), 'desc' => 'Send notification when a payment charge is declined', 'preference_type' => 3, 'status' => 1 ],
            [ 'name' => config($file.'newBooking'), 'desc' => 'Send notification when new booking received', 'preference_type' => 1, 'status' => 1 ],
            [ 'name' => config($file.'cancelBooking'), 'desc' => 'Send notification when a booking is cancelled', 'preference_type' => 1, 'status' => 1 ],
            [ 'name' => config($file.'refundSuccessful'), 'desc' => 'Send notification when a refund is successfully processed', 'preference_type' => 3, 'status' => 1 ],
            [ 'name' => config($file.'refundDecline'), 'desc' => 'Send notification when a refund declines or fails', 'preference_type' => 3, 'status' => 1 ],

            [ 'name' => config($file.'bookingCardDetailsMissing'), 'desc' => 'Send notification when payment card details are missing', 'preference_type' => 3, 'status' => 1 ],
            [ 'name' => config($file.'bookingSourceActivatedDeactivated'), 'desc' => 'Booking source status activity settings', 'preference_type' => 1, 'status' => 0 ],
            [ 'name' => config($file.'propertyConnectedDisconnected'), 'desc' => 'Property status activity settings', 'preference_type' => 1, 'status' => 0 ],
            [ 'name' => config($file.'guestCorrespondence'), 'desc' => 'Send confirmation email to guest when a new booking is received', 'preference_type' => 2, 'status' => 0 ],
            [ 'name' => config($file.'OnGuestDocumentsUploading'), 'desc' => 'Send notification when a guest uploads document(s)', 'preference_type' => 2, 'status' => 1 ],
            [ 'name' => config($file.'OnGuestCommunicationMessage'), 'desc' => 'Send notification when a guest message is received via chat', 'preference_type' => 2, 'status' => 1 ],
            [ 'name' => config($file.'upsell'), 'desc' => 'Send notification when a guest purchased an Add-on Service', 'preference_type' => 2, 'status' => 1 ]

            // 'Confirmation Email' => 'Send confirmation email to guest when a new booking is received'
        ];



        foreach ($userActivities as $index => $activity) {
            Activity::create([
                'name' => $activity['name'],
                'desc' => $activity['desc'],
                'preference_type' => $activity['preference_type'],
                'sms' => 0,
                'email' => 1,
                'status' => $activity['status']
            ]);
        }
    }
}
