<?php


namespace App\Repositories\BookingDetail;


use App\BookingInfo;
use App\GuestData;
use App\GuestImage;
use App\GuestImageDetail;
use App\PropertyInfo;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\SentEmail;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Illuminate\Support\Facades\Log;

class BookingDetailRepository
{

    public function getBookingDetailHeader($booking_info_id){

        try {
            $user_account = auth()->user()->user_account;

            $booking = BookingInfo::with('guest_images')->where([['id', '=', $booking_info_id], ['user_account_id', '=', $user_account->id]])->first();
            $booking->previous = BookingInfo::where([['id','<',  $booking_info_id], ['user_account_id', '=', $user_account->id]])->max('id');
            $booking->next = BookingInfo::where([['id','>',  $booking_info_id], ['user_account_id', '=', $user_account->id]])->min('id');
            $guestChat = new ClientGeneralPreferencesSettings($booking->user_account_id);
            $booking->chat_active = $guestChat->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'), $booking->bookingSourceForm);

            return $booking;

        }catch (\Exception $e){

            Log::notice($e->getMessage(), ['']);
            return null;
        }
    }

    public function getBookingDetails($booking_info_id){

        $booking_info = BookingInfo::with([
            'guest_data',
            //'room_info'
        ])->where('id', '=', $booking_info_id)->first();
        $booking_info->room_info;
        $booking_info->property_info;  //lazy load property_info relationship
        $booking_info->bookingSourceForm;
        return $booking_info;
    }

    public function getGuestExperienceTabData($booking_info_id){

        return BookingInfo::with(['guest_images', 'guest_data'])->where('id', $booking_info_id)->first();
    }

    /**
     * @param array $data
     */
    public function saveGuestExperienceData($data = []){

        try {

            $guest_data = GuestData::where('booking_id', $data['booking_id'])->first();

            if(!empty($guest_data)){

                $guest_data->arrivaltime = $data['arrival_time'];
                $guest_data->arriving_by = $data['arriving_by'];
                if($data['arriving_by'] == 'Plane'){
                    $guest_data->plane_number = $data['plane_number'];
                }
                if($data['arriving_by'] == 'Other'){
                    $guest_data->other_detail = $data['other_detail'];
                }

                $guest_data->save();

            }else{

                GuestData::create([
                    'booking_id'=> $data['booking_id'],
                    'arrivaltime'=> '',
                    'arriving_by'=> '',
                    'plane_number'=> $data['arriving_by'] == 'Plane' ? $data['arriving_by'] : null,
                    'other_detail'=> $data['arriving_by'] == 'Other' ? $data['arriving_by'] : null
                ]);
            }

            return true;
        }catch (\Exception $e){

            log_exception_by_exception_object($e, null);
            return false;
        }
    }

    public function getPaymentsInformation($booking_info_id){

        try{

            return BookingInfo::with([
                'transaction_init',
                'credit_card_authorization',
                'cc_Infos'=> function($query){
                    $query->where('is_vc', 0);
                }
            ])->where('id', $booking_info_id)->first();

        }catch(\Exception $e){

            log_exception_by_exception_object($e, null);
            return null;
        }
    }

    public function getActivityLog($booking_info_id){

        try{

            return BookingInfo::with([
                'transaction_init',
                'transaction_init.transactions_detail',
                'credit_card_authorization',
                'credit_card_authorization.authorization_details',
            ])->where('id', $booking_info_id)->first();

        }catch(\Exception $e){

            log_exception_by_exception_object($e, null);
            return null;
        }
    }

    public function getSentEmailForBooking($booking_info_id){

        try{
            /*
             * sent_emails table have record for all emails
             * for property | account | team-members | payments | booking
             * We only need booking related emails
             */
            return SentEmail::where('booking_info_id', $booking_info_id)->orderBy('id', 'desc')->paginate(5);

        }catch(\Exception $e){

            log_exception_by_exception_object($e, null);
            return null;
        }
    }

    public function getGuestDocuments($booking_info_id){

        try {
            $booking_info = BookingInfo::findOrFail($booking_info_id);

            $guest_images = collect($booking_info->guest_images);

            return  $guest_images->merge(collect($booking_info->guest_deleted_images));

        }catch (\Exception $e){
            log_exception_by_exception_object($e, ['BookingInfoID' => $booking_info_id]);
            return null;
        }
    }

    public static function updateBasicInfoAtBA(UserAccount $userAccount, PropertyInfo $prop, BookingInfo $bookingInfo, $bookingData){

        //Update Email at BookingAutomation
        $pms = new PMS($userAccount);

        $pmsOptions = new PmsOptions();
        $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
        $pmsOptions->propertyID = $prop->pms_property_id;
        $pmsOptions->bookingID = $bookingInfo->pms_booking_id;
        $pmsOptions->propertyKey = $prop->property_key;

        $booking = new Booking();
        $booking->notes = '';
        /**
         * Fetch Current Booking Details From PMS To verify Booking and to get previous notes to concat new Notes String
         */
        $bookingDetailsOnPMS = $pms->fetch_Booking_Details($pmsOptions);
        if (count($bookingDetailsOnPMS ) > 0) {
            if(!empty($bookingDetailsOnPMS[0]->hostComments)){
                $booking->notes = $bookingDetailsOnPMS[0]->hostComments."\n";
            }
            if(!empty($bookingDetailsOnPMS[0]->guestArrivalTime)){
                $booking->guestArrivalTime = $bookingDetailsOnPMS[0]->guestArrivalTime;
            }
            if(!empty($bookingDetailsOnPMS[0]->guestEmail)){
                $booking->guestEmail =  $bookingDetailsOnPMS[0]->guestEmail;
            }
            if(!empty($bookingDetailsOnPMS[0]->guestMobile)){
                $booking->guestMobile = $bookingDetailsOnPMS[0]->guestMobile;
            }
        }

        if(!empty($bookingData->guest_email)){
            if(!empty($booking->guestEmail)){
                if($booking->guestEmail != $bookingData->guest_email ){
                    $booking->notes .= "\n".'Guest Email Updated to: '.$bookingData->guest_email."\n";
                }
            }else{
                $booking->notes .= "\n".'Guest Email : '.$bookingData->guest_email."\n";
            }
            $booking->guestEmail = $bookingData->guest_email;

        }
        if(!empty($bookingData->phone)){

            if(!empty($booking->guestMobile)){
                if($booking->guestMobile != $bookingData->phone ){
                    $booking->notes .="\n".'Guest Phone Updated to: '.$bookingData->phone."\n";
                }
            }else{
                $booking->notes .= "\n".'Guest Phone : '.$bookingData->phone."\n";
            }
            $booking->guestMobile = $bookingData->phone;

        }
        if(!empty($bookingData->arrival_time)){
            $pre_notes ='Guest will arrive at ';
            $update_notes = 'Guest Arrival Time Updated to '.$bookingData->arrival_time."";
            if(strpos($booking->notes,$pre_notes) != false){
                if(strpos($booking->notes,$update_notes) == false){
                    $booking->notes .= "\n".$update_notes;
                }
            }else{
                $booking->notes .= "\n".$pre_notes.$bookingData->arrival_time."";
            }
            $booking->guestArrivalTime = $bookingData->arrival_time;
        }

        try{
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

            $updated = $pms->update_booking($pmsOptions, $booking);

        }catch (Exception $e){
            Log::error($e->getMessage(), ['File'=> 'GuestController@emailUpdateAtBA']);
        }
    }
}
