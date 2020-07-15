<?php

namespace App\Jobs;

use App\Events\BAPropertyChangeEncounteredEvent;
use App\Repositories\Bookings\Bookings;
use App\Repositories\Settings\PaymentTypeMeta;
use App\UserPms;
use \Exception;
use App\BookingInfo;
use App\UserAccount;
use Illuminate\Bus\Queueable;
use App\CreditCardAuthorization;
use Illuminate\Support\Facades\Log;
use App\Events\BACancelBookingsEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\BookingSources\BookingSources;
use App\System\PMS\exceptions\PmsExceptions;


class BACancelBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var UserAccount
     */
    private $userAccount;
    private $bookingId;
    private $bookingChannelCode;
    private $propertyId;
    private $bookingStatus;
    private $isCancelRequestFromModifyJob = false;
    private $detailsArr = array();

    public $tries = 3;

    /**
     * @var Bookings
     */
    private  $repBookings;

    /**
     * @var PaymentTypeMeta
     */
    private $paymentTypeMeta;

    /**
     * Create a new job instance.
     *
     * @param UserAccount $userAccount
     * @param $bookingId
     * @param $bookingChannelCode
     * @param $propertyId
     * @param $bookingStatus
     * @param bool $isCancelRequestFromModifyJob
     */
    public function __construct(UserAccount $userAccount, $bookingId, $bookingChannelCode, $propertyId, $bookingStatus, $isCancelRequestFromModifyJob = false)
    {
        $this->userAccount = $userAccount;
        $this->bookingId = $bookingId;
        $this->bookingChannelCode = $bookingChannelCode;
        $this->propertyId = $propertyId;
        $this->bookingStatus = $bookingStatus;
        $this->detailsArr = ['pms_booking_id' => $bookingId, 'pms_property_id' => $propertyId, 'Booking Request Status'=> $bookingStatus, 'bookingChannelCode' => $bookingChannelCode , 'File' => 'BACancelBookingJob'];
        $this->isCancelRequestFromModifyJob = $isCancelRequestFromModifyJob;
        $this->repBookings = new  Bookings($this->userAccount->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){

        $bookingInfo = BookingInfo::where([['pms_booking_id', $this->bookingId], ['user_account_id', $this->userAccount->id], ['property_id', $this->propertyId] ])->first();

        if (is_null($bookingInfo))
            return;

        if (($bookingInfo->pms_booking_status == 0)  && ($bookingInfo->cancellationTime != null)) {

            // Log::notice('Booking Already Cancelled, Received Booking Cancellation Notification from PMS' , $this->detailsArr);

            //Check for previous pms booking fetching failed system job and update by success status
            $this->repBookings->UpdateSystemJobOnSuccess($this->userAccount->id, $bookingInfo->pms_booking_id, $bookingInfo->property_id, $this->bookingStatus, $bookingInfo->id);

            return;
        } else {

            $this->paymentTypeMeta = new PaymentTypeMeta();
            $countTransactions  = $bookingInfo->transaction_init->whereIn('transaction_type',[$this->paymentTypeMeta->getAutoRefund(), $this->paymentTypeMeta->getBookingCancellationAutoCollectionFull()])->count();

            if ($countTransactions > 0) {
                Log::emergency('Booking already have Cancellation Adjustment or Auto Refund Entries, Again Received Cancellation Request from PMS', $this->detailsArr);
                //Check for previous pms booking fetching failed system job and update by success status
                $this->repBookings->UpdateSystemJobOnSuccess($this->userAccount->id, $bookingInfo->pms_booking_id, $bookingInfo->property_id, $this->bookingStatus, $bookingInfo->id);
                return;
            }

            if(!$this->isCancelRequestFromModifyJob){

                try {
                    $pmsCurrentBookingStatus = Bookings::getPMSCurrentBookingStatus($this->userAccount, $bookingInfo->id);

                    if (($pmsCurrentBookingStatus == null) || (!isset($pmsCurrentBookingStatus['status'])) || ($pmsCurrentBookingStatus['status'] != 0 )) {

                        //check if booking property changed -- If changed then update new property inside event
                        if(event(new BAPropertyChangeEncounteredEvent($bookingInfo->pms_booking_id ) )) {

                            //booking property is now updated so recall same job again
                            BACancelBookingJob::dispatch($this->userAccount, $this->bookingId, $this->channelCode, $this->propertyId, $this->bookingStatus, false)
                                ->onQueue('ba_cancel_bookings');
                            Log::notice('Booking property was changed so this job will be recalled as we have updated the property info', $this->detailsArr);
                            return;
                        }

                        Log::notice('Booking Status from PMS to Verify Cancelled Booking is not Valid to entertain Booking, Booking Status => '.json_encode($pmsCurrentBookingStatus), $this->detailsArr);
                        return;
                    }

                } catch (PmsExceptions $e) {

                    log_exception_by_exception_object($e, $this->detailsArr);
                    /*Log::error($e->getMessage());
                    Log::error($e->getTraceAsString());*/

                    // Check status Code of PMS Exception & perform some specific action regarding to Exception Code
                    $this->repBookings->addFetchingBookingDetailsAPICallFailedSystemJobsEntry($this->userAccount, $bookingInfo->pms_booking_id, $this->bookingChannelCode, $bookingInfo->property_id, $this->bookingStatus, $e, $bookingInfo->id);
                    return;

                } catch (\Exception $e) {
                    log_exception_by_exception_object($e, $this->detailsArr);
                    /*Log::error($e->getMessage());
                    Log::error($e->getTraceAsString());*/
                    return;
                }
            }

            /**
             * Request is Valid, Cancel Booking & check Booking Type
             */
            $this->cancelBooking($bookingInfo);
        }

    }


    /**
     * @param BookingInfo $bookingInfo
     */

    private function cancelBooking(BookingInfo $bookingInfo)
    {
        try{

            switch ($bookingInfo->is_vc) {
                case BS_Generic::PS_CREDIT_CARD:

                    $propertyInfo = $this->userAccount->properties_info->where('pms_property_id', $this->propertyId)
                        ->where('pms_id', $bookingInfo->pms_id)->first();

                    if (!is_null( $propertyInfo )) {
                        if ($propertyInfo->status != 1){
                            Log::error( "Property not active to entertain BookingCancellation request", $this->detailsArr);
                            return;
                        }

                        $bookingSourceRepo = new BookingSources();
                        $is_activeBookingSource = $bookingSourceRepo->isBookingSourceActive($propertyInfo, $bookingSourceRepo::getBookingSourceFormIdByChannelCode($bookingInfo->pms_id, $this->bookingChannelCode));
                        if (!$is_activeBookingSource) {
                            // Log::error("Booking Source not active to entertain BookingCancellation request", $this->detailsArr );
                            return;
                        }
                        /**
                         * booking Cancellation Request Received Shifted to Listener to entertain
                         */
                        event(new BACancelBookingsEvent( $this->userAccount , $propertyInfo , $bookingInfo , $this->bookingChannelCode ,$this->bookingStatus ) );
                    }
                    break;
                default:
                    CreditCardAuthorization::where('booking_info_id', $bookingInfo->id)->update(['status' => CreditCardAuthorization::STATUS_VOID]);
                    Log::notice("Cancellation Request could not entertain Booking Type is ".$bookingInfo->is_vc,$this->detailsArr);
                    break;
            }
            
            $bookingInfo->cancellationTime = now()->toDateTimeString();
            $bookingInfo->pms_booking_status = 0;
            $bookingInfo->save();

            //Check for previous pms booking fetching failed system job and update by success status
            $this->repBookings->UpdateSystemJobOnSuccess($this->userAccount->id, $bookingInfo->pms_booking_id, $bookingInfo->property_id, $this->bookingStatus,  $bookingInfo->id);
        } catch (\Exception $e) {
            Log::error($e->getMessage(),$this->detailsArr);
            Log::error($e->getTraceAsString(),$this->detailsArr);
        }
    }
}
