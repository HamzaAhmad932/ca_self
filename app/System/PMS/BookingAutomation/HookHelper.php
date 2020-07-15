<?php


namespace App\System\PMS\BookingAutomation;


use App\ApiRequestDetail;
use App\BookingInfo;
use App\CaCapability;
use App\FailedBooking;
use App\GroupBookingOnHold;
use App\PropertyInfo;
use App\Repositories\BookingSources\BookingSources;
use App\UserAccount;
use App\UserPms;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait HookHelper {

    public static $STATUS_NEW = 'new';
    public static $STATUS_MODIFY = 'modify';
    public static $STATUS_CANCEL = 'cancel';

    private $ip = '';
    private $bookingId = null;
    private $bookingStatus = null;
    private $channelCode = null;
    private $propertyId = null;
    private $userAccountId = null;
    private $token = null;
    private $cvv = null;
    private $fullUrl = '';
    private $groupId = null;

    /**
     * @var ApiRequestDetail
     */
    private $apiRequestDetail = null;

    public function parseRequest(Request $r) {
        try {
            $this->ip = $r->ip();
            $this->fullUrl = $r->fullUrl();
            $this->bookingId = $r->input(NotificationUrlBookingAutomation::BOOKING_ID, null);
            $this->bookingStatus = $r->input(NotificationUrlBookingAutomation::STATUS, null);
            $this->channelCode = filteredChannelCode($r->input(NotificationUrlBookingAutomation::CHANNEL_CODE, null));
            $this->propertyId = $r->input(NotificationUrlBookingAutomation::PROPERTY_ID, null);
            $this->userAccountId = $r->input(NotificationUrlBookingAutomation::META_USER_ACCOUNT_ID, null);
            $this->token = $r->input(NotificationUrlBookingAutomation::TOKEN, null);
            $this->cvv = $r->input(NotificationUrlBookingAutomation::CVV, null);
            $this->groupId = $r->input(NotificationUrlBookingAutomation::GROUP_ID, null);

            /**
             * Note BA sends booking id in groupId if its not group booking.
             * So we are making groupId null to avoid misbehaviour
             */
            if($this->groupId == $this->bookingId)
                $this->groupId = null;

//            Log::notice('BA Booking Notification', [
//                'ip' => $this->ip,
//                'fullUrl' => $this->fullUrl,
//                'bookingId' => $this->bookingId,
//                'bookingStatus' => $this->bookingStatus,
//                'channelCode' => $this->channelCode,
//                'propertyId' => $this->propertyId,
//                'UserAccountId' => $this->userAccountId,
//                'token' => $this->token,
//                'cvv' => $this->cvv
//            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'Function'=>__FUNCTION__,
                    'data'=>json_decode(json_encode($r), true),
                    'stack'=>$e->getTraceAsString()
                ]);
        }
    }

    public function insertApiRequestLog() {
        try {
            $this->apiRequestDetail = ApiRequestDetail::create([
                'user_account_id' => $this->userAccountId,
                'channel_code' => $this->channelCode,
                'pms_property_id' => $this->propertyId,
                'pms_booking_id' => $this->bookingId,
                'full_request_url' => $this->fullUrl ."\nToken: {$this->token}\nCVV: {$this->cvv}\nGroupId: {$this->groupId}",
                'status' => $this->bookingStatus,
                'client_ip' => $this->ip]);
        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'function'=>__FUNCTION__,
                    'stack'=>$e->getTraceAsString()
                ]);
        }
    }

    /**
     * Is used to check if incoming request is from allowed/trusted IPs
     * @return bool
     */
    public function isIncomingIpAllowed() {

        if(config('app.env') == 'production' && config('app.debug') == false)
            return $this->ip === '176.9.52.204' || $this->ip === '195.201.74.20' || $this->ip === '77.104.162.129';
        else
            return true;
    }

    /**
     * Is used to check that booking status parameter is valid or supported
     * @return bool
     */
    public function isPmsBookingStatusValid() {

        return in_array($this->bookingStatus, [self::$STATUS_NEW, self::$STATUS_MODIFY, self::$STATUS_CANCEL]);
    }

    public function doesRequiredParametersExists() {

        return $this->bookingId !== null
            && $this->bookingStatus !== null
            && $this->channelCode !== null
            && $this->propertyId !== null
            && $this->userAccountId !== null;
    }

    /**
     * @param string $message
     * @param int $code
     * @return ResponseFactory|Response
     */
    public function replyToHook(string $message, int $code) {

        if($this->apiRequestDetail != null) {
            $this->apiRequestDetail->update(['full_request_url' => $this->apiRequestDetail->full_request_url . "\n" . $message]);
        }

        FailedBooking::create([
        'user_account_id' => !empty($this->userAccountId) ? $this->userAccountId : 0,
        'channel_code' => !empty($this->channelCode) ? $this->channelCode : 0,
        'pms_property_id' => !empty($this->propertyId) ? $this->propertyId : 0,
        'pms_booking_id' => !empty($this->bookingId) ? $this->bookingId : 0,
        'status' => $this->bookingStatus,
        'exception' => $message]);
        return response($message, $code);
    }

    /**
     * @param $user_account_id
     * @param $pms_booking_id
     * @param $pms_property_id
     * @return UserAccount|null
     */
    public function getUserAccountObject($user_account_id, $pms_booking_id, $pms_property_id) {
        try {

            return resolve(UserAccount::class)->where('id', $user_account_id)
                ->with(['properties_info' => function ($query) use ($pms_property_id) {

                    $query->where('pms_property_id', $pms_property_id);

                }])->with(['bookings_info' => function ($query) use ($pms_booking_id) {

                    $query->where('pms_booking_id', $pms_booking_id)->select('pms_booking_status', 'user_account_id', 'property_id');

                }])->with('pms')->first();

        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'function'=>__FUNCTION__,
                    'stack'=>$e->getTraceAsString()
                ]);
        }

        return null;
    }

    /**
     * @param UserAccount $userAccount
     * @return bool
     */
    public function isUserPropertyActive(UserAccount $userAccount) {
        /**
         * @var $property_info PropertyInfo
         */
        $property_info = $userAccount->properties_info->first();
        if($property_info != null)
            return $property_info->isActive();
        return false;
    }

    /**
     * @param UserAccount $userAccount
     * @return bool
     */
    public function isUserPmsVerified(UserAccount $userAccount) {
        /**
         * @var $userPms UserPms
         */
        $userPms = $userAccount->pms->first();
        if($userPms != null)
            return $userPms->isVerified();
        return false;
    }

    public function isBookingSourceActiveToFetchBooking(UserAccount $userAccount) {

        try {
            /**
             * @var $userPms UserPms
             */
            $userPms = $userAccount->pms->first();
            $booking_source_form_id = BookingSources::getBookingSourceFormIdByChannelCode(
                $userPms->pms_form_id,
                $this->channelCode
            );

            return BookingSources::isActiveFetchBookingSetting($userAccount, $booking_source_form_id);

        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'function'=>__FUNCTION__,
                    'stack'=>$e->getTraceAsString()
                ]);
        }

        return false;

    }

    public function checkBookingSourceCapabilityByName(UserAccount $userAccount, $capability_name) {

        try {

            /**
             * @var $userPms UserPms
             */
            $userPms = $userAccount->pms->first();

            $bookingSourceRepo = new BookingSources();
            $capabilities = $bookingSourceRepo->getAllBookingSourcesCapabilities();
            $bookingSourceFormId = $bookingSourceRepo::getBookingSourceFormIdByChannelCode($userPms->pms_form_id, $this->channelCode);

            return $capability_name == CaCapability::FETCH_BOOKING //Fetch Booking Activated for All Channels
                ? true
                : !empty($capabilities[$bookingSourceFormId][$capability_name]);

        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'function'=>__FUNCTION__,
                    'stack'=>$e->getTraceAsString()
                ]);
        }
        return false;
    }

    /**
     * Simply checks that if booking exists in database or not.
     * @param $pms_booking_id
     * @param $user_account_id
     * @return bool
     */
    public function isBookingInDb($pms_booking_id, $user_account_id) {
        return BookingInfo::where('pms_booking_id', $pms_booking_id)
                ->where('user_account_id', $user_account_id)
                ->count() > 0;
    }

    /**
     * This function checks that if notification have group id then does master booking is in db or not,
     * If Master-Booking is not found then this booking's request should be put on hold by inserting this
     * request in separate table.
     * @param int|null $groupId
     * @param int $user_account_id
     * @return bool
     */
    public function shouldProcessIfGroupBooking($groupId, int $user_account_id) {
        if(!empty($groupId))
            return $this->isBookingInDb($groupId, $user_account_id);

        return true;

    }

    public function insertBookingOnHold($user_account_id, $pms_booking_id, $group_id, $booking_status, $channel_code,
                                        $pms_property_id, $token = null, $cvv = null, $name_of_caller = null) {

        $group = new GroupBookingOnHold();

        $group->user_account_id = $user_account_id;
        $group->pms_booking_id = $pms_booking_id;
        $group->master_id = $group_id;
        $group->booking_status = $booking_status;
        $group->channel_code = $channel_code;
        $group->pms_property_id = $pms_property_id;
        $group->token = $token;
        $group->cvv = $cvv;
        $group->caller = $name_of_caller;

        $group->save();
    }

}