<?php

namespace App\Listeners;

use App\BookingInfo;
use App\Events\BAPropertyChangeEncounteredEvent;
use App\Repositories\Bookings\BookingRepository;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use Illuminate\Support\Facades\Log;

class BAPropertyChangeEncounteredListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BAPropertyChangeEncounteredEvent  $event
     * @return boolean
     */
    public function handle(BAPropertyChangeEncounteredEvent $event)
    {
        try
        {
            $booking_info_obj = BookingInfo::where('pms_booking_id', $event->pms_booking_id)->first();

            //try with only XML to check if property change
            $pms_xml_booking = $this->fetchBookingDetailsXml($booking_info_obj);
            if ($pms_xml_booking instanceof Booking && $pms_xml_booking->propertyId != $booking_info_obj->property_id) {

                //this means booking is moved to some other property on PMS so update property
                $updated = BookingRepository::changeBookingProperty($pms_xml_booking->propertyId, $pms_xml_booking->roomId, $booking_info_obj->id);
                if($updated) return true;

            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage(), $this->detailsArray);
        }

        return false;
    }

    /**
     * @param BookingInfo $booking_info_obj
     * @return Booking|string
     */
    private function fetchBookingDetailsXml(BookingInfo $booking_info_obj)
    {
        try {

            $options = new PmsOptions();
            $options->propertyID = null;
            $options->requestType = PmsOptions::REQUEST_TYPE_XML;
            $options->bookingID = $booking_info_obj->pms_booking_id;

            $pms = new PMS($booking_info_obj->user_account); //passing UserAccount object
            $pms_booking = $pms->fetch_Booking_Details($options);

            if (!empty($pms_booking[0]) && $pms_booking[0] instanceof Booking) {
                return $pms_booking[0];
            } else {
                return 'No Booking Found';
            }

        } catch (PmsExceptions $exception) {

            log_exception_by_exception_object($exception, $this->current_record);
            return $exception->getCADefineMessage();
        }

    }
}
