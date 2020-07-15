<?php

namespace App\Repositories\Bookings;

use App\BookingInfo;

use App\BookingSourceForm;
use App\CaCapability;
use App\PropertyInfo;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\Upsells\UpsellRepository;
use App\Services\CapabilityService;
use App\CreditCardInfo;
use App\Upsell;
use App\UpsellOrder;
use App\UpsellOrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function foo\func;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BookingRepository implements BookingRepositoryInterface
{

    public function __construct()
    {

    }

    public function get_bookings_list()
    {
        $user_account = Auth::user()->user_account;

        $bookings = BookingInfo::with([
                'credit_card_authorization'=>function($query){
                    $query->whereIn('type', [19, 20, 21, 22]);
                },
                'transaction_init'=>function($transaction_init){
                    $transaction_init->where('type','C');
                },
                'guest_images',
                //'booking_source',
                'cc_Infos',
                //'room_info'
            ])->where('user_account_id', $user_account->id)
            ->latest()->paginate(10);

        $bs_forms = BookingSourceForm::all();
        foreach ($bookings as $booking) {
            $booking->booking_source = $bs_forms->where('channel_code', $booking->channel_code)
                ->where('pms_form_id', $booking->pms_id)->first();
            $booking->bookingSourceForm = $booking->booking_source;

        }
        return $bookings;
    }

    public function get_booking_detail($booking_info_id){

        $user_account_id = Auth::user()->user_account_id;
        $general_settings = new ClientGeneralPreferencesSettings($user_account_id);
        $booking = BookingInfo::with([
            'transaction_init.transactions_detail.ccinfo',
            'transaction_init.refund_detail',
            'credit_card_authorization.authorization_details',
            'credit_card_authorization.ccinfo',
            'guest_data',
            'cc_Infos'=> function($query){
                $query->where('is_vc', 0);
            }
        ])->where('id', $booking_info_id)
            ->where('user_account_id', $user_account_id)
            ->first();
        $booking->capabilities = CapabilityService::allCapabilities($booking);

        $guest_experience = !empty($booking->capabilities[CaCapability::GUEST_EXPERIENCE])
            && $general_settings->isActiveStatus(config('db_const.general_preferences_form.emailToGuest'),
                $booking->bookingSourceForm);
        $booking->guest_experience = $guest_experience;

        return $booking;
    }

    /**
     * @param $filter
     * @return array|mixed
     * @throws \Exception
     */
    public function get_bookings_list_filtered($filter)
    {
        $this->setBookingListPageFilters($filter);
        try {
            return $bookings = get_collection_by_applying_filters($filter, BookingInfo::class);
        } catch (\Exception $e) {
            Log::error('Booking List filter query exception' . $e->getMessage(),
                ['file'=> BookingRepository::class, 'method'=> 'get_bookings_list_filtered', 'stack'=>$e->getTraceAsString()]);
            return [];
        }
    }

    /**
     * @param array $filter
     * @throws \Exception
     */
    private function setBookingListPageFilters(array &$filter){
        if (empty($filter)) {throw new \Exception('Request Filter not Valid');}
        $user_account_id = Auth::user()->user_account_id;
        array_push($filter['constraints'], ['user_account_id', $user_account_id]);
        array_push($filter['constraints'], ['created_at', '<', now()->subSeconds(7)->toDateTimeString()]);
        if (!empty($filter['property_id'])) {
            array_push($filter['constraints'], ['property_id',$filter['property_id']]);
            $property = PropertyInfo::where('user_account_id', $user_account_id)->where('pms_property_id',
                $filter['property_id'])->select('time_zone','pms_property_id')->first();
            if (empty($property)){ throw new \Exception('Property not found.');}
        }

    }

    public static function isCCInfoValidAndCustomerObjectCreated(CreditCardInfo $ccInfo = null) {
         return (!is_null($ccInfo) && ($ccInfo->customer_object != null) && ($ccInfo->customer_object->token != null )
             && !in_array($ccInfo->status, [3, 4, 5]));
    }

    /**
     * @param int|$new_pms_property_id
     * @param int|$new_room_id
     * @param int|$booking_info_id
     * @return bool|string
     */
    public static function changeBookingProperty($new_pms_property_id, $new_room_id, $booking_info_id)
    {

        //search if new property is available in CA database
        $new_property = PropertyInfo::where('pms_property_id', $new_pms_property_id)->first(['id', 'property_key', 'pms_property_id']);

        //if available then update current processing booking with new property details
        if($new_property && isset($new_property->id)) {

            $updated = BookingInfo::where('id', $booking_info_id)->update([
                'property_id' => $new_property->pms_property_id,
                'property_info_id' => $new_property->id,
                'room_id' => $new_room_id
            ]);

            if($updated) return true;

        }

        return false;
    }
}
