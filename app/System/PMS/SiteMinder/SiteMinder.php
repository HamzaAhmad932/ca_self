<?php

namespace App\System\PMS\SiteMinder;

use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\System\PMS\PmsInterface;
use App\System\PMS\exceptions\PmsExceptions;
use App\UserAccount;
use App\System\PMS\SiteMinder\SMX_Parser;
use App\System\PMS\SiteMinder\SMX_JsonClient;
use Illuminate\Support\Facades\Log;


/**
 * Description of SiteMinder
 *
 * @author mmammar
 */
class SiteMinder implements PmsInterface, SiteMinderSpecific {

    /**
     * @var SMX_JsonClient
     */
    private $jsonClient;
    


    public function __construct(SMX_JsonClient $jsonClient) {
        $this->jsonClient = $jsonClient;
    }


    public function fetch_Booking_Details(UserAccount $user, PmsOptions $options) {
        
//        if(empty($options->dump))
//            throw new PmsExceptions('Booking data not found');
        
        try {
            
            $parser = new SMX_Parser();
            $smx_bookings = $parser->parseReservation($options->dump);
            
            $bookings = [];
            
            foreach ($smx_bookings as $smx) {
                
                $booking = new Booking();
                $parser->mapReservation2Booking($smx, $booking);
                $bookings[] = $booking;
                
            }
            
            return $bookings;
            
        } catch (\Exception $e) {
            throw new PmsExceptions($e->getMessage());
        }
        
    }

    public function fetch_Booking_Details_json_xml(UserAccount $user, PmsOptions $options) {
        
        return $this->fetch_Booking_Details($user, $options);
        
    }

    public function fetch_card_for_booking(UserAccount $user, PmsOptions $options) {
        
    }

    public function fetch_properties(UserAccount $user, PmsOptions $pmsOptions) {
        
        $parser = new SMX_Parser();
        $properties = [];
        
        try {
            
            $api = 'publishers/littlehotelier/hotels';

            $content = $this->jsonClient->makeJsonRequest($api);
            $hotels = $parser->parsePublisherHotels($content);
            
            for($i = 0; $i < count($hotels); $i++) {
                
                $hotel = $hotels[$i];
                $property = new Property();

                try {
                    if ($hotel->isMessageTypeSupported(SMX_Hotel::ARI)) {
                        $apiRoom = 'publishers/littlehotelier/hotels/' . $hotel->getCode() . '/roomTypes';
                        $contentRoom = $this->jsonClient->makeJsonRequest($apiRoom);
                        $rooms = $parser->parseHotelRooms($contentRoom);
                        $hotel->setRooms($rooms);
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage(), [
                        'File' => __FILE__,
                        'Function' => __FUNCTION__,
                        'Trace' => $e->getTraceAsString()
                    ]);
                }

                $parser->mapHotel2Property($hotel, $property);
                $properties[] = $property;
                
            }
            
            return $properties;
            
        } catch (\Exception $e) {
//            dd($e->getMessage());
            throw $parser->parseException($e);

        }
        
    }

    public function fetch_properties_json_xml(UserAccount $user, PmsOptions $options) {
        
        return $this->fetch_properties($user, $options);
    }

    public function fetch_property(UserAccount $user, PmsOptions $options) {
        
        return $this->fetch_properties($user, $options);
    }

    public function fetch_user_account(UserAccount $user, PmsOptions $options) {
        
    }

    public function getActualResponse() {
        
    }

    public function update_booking(UserAccount $user, PmsOptions $options, Booking $bookingToUpdateData) {
        
    }

    public function update_properties(UserAccount $user, PmsOptions $options, array $propertiesToUpdateData) {
        $props = '';
        //<property id="114739" action="modify"></property>
        /**
         * @var $property Property
         */
        foreach ($propertiesToUpdateData as $property)
            $props .= '<property id="'.$property->id.'" action="modify"></property>';

        return new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><properties>'.$props.'</properties>');
    }

    public function fetch_Publisher_list() {
        
        $parser = new SMX_Parser();
        
        try {
            
            $api = 'publishers';

            $content = $this->jsonClient->makeJsonRequest($api);
            
            $publishers = $parser->parsePublishers($content);
            
            return $publishers;
            
        } catch (\Exception $e) {   
            throw $parser->parseException($e);

        }
        
    }

}
