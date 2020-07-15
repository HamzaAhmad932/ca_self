<?php

namespace App\Http\Controllers;

use App\CaCapability;
use App\Jobs\BANewBookingJobNew;
use App\PaymentGatewayForm;
use App\System\PMS\BookingAutomation\HookHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\BACancelBookingJob;
use App\Jobs\BAModifyBookingJob;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\General\PaymentGateway\AllSupportedPaymentGatewaysResource;

class ApiPmsBookingAutomation extends Controller {

    use HookHelper;

    public function __construct() {

    }

    public function receiveBooking(Request $request) {

        $newBookingDispatchDelay = 1; // Minute(s)

        try {

            $this->parseRequest($request);

            $this->insertApiRequestLog();

            if (!$this->isIncomingIpAllowed())
                return $this->replyToHook('Ip Not Allowed', 200);

            if(!$this->isPmsBookingStatusValid())
                return $this->replyToHook('Invalid Booking status', 200);

            if(!$this->doesRequiredParametersExists())
                return $this->replyToHook('Missing Required Parameters', 200);

            $userAccount = $this->getUserAccountObject($this->userAccountId, $this->bookingId, $this->propertyId);

            if($userAccount == null)
                return $this->replyToHook('UserAccount not found', 200);

            if($userAccount->status != config('db_const.user.status.active.value'))
                return $this->replyToHook('UserAccount not Active', 200);

            if(!$userAccount->isIntegrationCompleted())
                return $this->replyToHook('UserAccount Integration Not Completed', 200);

            if(!$this->isUserPropertyActive($userAccount))
                return $this->replyToHook('Property not Active', 200);

            if(!$this->isUserPmsVerified($userAccount))
                return $this->replyToHook('PMS not Verified', 200);

            if(!$this->checkBookingSourceCapabilityByName($userAccount, CaCapability::FETCH_BOOKING))
                return $this->replyToHook('Booking Source not supported for ' . CaCapability::FETCH_BOOKING, 200);

            if(!$this->isBookingSourceActiveToFetchBooking($userAccount))
                return $this->replyToHook('Booking Source not Active', 200);

            $isBookingStatusChanged = false;
            /**
             * Note: this patch was added due to BA start sending booking status as modify instead of new when booking
             * was created by setBooking JSON API.
             */
            if($this->bookingStatus == 'modify' && !$this->isBookingInDb($this->bookingId, $this->userAccountId)) {
                $this->bookingStatus = 'new';
                $newBookingDispatchDelay = 2;
                $isBookingStatusChanged = true;
                // Log::notice('Changing status to new for booking: ' . $this->bookingId . ' ApiPmsBookingAutomation class');
            }

            /**
             * Checking if Group Booking (child/sub booking) arrived before Master booking then we are storing
             * it in Booking Hold table, so it can be fetched later when master booking arrived.
             */
            if(!$this->shouldProcessIfGroupBooking($this->groupId, $this->userAccountId)) {
                $this->insertBookingOnHold($this->userAccountId, $this->bookingId, $this->groupId, $this->bookingStatus,
                    $this->channelCode, $this->propertyId, $this->token, $this->cvv, ApiPmsBookingAutomation::class);
                return response(null, 200);
            }
            
            switch ($this->bookingStatus) {

                case 'new':
                    BANewBookingJobNew::
                    dispatch($userAccount, $this->bookingId, $this->channelCode, $this->propertyId, $this->bookingStatus, $this->token, $this->cvv, $isBookingStatusChanged)
                        ->delay(now()->addMinutes($newBookingDispatchDelay))
                        ->onQueue('ba_new_bookings');
                    break;

                case 'modify':
                    BAModifyBookingJob::
                    dispatch($userAccount, $this->bookingId, $this->channelCode, $this->propertyId, $this->bookingStatus,  $this->token, $this->cvv)
                        ->onQueue('ba_modify_bookings');
                    break;

                case 'cancel':
                    BACancelBookingJob::
                    dispatch($userAccount, $this->bookingId, $this->channelCode, $this->propertyId, $this->bookingStatus, false)
                        ->onQueue('ba_cancel_bookings');
                    break;
            }

        } catch (Exception $e) {
            Log::error($e->getMessage(),
                [
                    'File'=>__CLASS__,
                    'function'=>__FUNCTION__,
                    'data' => json_decode(json_encode($request), true),
                    'stack'=>$e->getTraceAsString()
                ]);
                return response(null, 400);
        }
        return response(null, 200);
    }

    /**
     * Getting all CA Supported gateways to Show on Partners Page
     * @param Request $request
     * @return JsonResponse
     */
    public function allSupportedPaymentGateways(Request $request)
    {
        if (!empty($request->status) && ($request->status == 'active')) {
            $paymentGateways = PaymentGatewayForm::where('status', 1)->get();
        } else {
            $paymentGateways = PaymentGatewayForm::all();
        }

        return response()->json(new AllSupportedPaymentGatewaysResource($paymentGateways), 200);
    }
}
