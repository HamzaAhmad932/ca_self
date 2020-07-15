<?php

namespace App\Http\Controllers;

use App\PropertyInfo;
use App\System\PMS\SiteMinder\SMX_Reservation;
use App\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\System\PMS\SiteMinder\SMX_Parser;
use App\Jobs\SMX_NewBookingJob;
use App\System\PMS\exceptions\PmsExceptions;

use App\Siteminder;

/**
 * Description of ApiPmsSiteMinder
 *
 * @author mmammar
 */
class ApiPmsSiteMinder extends Controller {

    public function __construct() {
        
    }
    
    public function receiveReservation (Request $request) {

        $parser = new SMX_Parser();
        $content = $request->getContent();

        try {

            $raw_booking = new Siteminder();
            $raw_booking->request_data = $content;
            $raw_booking->save();

            $bookings = $parser->parseReservation($content);

            /*
             * Here we can get an array of bookings
             * TODO - We need to save IDs and stay dates for both?
             */
            $raw_booking->unique_booking_id = $bookings[0]->UniqueID;
            $raw_booking->check_in_date = $bookings[0]->Start;
            $raw_booking->check_out_date = $bookings[0]->End;
            $raw_booking->hotel_code = $bookings[0]->HotelCode;

            $raw_booking->save();

            return $this->handelReservation($parser, $content, $bookings);

        } catch (PmsExceptions $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__]);
            return $this->errorResponseForSiteMinder($parser, $content, $e->getErrorType(), $e->getCode(), $e->getMessage());

        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__,'Stack' => $e->getTraceAsString()]);
            return $this->errorResponseForSiteMinder($parser, $content, PmsExceptions::SMX_EWT_Processing_exception, PmsExceptions::SMX_ERR_System_error, $e->getMessage());
        }

    }

    /**
     * @param SMX_Parser $parser
     * @param string $content
     * @param array $bookings
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws PmsExceptions
     */
    public function handelReservation(SMX_Parser $parser, string $content, array $bookings) {

        if(!empty($bookings)) {

            /**
             * @var $smx_reservation SMX_Reservation
             */
            $smx_reservation = $bookings[0];

            $propertyInfo = PropertyInfo::where('pms_property_id', $smx_reservation->HotelCode)
                ->with('user_account')
                ->first();

            if(empty($propertyInfo->user_account)) {
                $ex = new PmsExceptions("Hotel Not found", PmsExceptions::SMX_ERR_Hotel_not_active);
                $ex->setErrorType(PmsExceptions::SMX_EWT_No_implementation);
                throw $ex;
            }

            /**
             * @var $userAccount UserAccount
             */
            $userAccount = $propertyInfo->user_account;

            SMX_NewBookingJob::dispatch($userAccount, $propertyInfo, $bookings, $content);//->onQueue('ba_new_bookings');

            $responseSuccess = $parser->successResponseForReservationNotification($content, $bookings);
            $headers = ['Content-type' => 'application/xml'];
            return response($responseSuccess, 200, $headers);

        } else {
            $ex = new PmsExceptions("No, Reservations found.", PmsExceptions::SMX_ERR_Unable_to_process);
            $ex->setErrorType(PmsExceptions::SMX_EWT_Unknown);
            throw $ex;
        }
    }

    private function errorResponseForSiteMinder(SMX_Parser $parser, string $content, $errorType, $errorCode, $errorMessage) {
        $errors[] = ['type' => $errorType, 'code' => $errorCode, 'message' => $errorMessage];
        $responseError = $parser->errorResponseForReservationNotification($content, $errors);
        $headers = ['Content-type' => 'application/xml'];
        return response($responseError, 400, $headers);
    }

    
}
