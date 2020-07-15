<?php

use Illuminate\Database\Seeder;

class AddUserBookingSourceIdsToUserSettingBridgeTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userBookingSources  = \App\UserBookingSource::all();
        foreach ($userBookingSources as $userBookingSource) {
            \App\UserSettingsBridge::where([['user_account_id', $userBookingSource->user_account_id],
                    ['property_info_id', $userBookingSource->property_info_id],
                    ['booking_source_form_id', $userBookingSource->booking_source_form_id]])
                ->update(['user_booking_source_id' => $userBookingSource->id]);
            echo "User Booking Source $userBookingSource->id Seeded! \n";
        }
    }
}
