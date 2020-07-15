<?php

namespace App\Jobs;



use App\Events\Emails\EmailEvent;
use App\PropertyInfo;
use App\System\PMS\BookingAutomation\HookHelper;
use App\System\PMS\Models\Booking;
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


class BANewBookingJobNew implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BookingJobHelper, HookHelper;
    /**
     * @var UserAccount
     */
    private $user_account;
    private $pms_booking_id;
    private $booking_channel_code;
    private $pms_property_id;
    private $pms_booking_status;
    private $get_card_token;
    private $card_cvv;
    private $isBookingStatusChanged = false;

    /**
     * @var false|string|null
     */
    private $log_content_json = null;

    public $tries = 1;

    /**
     * Create new Booking.
     * @param UserAccount $user_account
     * @param $pms_booking_id
     * @param $booking_channel_code
     * @param $pms_property_id
     * @param $pms_booking_status
     * @param $get_card_token
     * @param $card_cvv
     */
    
    public function __construct(UserAccount $user_account, $pms_booking_id, $booking_channel_code, $pms_property_id,
                                $pms_booking_status, $get_card_token = null, $card_cvv = null, $isBookingStatusChanged = false) {
        $this->user_account = $user_account;
        $this->pms_booking_id = $pms_booking_id;
        $this->booking_channel_code = $booking_channel_code;
        $this->pms_property_id = $pms_property_id;
        $this->pms_booking_status = $pms_booking_status;
        $this->get_card_token = $get_card_token;
        $this->card_cvv = $card_cvv;
        $this->isBookingStatusChanged = $isBookingStatusChanged;
        $this->log_content_json = json_encode(['user_account_id' => $this->user_account->id,
            'pms_booking_id' => $this->pms_booking_id, 'pms_property_id' => $this->pms_property_id]);
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->bookingAlreadyExist())
                return;

            $this->init_booking_job_helper();

            $this->handleNewBookingNotifyReceived();

        } catch (Exception $e) {
            $this->log_content_json['stack'] = $e->getTraceAsString();
            Log::error($e->getMessage(), json_decode($this->log_content_json, true));
        }
    }

    /**
     * Handle New Booking Notify Web-hook.
     */
    private function handleNewBookingNotifyReceived()
    {
        try {

            /**
             * @var $property_info PropertyInfo
             * @var $pms PMS
             * @var $pms_booking Booking
             */

            $property_info = $this->user_account->properties_info->where('pms_property_id', $this->pms_property_id)->first();

            $pms_options = $this->getPmsOptions_FetchBooking($property_info, $this->pms_booking_id);

            $pms = new PMS($this->user_account);

            $pms_bookings = $pms->fetch_Booking_Details_json_xml($pms_options);

            if (!$this->isBookingFound($pms_bookings))
                return;

            $pms_booking = $pms_bookings[0];

            if($this->isOldBooking($pms_booking, $this->isBookingStatusChanged, $this->user_account))
                return;

            /**
             * Checking if Group Booking (child/sub booking) arrived before Master booking then we are storing
             * it in Booking Hold table, so it can be fetched later when master booking arrived.
             * Below code is from HookHelper Trait
             */
            if($pms_booking->isGroupBooking())
                if(!$this->shouldProcessIfGroupBooking($pms_booking->masterId, $this->user_account->id)) {
                    $this->insertBookingOnHold($this->user_account->id, $pms_booking->id, $pms_booking->masterId, $this->pms_booking_status,
                        $this->booking_channel_code, $this->pms_property_id, $this->get_card_token, $this->card_cvv, BANewBookingJobNew::class);
                    return;
                }


            $this->booking_channel_code = $pms_booking->channelCode = filteredChannelCode($pms_booking->channelCode);
            $booking_source_form_id  = $this->bookingSourceFormId();

            if (! self::$bsRepo::isActiveFetchBookingSetting($this->user_account, $booking_source_form_id)) {
                Log::notice('Booking Source not Active  to Fetch Bookings '.
                    $booking_source_form_id, [$this->log_content_json]);
                return;
            }

            //GET CARD
            $card = $this->getCard($this->user_account, $property_info, $pms, $this->get_card_token, $this->card_cvv,$pms_booking);
            $card->adjust_first_last_name_if_empty_any();

            $typeOfPaymentCard = $this->getTypeOfPaymentCard($pms_booking, $this->user_account);

            //BOOKING_INFO RECORD
            $booking_info = $this->insertBookingInfoRecord($this->user_account, $property_info, $pms_booking, $typeOfPaymentCard, $booking_source_form_id, false);
            //TRANSACTION_INITS | CC AUTH | SD AUTH RECORDS
            $this->insertTransactionRecords($this->user_account, $property_info, $pms_booking, $booking_info, $card, $booking_source_form_id, $typeOfPaymentCard);

        } catch (PmsExceptions $exception) {

            $this->fetchingFailed($exception->getMessage());

            //send email to notify client
            event(new EmailEvent(config('db_const.emails.heads.booking_fetch_failed.type'), $this->user_account->id, [ 'errorCode' => $exception->getPMSCode(), 'exceptionMsg' => $exception->getMessage(),'exceptionType' => config('db_const.booking_fetching_failed.exception_type.pms_exception'), 'pms_booking_id' => $this->pms_booking_id ]));

            log_exception_by_exception_object($exception, $this->log_content_json);

        } catch (Exception $exception) {
            $this->fetchingFailed($exception->getMessage());
            log_exception_by_exception_object($exception, $this->log_content_json);
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
            Log::notice('Booking not found on PMS', ['File' => __FILE__, 'content' => $this->log_content_json]);
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
        try {
            FailedBooking::create([ 'user_account_id'  => $this->user_account->id,
            'channel_code'     => $this->booking_channel_code,
            'pms_property_id'  => $this->pms_property_id,
            'pms_booking_id'   => $this->pms_booking_id,
            'status'           => $this->pms_booking_status,
            'exception'        => $exceptionMsg]);
        } catch (Exception $exception) {
            log_exception_by_exception_object($exception, $this->log_content_json);
        }
    }


    /**
     * @return bool
     */
    private function bookingAlreadyExist()
    {
        return $this->user_account->bookings_info->where('pms_booking_id', $this->pms_booking_id)->count() > 0;
    }
    
    /**
     * In This function we check if booking status was changed to 'new' from 'modify'
     * by us, and booking-time is not old than 24 hours. If booking-time is old than
     * 24 hours then we ignore this request.
     * @param type $pms_booking
     * @return boolean
     */
    private function isOldBooking($pms_booking, $isBookingStatusChanged, $userAccount) {
        
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
                'Data' => json_decode($this->log_content_json, true),
                'Stack' => $e->getTraceAsString()
            ]);
        }
        
        return false;
    }
    
}