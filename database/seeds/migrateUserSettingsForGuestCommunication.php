<?php

use App\UserGeneralPreference;
use App\UserPreferencesNotificationSettings;
use App\GeneralPreferencesForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class migrateUserSettingsForGuestCommunication extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	//get settings for guest for all clients
        $all_clients_setting_for_guest = UserPreferencesNotificationSettings::where('activity_id', 12)->get();
        
        //fetch the default record for guest setting from settings table
        $default_guest_setting_record = GeneralPreferencesForm::find(7);

        if($all_clients_setting_for_guest && $default_guest_setting_record)
        {
        	foreach ($all_clients_setting_for_guest as $index => $guest_setting) {
		        if ($guest_setting->notify_settings != null) {

		            $guest_email_setting = json_decode($guest_setting->notify_settings);

		            //check if client has set it disabled then put entry in the new table for same
		            if(isset($guest_email_setting->email_to_guest) && $guest_email_setting->email_to_guest != 1)
		            {
						/*************Insert 5 records for 4 bookings channels and 1 for others**********
		                 * Here 1, 2, 3, 4 referes to Booking.com, Agoda, Expedia, CTrip
		                 * 0 referes to to all others channels
		                */
		                $booking_sources = [1, 2, 3, 4, 0];

		                foreach ($booking_sources as $booking_source) {
		                	UserGeneralPreference::updateOrCreate(
		                		//write condition to check if already exist
			                	[
			                		'booking_source_form_id' => $booking_source,
			                		'user_account_id' => $guest_setting->user_account_id,
			                		'form_id' => 7
			                	],
			                	[
			                		'name' => $default_guest_setting_record->name,
			                		'description' => $default_guest_setting_record->description,
			                		'user_account_id' => $guest_setting->user_account_id,
			                		'form_id' => 7,
			                		'booking_source_form_id' => $booking_source,
			                		'form_data' => json_encode(['status'=>0])
			                	]
		                	);
		                }

		                //delete the record in old table as it is useless from now
		                $guest_setting->delete();
		            }

		        }
	    	}
	    }
    }
}