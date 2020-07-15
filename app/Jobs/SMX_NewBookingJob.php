<?php

namespace App\Jobs;

use App\Events\Emails\EmailEvent;
use App\PropertyInfo;
use App\RoomInfo;
use App\System\PMS\BookingAutomation\HookHelper;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\SiteMinder\SMX_Guest;
use App\System\PMS\SiteMinder\SMX_Reservation;
use App\System\PMS\SiteMinder\SMX_Room;
use Exception;
use App\UserAccount;
use App\BookingInfo;
use App\FailedBooking;
use App\System\PMS\PMS;
use App\PMS\BookingJobHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PMS\exceptions\PmsExceptions;
use Illuminate\Support\Carbon;

class SMX_NewBookingJob implements ShouldQueue {

    /**
     * @var array
     */
    public $reservations;

    /**
     * @var UserAccount
     */
    public $user_account;
    /**
     * @var PropertyInfo
     */
    public $propertyInfo;
    /**
     * @var string
     */
    private $content;
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $booking_channel_code = 0;

    /**
     * @var array
     */
    private $log_content_json = [];

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BookingJobHelper, HookHelper;

    /**
     * SMX_NewBookingJob constructor.
     * @param UserAccount $user_account
     * @param PropertyInfo $propertyInfo
     * @param array $reservations
     * @param string $content
     */
    public function __construct(UserAccount $user_account, PropertyInfo $propertyInfo, array $reservations, string $content) {

        $this->user_account = $user_account;
        $this->reservations = $reservations;
        $this->propertyInfo = $propertyInfo;
        $this->content = $content;
        $this->log_content_json = [
            'user_account' => $user_account->id,
            'property_info' => $propertyInfo->id,
            'reservation' => $reservations[0]->UniqueID];
    }
    
    public function handle() {
        
        try {

            /**
             * Note: If site-minder sends multiple bookings than perform below actions in side loop rather than
             *       using only zero index.
             *
             * @var $reservation SMX_Reservation
             * @var $guest SMX_Guest
             */
            $reservation = $this->reservations[0];
            $guest = $reservation->guests[0];

            if ($this->doesBookingAlreadyExist($this->user_account, $reservation->UniqueID, 2))
                return;

            $this->sync_rooms($this->reservations, $this->user_account, $this->propertyInfo);

            $this->init_booking_job_helper();

            $this->handleNewBookingNotifyReceived($this->content);
            
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'UserAccount' => $this->user_account->id . " - " . $this->user_account->name,
                'Stack' => $e->getTraceAsString()
            ]);
        }
        
    }

    /**
     * @param UserAccount $userAccount
     * @param string $pmsBookingId
     * @param int $pmsId
     * @return bool
     */
    private function doesBookingAlreadyExist(UserAccount $userAccount, string $pmsBookingId, int $pmsId)
    {
        return $userAccount->bookings_info
                ->where('pms_booking_id', $pmsBookingId)
                ->where('pms_id', $pmsId)
                ->count() > 0;
    }

    /**
     * Handle New Booking Notify Web-hook.
     * @param string $content
     */
    private function handleNewBookingNotifyReceived(string $content) {

        try {

            $pms_options = new PmsOptions();
            $pms_options->dump = $content;

            /**
             * @var $pms PMS
             * @var $pms_booking Booking
             *
             */
            $pms = new PMS($this->user_account);
            $pms_bookings = $pms->fetch_Booking_Details_json_xml($pms_options);

            if (!$this->isBookingFound($pms_bookings))
                return;

            $pms_booking = $pms_bookings[0];

            if($this->isOldBooking($pms_booking, false, $this->user_account))
                return;

            /**
             * Checking if Group Booking (child/sub booking) arrived before Master booking then we are storing
             * it in Booking Hold table, so it can be fetched later when master booking arrived.
             * Below code is from HookHelper Trait
             */
//            if($pms_booking->isGroupBooking())
//                if(!$this->shouldProcessIfGroupBooking($pms_booking->masterId, $this->user_account->id)) {
//                    $this->insertBookingOnHold($this->user_account->id, $pms_booking->id, $pms_booking->masterId, $this->pms_booking_status,
//                        $this->booking_channel_code, $this->pms_property_id, $this->get_card_token, $this->card_cvv, BANewBookingJobNew::class);
//                    Log::notice('Inserting ' . $pms_booking->id . ' in Booking Hold');
//                    return;
//                }


            $this->booking_channel_code = $pms_booking->channelCode = filteredChannelCode($pms_booking->channelCode);
            $booking_source_form_id  = $this->bookingSourceFormId();

            if (! self::$bsRepo::isActiveFetchBookingSetting($this->user_account, $booking_source_form_id)) {
                Log::notice('Booking Source not Active  to Fetch Bookings '. $booking_source_form_id);
                return;
            }

            //GET CARD
            $card = $this->getCard($this->user_account, $this->propertyInfo, $pms, null, null,$pms_booking);
            $card->adjust_first_last_name_if_empty_any();

            $typeOfPaymentCard = $this->getTypeOfPaymentCard($pms_booking, $this->user_account);

            //BOOKING_INFO RECORD
            $booking_info = $this->insertBookingInfoRecord($this->user_account, $this->propertyInfo, $pms_booking, $typeOfPaymentCard, $booking_source_form_id, false);

            //TRANSACTION_INITS | CC AUTH | SD AUTH RECORDS
            $this->insertTransactionRecords($this->user_account, $this->propertyInfo, $pms_booking, $booking_info, $card, $booking_source_form_id, $typeOfPaymentCard);

        } catch (PmsExceptions $exception) {

            $this->fetchingFailed($exception->getMessage());

            //send email to notify client
            event(new EmailEvent(config('db_const.emails.heads.booking_fetch_failed.type'), $this->user_account->id, [ 'errorCode' => $exception->getPMSCode(), 'exceptionMsg' => $exception->getMessage(),'exceptionType' => config('db_const.booking_fetching_failed.exception_type.pms_exception')]));

            log_exception_by_exception_object($exception, []);

        } catch (Exception $exception) {
            $this->fetchingFailed($exception->getMessage());
            log_exception_by_exception_object($exception, []);
        }
    }


    /**
     * @param array|null $pms_bookings
     * @return bool
     */
    private function isBookingFound(array $pms_bookings = null)
    {
        if (!is_null($pms_bookings) && is_array($pms_bookings) && count($pms_bookings) > 0) {
            return true;
        } else {
            $this->fetchingFailed('Booking not found on PMS');
            Log::notice('Booking not found on PMS', ['File' => __FILE__, 'content' => []]);
            return false;
        }
    }

    /**
     * @return integer
     * @throws Exception
     */
    private function bookingSourceFormId()
    {
        return self::$bsRepo::getBookingSourceFormIdByChannelCode($this->user_account->pms->pms_form_id, $this->booking_channel_code);
    }

    /**
     * @param $exceptionMsg
     */
    private function fetchingFailed(string $exceptionMsg = null)
    {
        // TODO: fix status to proper code !
        // TODO: pass parameters to it rather than using $this approach
        try {
            FailedBooking::create([ 'user_account_id'  => $this->user_account->id,
                'channel_code'     => $this->booking_channel_code,
                'pms_property_id'  => $this->propertyInfo->pms_property_id,
                'pms_booking_id'   => $this->reservations[0]->UniqueID,
                'status'           => 'new',
                'exception'        => $exceptionMsg]);
        } catch (Exception $exception) {
            log_exception_by_exception_object($exception, $this->log_content_json);
        }
    }

    /**
     * In This function we check if booking status was changed to 'new' from 'modify'
     * by us, and booking-time is not old than 24 hours. If booking-time is old than
     * 24 hours then we ignore this request.
     * @param Booking $pms_booking
     * @param $isBookingStatusChanged
     * @param $userAccount
     * @return boolean
     */
    private function isOldBooking(Booking $pms_booking, $isBookingStatusChanged, $userAccount) {

        try {

            if($userAccount->integration_completed_on == null)
                return true;


            if($isBookingStatusChanged) {

                $bookingTime = Carbon::parse($pms_booking->bookingTime);
                $integrationTime = Carbon::parse($userAccount->integration_completed_on);

                if($bookingTime->isAfter($integrationTime)) {

                    // After Integration

                    if($bookingTime->isBefore(now("GMT")->subHours(24)))
                        return true;
                    else
                        return false;

                } else {
                    // Before Integration
                    return true;
                }

            }

        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Data' => $this->log_content_json,
                'Stack' => $e->getTraceAsString()
            ]);
        }

        return false;
    }

    private function sync_rooms(array $reservations, UserAccount $userAccount, PropertyInfo $propertyInfo) {

        try {

            /**
             * @var $room SMX_Room
             * @var $res SMX_Reservation
             * @var $roomInfos array
             */
            $roomInfos = RoomInfo::where('property_info_id', $propertyInfo->id)
                ->select('name')
                ->get()
                ->pluck(['name'])
                ->toArray();

            foreach($reservations as $res) {
                foreach ($res->rooms as $room) {
                    if(!in_array($room->RoomType, $roomInfos)) {
                        RoomInfo::create([
                            'name' => $room->RoomType,
                            'property_info_id' => $propertyInfo->id,
                            'pms_room_id' => '',
                            'available_on_pms' => 1
                        ]);
                    }
                }
            }

        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'UserAccount' => $userAccount->id . " - " . $propertyInfo->name,
                'Stack' => $e->getTraceAsString()
            ]);
        }

    }

}
