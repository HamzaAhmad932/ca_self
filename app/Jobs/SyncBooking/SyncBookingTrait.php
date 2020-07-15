<?php


namespace App\Jobs\SyncBooking;


use App\BookingInfo;
use App\CaCapability;
use App\PMS\BookingJobHelper;
use App\PropertyInfo;
use App\System\PMS\BookingAutomation\HookHelper;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait SyncBookingTrait
{
    use BookingJobHelper, HookHelper;

    /**
     * @var bool
     */
    private $api_limit_exceed = false;

    /**
     * @var bool
     */
    private $api_un_authorize = false;

    /**
     * @param PropertyInfo $property_info
     * * Update Last Sync for PropertyInfo
     */
    private function updateSyncFrom(PropertyInfo $property_info)
    {

        if (!empty($property_info->sync_booking_from)) { // If isset Sync_from for property.
            if (empty($this->api_limit_exceed)
                && empty($this->api_un_authorize)) {
                $property_info->update(['sync_booking_from' => null]);
            }
        }
    }

    /**
     * @param $user_account
     * Update Last Sync for UserAccount
     */
    private function updateLastSync($user_account)
    {

        $last_sync = $user_account->last_booking_sync ?? now()->toDateTimeString();

        // If Api Limit Exceed Schedule job for this user after some minutes.
        $now = $this->api_limit_exceed
            ? Carbon::parse($last_sync)->addMinute(15)->toDateTimeString()
            : now()->toDateTimeString();

        $user_account->update(['last_booking_sync' => $now]);
    }


    /**
     * @param PropertyInfo $property_info
     * @param PMS $pms
     * @return array
     */
    private function fetch_Booking_Details_json_xml(PropertyInfo $property_info, PMS $pms)
    {

        try {

            $sync_from = $property_info->sync_booking_from ?? Carbon::now()->subHour(24)->toDateTimeString();

            $pms_options = new PmsOptions();
            $pms_options->includeCard = true;
            $pms_options->includeInvoice = true;
            $pms_options->includeInfoItems = true;
            $pms_options->dateFrom = $sync_from;
            $pms_options->modifiedDate = $sync_from;
            $pms_options->modifiedSince = $sync_from;
            $pms_options->propertyKey = $property_info->property_key;
            $pms_options->propertyID = $property_info->pms_property_id;

            return $pms->fetch_Booking_Details_json_xml($pms_options);

        } catch (PmsExceptions $exception) {

            $this->handlePMSRequestFailedBySync($exception,
                ($property_info->user_account_id . ' - Property_id : ' . $property_info->id)
            );

        } catch (Exception $exception) {

            log_exception_by_exception_object($exception,
                json_encode(['property_info_id' => $property_info->id])
            );
        }

        return [];
    }

    /**
     * @param UserAccount $user_account
     * @return array
     * return array of pms_property_ids
     */
    private function properties(UserAccount $user_account)
    {
        try {

            /**
             * if user selected sync bookings_from for properties after integration to Sync previous bookings from PMS
             * set Sync_from Bookings whose time greater than last minimum Sync time of property's bookings.
             * else Sync_from last 24 hours bookings
             */

            $sync_from = $user_account->activeProperties->min('sync_booking_from')
                ?? Carbon::now()->subHour(24)->toDateTimeString();

            $properties = array();

            $pms = new PMS($user_account);
            $pms_options = new PmsOptions();
            $pms_options->modifiedDate = $sync_from;
            $pms_options->modifiedSince = $sync_from;
            $pms_options->dateFrom = $sync_from;
            $pms_options->requestType = PmsOptions::REQUEST_TYPE_XML;

            // Sync PMS Bookings With XML Request
            $pms_bookings = $pms->fetch_Booking_Details($pms_options);


            foreach ($pms_bookings as $pms_booking) {
                if ($this->isBookingValidToSync($user_account, $pms_booking, $sync_from)) {
                    array_push($properties, $pms_booking->propertyId);
                }
            }

            $properties = array_unique($properties);

            /*Log::info('Properties having new bookings',
                [
                    'UserAccount' => $user_account->id,
                    'Properties' => $properties,
                ]
            );*/

            // Properties whose new bookings available on PMS to Sync.
            return $properties;

        } catch (PmsExceptions $exception) {

            $this->handlePMSRequestFailedBySync($exception, $user_account->id);

        } catch (Exception $exception) {

            log_exception_by_exception_object($exception,
                json_encode(['user_account' => $user_account->id]),
                'error'
            );
        }

        return [];
    }


    /***
     * @param PmsExceptions $exception
     * @param int $user_account_id
     */
    private function handlePMSRequestFailedBySync(PmsExceptions $exception, $user_account_id)
    {

        switch ($exception->getPMSCode()) {
            case PMS::ERROR_LIMIT_EXCEED:
                $this->api_limit_exceed = true;
                break;
            case PMS::ERROR_UN_AUTHORIZED:
                $this->api_un_authorize = true;
                break;
        }

        log_exception_by_exception_object($exception,
            json_encode(['user_account' => $user_account_id]), 'notice');
    }


    /**
     * @param UserAccount $user_account
     * @param $booking_channel_code
     * @return mixed
     * @throws Exception
     */
    private function bookingSourceFormId(UserAccount $user_account, $booking_channel_code)
    {
        return self::$bsRepo::getBookingSourceFormIdByChannelCode(
            $user_account->pms->pms_form_id,
            $booking_channel_code
        );
    }

    /**
     * @param UserAccount $user_account
     * @param Booking $pms_booking
     * @param $sync_from
     * @return bool
     * @throws Exception
     * Valid to Sync Bookings whose BookingTime >= $sync_from dateTime
     */
    private function isBookingValidToSync(UserAccount $user_account, Booking &$pms_booking, string $sync_from)
    {
        // Property in-active or un-available
        if (empty($user_account->activeProperties->where('pms_property_id', $pms_booking->propertyId))) {
            return false;
        }

        $pms_booking->channelCode = filteredChannelCode($pms_booking->channelCode);
        $booking_source_form_id = $this->bookingSourceFormId($user_account, $pms_booking->channelCode);


        // CA -- Booking Source Capability Check FETCH BOOKING
        if (empty(self::$bsCapabilities[$booking_source_form_id][CaCapability::FETCH_BOOKING])) {
            return false;
        }


        // Booking Status not Cancelled && Booking not already in DB && BookingTime less then now - 20 seconds to wait for PMS ping.
        if (($pms_booking->bookingStatus == 0)
            || ($pms_booking->bookingTime < $sync_from)
            || ($pms_booking->bookingTime > now()->subSeconds(20)->toDateTimeString())
            || (BookingInfo::where(
                'pms_id', $user_account->pms->pms_form_id)->where(
                    'pms_booking_id', $pms_booking->id)->count() > 0)) {
            return false;
        }


        // Fetch Booking Preference active
        if (! self::$bsRepo::isActiveFetchBookingSetting($user_account, $booking_source_form_id)) {

            return false;
        }


        return true;
    }


    /**
     * @return mixed
     */
    private function userAccounts()
    {
        $user_accounts = UserAccount::where('status', config('db_const.user.status.active.value'))->with('pms');

        if ($this->custom_dispatch) {

            // Custom Dispatched Job for specific user
            return $user_accounts->where('id', $this->user_account_id)->get();

        } else {

            $sync_offsets = now()->subHours(config('db_const.sync_offsets.booking-sync-after-integration'))->toDateTimeString();
            $last_sync = now()->subHours(config('db_const.sync_offsets.booking-sync'))->toDateTimeString();

            // Default Dispatched Job for All users
            return $user_accounts->whereNotNull('integration_completed_on')->where('integration_completed_on', '<', $sync_offsets)
                ->where(function ($query) use ($last_sync) {
                    $query->where('last_booking_sync', '<', $last_sync)
                        ->orWhere('last_booking_sync', '=', null);
                })->orderBy('last_booking_sync', 'asc')->take(10)->get();
        }
    }

}