<?php 
/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 18-Feb-19
 * Time: 4:47 PM
 */

namespace App\Repositories\Settings;

use App\BookingSourceForm;
use App\GeneralPreferencesForm;
use App\UserGeneralPreference;


class ClientGeneralPreferencesSettings {

    private $userAccountId;
    private $generalPreferences;
    private $userGeneralPreference;

    /**
     * ClientGeneralPreferencesSettings constructor.
     * @param int $userAccountId
     */
	function __construct(int $userAccountId) {

	    $this->userAccountId = $userAccountId;
	    $this->generalPreferences = GeneralPreferencesForm::get();
	    $this->userGeneralPreference = UserGeneralPreference::where('user_account_id' , $this->userAccountId)->get();
    }

    /**
     * @param $configVar
     * @param BookingSourceForm $bookingSourceForm
     * @return mixed
     */
    public function isActiveStatus($configVar, BookingSourceForm $bookingSourceForm)
    {
        $generalPreference = $this->generalPreferences->where('name', $configVar)->first();
        $booking_source_form_id = $this->getCustomOrDefaultBookingSourceFormId($bookingSourceForm);
        $userPreference  = $this->havingUserPreference($generalPreference->id, $booking_source_form_id);
        return !empty($userPreference) ?  $userPreference['status'] : $generalPreference->status;
    }

    /**
     * @param $form_id
     * @return bool|mixed
     */

    private function havingUserPreference($form_id, $booking_source_form_id)
    {
        $userPreference = $this->userGeneralPreference->where('form_id', $form_id)
            ->where('booking_source_form_id', $booking_source_form_id)->first();
        return !empty($userPreference) ? json_decode($userPreference->form_data, true) : false;
    }

    /**
     * @param BookingSourceForm $booking_source_form
     * @return int
     */
    private function getCustomOrDefaultBookingSourceFormId(BookingSourceForm $booking_source_form)
    {
        return $booking_source_form->use_custom_settings ==  1 ? $booking_source_form->id : 0;
    }
}