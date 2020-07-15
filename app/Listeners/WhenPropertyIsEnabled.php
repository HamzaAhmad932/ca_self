<?php


namespace App\Listeners;


use App\BookingInfo;
use App\PropertyInfo;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use Illuminate\Support\Facades\Log;

abstract class WhenPropertyIsEnabled {

    protected function isCanceledOnPMS(PropertyInfo $propertyInfo, $pmsBookingId) {

        $response = $this->fetch_Booking_Details($propertyInfo, $pmsBookingId);

        if(is_array($response)) {
            if(count($response) > 0) {

                /**
                 * @var $booking Booking
                 */
                $booking = $response[0];
                $booking->adjustBookingStatusForXmlTextToInteger();

                if($booking->getBookingStatusAsText() == 'Cancelled')
                    return true;
            }
        }

        return false;
    }

    protected function isCanceledInDatabase(BookingInfo $bookingInfo) {
        if($bookingInfo->cancellationTime != null)
            return true;
        return false;
    }

    private function fetch_Booking_Details(PropertyInfo $propertyInfo, $bookingId){


        try {

            sleep(5);

            $userAccount = $propertyInfo->user_account;

            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
//            $pmsOptions->propertyID = $this->propertyId;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;

            $result = $pms->fetch_Booking_Details($pmsOptions);

            if(count($result) == 0)
                return [];

            /*
             * NOTE: calling again BookingAutomation API with JSON type request to fetch
             * infoItems, which are not present in XML type request
             */
            $pms = new PMS($userAccount);
            $pmsOptions = new PmsOptions();
            $pmsOptions->includeInfoItems = true;
            $pmsOptions->includeCard = true;
            $pmsOptions->includeInvoice = true;
            $pmsOptions->bookingID = $bookingId;
            $pmsOptions->propertyKey = $propertyInfo->property_key;
            $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_JSON;
            $pmsOptions->propertyID = $propertyInfo->pms_property_id;
            $resultFromJsonRequest = $pms->fetch_Booking_Details($pmsOptions);

            for($j = 0; $j < count($resultFromJsonRequest); $j++) {
                for($x = 0; $x < count($result); $x++) {
                    if($resultFromJsonRequest[$j]->id == $result[$x]->id) {
                        $result[$x]->infoItems = $resultFromJsonRequest[$j]->infoItems;
                        $result[$x]->currencyCode = $resultFromJsonRequest[$j]->currencyCode;
                        continue;
                    }
                }
            }


            return $result;

        } catch (PmsExceptions $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'pms_code' => $e->getPMSCode()]);
            return $e->getMessage();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'BookingId' => $bookingId, 'Function'=>__FUNCTION__, 'Stack'=>$e->getTraceAsString()]);
            return $e->getMessage();
        }
    }

}