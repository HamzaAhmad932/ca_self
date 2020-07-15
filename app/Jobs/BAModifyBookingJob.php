<?php

namespace App\Jobs;

use App\BookingInfo;
use App\Services\CapabilityService;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\UserAccount;
use App\PropertyInfo;
use App\CreditCardInfo;
use App\System\PMS\PMS;
use App\TransactionInit;
use App\UserPaymentGateway;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\CreditCardAuthorization;
use App\System\PMS\Models\Booking;
use Illuminate\Support\Facades\Log;
use App\Events\BAModifyBookingsEvent;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\InvoiceItem;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Bookings\Bookings;
use Illuminate\Queue\InteractsWithQueue;
use App\System\PaymentGateway\Models\Card;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\BookingSources\BookingSources;
use App\Jobs\BACancelBookingJob;

class BAModifyBookingJob implements ShouldQueue
{
    // TODO :: Add Checks for Token Received or not before further processing request.
    // TODO :: Add Booking Status on BA notify_url.
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var UserAccount
     */
    private $userAccount;
    private $bookingId;
    private $bookingChannelCode;
    private $propertyId;
    private $bookingStatus;
    private $token;
    private $cvv;

    /**
     * @var Bookings
     */
    private $repBookings;
    public $tries = 3;

    private $log_details = [];
    const emergency = 1;
    const alert = 2;
    const critical = 3;
    const error = 4;
    const warning = 5;
    const notice = 6;
    const info = 7;
    const debug = 8;

    /**
     * Modify Booking .
     *
     * @param UserAccount $userAccount
     * @param $bookingId
     * @param $bookingChannelCode
     * @param $propertyId
     * @param $bookingStatus
     * @param $token
     * @param $cvv
     */
    public function __construct(UserAccount $userAccount, $bookingId, $bookingChannelCode, $propertyId, $bookingStatus, $token = null, $cvv = null) {

        $this->userAccount = $userAccount;
        $this->bookingId = $bookingId;
        $this->bookingChannelCode = $bookingChannelCode;
        $this->propertyId = $propertyId;
        $this->bookingStatus = $bookingStatus;
        $this->token = $token;
        $this->cvv = $cvv;
        $this->repBookings = new Bookings($this->userAccount->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    
        try {

            $this->log_details = [
                'file' => __FILE__,
                'user_account_id' => $this->userAccount->id,
                'pms_booking_id' => $this->bookingId,
                'channel_code' => $this->bookingChannelCode,
                'pms_property_id' => $this->propertyId,
                'notification_status' => $this->bookingStatus,
                'card-token' => $this->token,
                'card-cvv' => $this->cvv
            ];

            // Checking if booking exists in database
            /**
             * @var $bookingInfo BookingInfo
             */
            $bookingInfo = BookingInfo::where([
                ['pms_booking_id', $this->bookingId],
                ['user_account_id', $this->userAccount->id],
                ['property_id', $this->propertyId]
                ])->first();
            $propertyIdChanged = false;
            $propertyInfo = PropertyInfo::where('user_account_id', $this->userAccount->id)->where('pms_property_id', $this->propertyId)->first();
            if (is_null($bookingInfo)) {

                /**
                 * @var $bi BookingInfo
                 */
                $bi = BookingInfo::where([
                    ['pms_booking_id', $this->bookingId],
                    ['user_account_id', $this->userAccount->id]
                ])->first();

                if($bi == null) {
                    // $this->log(self::notice,"Booking not found in DB ");
                    return;
                }

                /*
                 * Sometime BA changes property and a modify message is sent with different property ID, which causes
                 * Other operations to fail specially charging, So here we are updating new property ID against booking.
                 */
                if($bi->property_id != $this->propertyId) {
                    $bi->property_id = $this->propertyId;
                    $bi->property_info_id = $propertyInfo->id;
                    $bi->save();
                    $bi->refresh();
                    $bookingInfo = $bi;
                    $propertyIdChanged = true;
                }

            }

            // Check Booking time and Modify Notification Request Time to entertain
            if (now()->subSeconds(15) < $bookingInfo->booking_time) {
                $this->log(self::notice, "Modify notification not Entertained,
                Notification time Less than (booking_time + 15 seconds)");
                return;
            }

            $isPropertyActive = !empty($propertyInfo) && ($propertyInfo->status == 1);

            $pms = new PMS($this->userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->includeCard = true;
            //$pmsOptions->propertyID = '';//$this->propertyId;
            $pmsOptions->bookingID = $this->bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;

            if(isset($this->log_details) && is_array($this->log_details)) {

                $this->log_details['property_key'] = $propertyInfo->property_key;

            }


            /**
             * Update booking Status  & get BookingDetails from PMS with Json RequestType
             */
            $resultFromJsonRequest = $this->updateBookingStatus($bookingInfo, $pms, $pmsOptions);
            if(count($resultFromJsonRequest) == 0)
                return;

            $ccInfo = CreditCardInfo::where('booking_info_id', $bookingInfo->id)
                ->where('is_vc', $bookingInfo->is_vc == 'VC' ? 1 : 0)
                ->latest('id')
                ->limit(1)
                ->first();

            if ($isPropertyActive || $propertyIdChanged) {

                $bookingSourceRepo = new BookingSources();
                $is_activeBookingSource = $bookingSourceRepo->isBookingSourceActive($propertyInfo, $bookingInfo->bookingSourceForm->id);

                $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
                /**
                 * Fetch BookingDetails form PMS with XML RequestType
                 */
                //$result = $pms->fetch_Booking_Details($pmsOptions);
                $result = $pms->fetch_Booking_Details($pmsOptions);
                //Log::notice('Testing XML response in BAModifyJob', ['content'=> json_encode($result)]);
                if(count($result) > 0) {
                    for($j = 0; $j < count($resultFromJsonRequest); $j++) {
                        for($x = 0; $x < count($result); $x++) {
                            if($resultFromJsonRequest[$j]->id == $result[$x]->id) {
                                $result[$x]->infoItems = $resultFromJsonRequest[$j]->infoItems;
                                $result[$x]->currencyCode = $resultFromJsonRequest[$j]->currencyCode;
                                $result[$x]->apiMessage = $resultFromJsonRequest[$j]->apiMessage;
                                continue;
                            }
                        }
                    }

                    /**
                     * @var $booking Booking
                     */
                    foreach ($result as $booking) {
                        
                        if($booking->id == $bookingInfo['pms_booking_id']) {
                            $balance = $booking->balancePrice;
                            $invoiceItems = $booking->invoice;
                            if($invoiceItems != null && is_array($invoiceItems) && count($invoiceItems) > 0) {
                                $balance = 0.0;
                                $type200Count = 0;
                                /**
                                 * @var $item InvoiceItem
                                 */
                                foreach ($invoiceItems as $item) {
                                    if( ((int) $item->type) < 200) {
                                        $balance += ((float)$item->price) * ((int)$item->quantity);
                                        $type200Count++;
                                    }
                                }
                                if($type200Count == 0)
                                    $balance = $booking->balancePrice;
                            }

                            $booking->balancePrice = $balance;
                            $bookingInfo->full_response = json_encode($booking);

                            $bookingInfo->room_id = $booking->roomId;
                            $bookingInfo->unit_id = $booking->unitId;
                            $bookingInfo->save();

                            if (!$is_activeBookingSource) {
                                // $this->log(self::notice, 'Booking Source not Active');
                                return;
                            }

                            if (!CapabilityService::isAnyPaymentOrSecuritySupported($bookingInfo)) {
                                $this->log(self::notice, 'Booking Source not Capable for Payments');
                                return;
                            }

                            if (!$isPropertyActive) {
                                $this->log(self::notice, 'Property Info Not Active');
                                return;
                            }

                            $typeOfPaymentSource = $booking->getTypeofPaymentSource();

                            $pmsOptions->bookingToken = $this->token;
                            $pmsOptions->cardCvv = $this->cvv;
                            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;

                            /**
                             * Creating Card to pass in event call for transaction init
                             */
                            $card = new Card();

                            if($this->isProcessableBookingSource($booking->channelCode) && !empty($this->token)){
                                Log::notice('Logging Token at BAModifyJob: ', ['token'=> $this->token]);
                                $card = Bookings::BA_GetCard($this->userAccount, $pms, $pmsOptions, $booking);
                            }

                            $card->currency = $propertyInfo->currency_code;
                            $card->adjust_first_last_name_if_empty_any();

                            /**
                             * Remove Token from Object to secure card
                             */
                            $pmsOptions->bookingToken = null;

                            if($typeOfPaymentSource != 'BT') {
                                PaymentGateways::addMetadataInformation($bookingInfo, $card, BAModifyBookingJob::class);
                            }
                            //Removing Card information from booking object, to store full response for security reason
                            $booking->cardNumber = null;
                            $booking->cardExpire = null;
                            $booking->cardCvv = null;
                            //$booking->cardName = null;
                            $booking->cardType = null;

                            $usingPaymentGateway = ($propertyInfo->use_pg_settings == 1 ? $propertyInfo->id : 0);

                            $userPaymentGateway = UserPaymentGateway::where('user_account_id',$this->userAccount->id)->where('property_info_id',$usingPaymentGateway)->first();

                            if (!empty($userPaymentGateway)) {

                                /**
                                 * VIP Note: dueDate is used to detect if this booking has been converted to VC from CC.
                                 * A null value will indicate that its normal and setting it to date as string will
                                 * indicate that this booking is not VC so it will be
                                 * handled accordingly in BAModifyBookingsEvent/Listener
                                 * @var $dueDate null|string
                                 */
                                $dueDate = null;

                                if($this->is_booking_changed_to_VC_from_CC($booking, $bookingInfo) && !empty($card->cardNumber)) {

                                    /**
                                     * Note setting $typeOfPaymentSource to VC here so that BAModifyBookingsEvent/Listener
                                     * can handel it accordingly. e.g. as VC Booking
                                     * @var $typeOfPaymentSource string
                                     */
                                    $typeOfPaymentSource = BS_Generic::PS_VIRTUAL_CARD;

                                    $dueDate = $this->getVcDueDate($booking, $bookingInfo, $this->userAccount, $propertyInfo);

                                    Log::notice('Test CC2VC: ' . $bookingInfo->pms_booking_id);
                                }

                                if(!empty($card->cardNumber))
                                    event(new BAModifyBookingsEvent(
                                        $card,
                                        $booking,
                                        $this->userAccount,
                                        $typeOfPaymentSource,
                                        $propertyInfo->id,
                                        $propertyInfo->user_id,
                                        $userPaymentGateway,
                                        $bookingInfo,
                                        $dueDate));

                                //Check for previous pms booking fetching failed system job and update by success status
                                $this->repBookings->UpdateSystemJobOnSuccess($this->userAccount->id, $bookingInfo->pms_booking_id, $bookingInfo->property_id, $this->bookingStatus, $bookingInfo->id);

                            } elseif(empty($userPaymentGateway)) {
                                $this->log(self::notice, "Payment Gateway not Active.", ['property_info_id'=>$propertyInfo->id]);
                            } else {
                                $this->log(self::notice, "Booking channel not supported for payments.", ['property_info_id'=>$propertyInfo->id]);
                            }
                        }
                    }
                } else {
                    $this->log(self::debug, "Booking not found!");
                }
            } else {
                $this->log(self::notice, "Property Info not found or Not Active ");
            }

            if($ccInfo != null) {
                if($ccInfo->auth_token != null && $ccInfo->auth_token != '') {

                    $transactions = TransactionInit::where('booking_info_id', $bookingInfo->id)->get();

                    $failedTranCount = $transactions->whereIn('payment_status',
                        [TransactionInit::PAYMENT_STATUS_FAIL, TransactionInit::PAYMENT_STATUS_REATTEMPT])
                        ->where('booking_info_id', $bookingInfo->id)
                        ->count();

                    $pendingTranCount = $transactions->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
                        ->where('lets_process', 0)
                        ->where('final_tick', 0)
                        ->where('booking_info_id', $bookingInfo->id)
                        ->count();

                    $ccAuthFailedReAttemptCount = CreditCardAuthorization::where('user_account_id', $ccInfo->id)
                        ->whereIn('status', [5, 7])
                        ->get()
                        ->count();

                    if($failedTranCount == 0 && $pendingTranCount == 0 && $ccAuthFailedReAttemptCount == 0) {
//                        Log::notice("No Failed transactions, customer-object and auth. Disregarding Modification message",
//                            [
//                                'File'=>BAModifyBookingJob::class,
//                                'BookingInfoId'=>$bookingInfo->id,
//                                'PMS Booking ID'=>$this->bookingId,
//                                'FailedTransCount' => $failedTranCount,
//                                'PendingTransCount' => $pendingTranCount,
//                                'Failed CC Auth Count' => $ccAuthFailedReAttemptCount
//                            ]);
                        return;
                    }
                }
            }

        } catch (PmsExceptions $e) {
            report($e);
            $this->log(self::error, $e->getMessage(), [], $e);
            // Check status Code of PMS Exception & perform some specific action regarding to Exception Code
            $this->repBookings->addFetchingBookingDetailsAPICallFailedSystemJobsEntry($this->userAccount, $bookingInfo->pms_booking_id, $this->bookingChannelCode, $bookingInfo->property_id, $this->bookingStatus, $e,  $bookingInfo->id);

        } catch (\Exception $e) {
            $this->log(self::error, $e->getMessage(), [], $e);
        }

    }

    /**
     *
     * Update Booking Status as on PMS
     *
     * @param BookingInfo $bookingInfo
     * @param PMS $pms
     * @param PmsOptions $pmsOptions
     *
     * @return array
     */

    private function updateBookingStatus(BookingInfo $bookingInfo, PMS $pms, PmsOptions $pmsOptions ) {

        try {

            $resultFromJsonRequest = [];

            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $resultFromJsonRequest = $pms->fetch_Booking_Details($pmsOptions);

            if(count($resultFromJsonRequest) > 0) {

                if($bookingInfo->pms_booking_status != $resultFromJsonRequest[0]->bookingStatus) {

                    $bookingInfo->pms_booking_status = $resultFromJsonRequest[0]->bookingStatus;
                    $bookingInfo->save();

                    if ($resultFromJsonRequest[0]->bookingStatus == 0) {
                        /**
                         * Cancel Booking with regarding cancellation policies
                         */
                        BACancelBookingJob::dispatch($this->userAccount, $this->bookingId, $this->bookingChannelCode, $this->propertyId, 'cancel', true)->onQueue('ba_cancel_bookings');
                        /**
                         * Don't do any action booking is Cancelled
                         */
                        return [];
                    }
                }
            }
        } catch (PmsExceptions $e) {
           $this->log(self::error, $e->getMessage(), ['Stack' => $e->getTraceAsString()]);
            // Check status Code of PMS Exception & perform some specific action regarding to Exception Code
            $this->repBookings->addFetchingBookingDetailsAPICallFailedSystemJobsEntry($this->userAccount, $bookingInfo->pms_booking_id, $this->bookingChannelCode, $bookingInfo->property_id, $this->bookingStatus, $e, $bookingInfo->id);
        } catch (\Exception $e) {
            $this->log(self::error, $e->getMessage(), ['Stack' => $e->getTraceAsString()]);
        }
        return $resultFromJsonRequest;
    }

    /**
     * Note: This function is a temporary check to avoid certain setting's creation and restrict API calls to BA like
     * Card fetch.
     * @param $channelCode
     * @return bool
     */
    private function isProcessableBookingSource($channelCode) {
        return in_array($channelCode, [19, 17, 14, 53]);
    }

    private function log(int $log, string $message, array $extra_details = [], $exceptionObject = null) {

        if (!empty($exceptionObject))
            log_exception_by_exception_object($exceptionObject);

        $details = array_merge($this->log_details, $extra_details);

        switch($log) {
            case 1:
                Log::emergency($message, $details);
                break;
            case 2:
                Log::alert($message, $details);
                break;
            case 3:
                Log::critical($message, $details);
                break;
            case 4:
                Log::error($message, $details);
                break;
            case 5:
                Log::warning($message, $details);
                break;
            case 6:
                Log::notice($message, $details);
                break;
            case 7:
                Log::info($message, $details);
                break;
            case 8:
                Log::debug($message, $details);
                break;
        }
    }

    /**
     * @param Booking $booking
     * @param BookingInfo $bookingInfo
     * @return bool
     */
    private function is_booking_changed_to_VC_from_CC(Booking $booking, BookingInfo $bookingInfo) {

        try {

            // Checking channel code and if previous booking was CC type
            if ($booking->channelCode == BS_BookingCom::BA_CHANNEL_CODE && $bookingInfo->is_vc == BS_Generic::PS_CREDIT_CARD) {

                if(empty($booking->guestComments) || empty($booking->apiMessage))
                    return false;

                $bs = new BS_BookingCom();
                $result = $bs->detectVC($booking->guestComments, $booking->apiMessage);
                return $result;

            }

        } catch (\Exception $e) {

            Log::error($e->getMessage(), [
                'BookingInfoId' => $bookingInfo->id,
                'BookingPmsID' => $booking->id,
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Stack' => $e->getTraceAsString()]);
        }

        return false;
    }

    private function getVcDueDate(Booking $booking, BookingInfo $bookingInfo, UserAccount $userAccount, PropertyInfo $propertyInfo) {

        $dueDate = $booking->getDueDateFromGuestComments();
        $dueDate = \Illuminate\Support\Carbon::parse($dueDate)->toDateTimeString();

        $bookingDate = Carbon::parse($bookingInfo->booking_time)->toDateString();
        $checkInDate = Carbon::parse($bookingInfo->check_in_date)->toDateString();

        if($bookingDate >= $checkInDate) {
            $dueDate = Carbon::parse($dueDate)->addMinute(10)->toDateTimeString();

        } else {
            $repoBookings = new Bookings($userAccount->id);
            $dueDateAddedHours = $repoBookings->addCheckInHours($dueDate); /* add Hours To Check-in Datetime */
            $dueDate = $repoBookings->setVcDueDateWithTimeZone($userAccount, $propertyInfo, $dueDateAddedHours);
        }
        return $dueDate;

    }

}
