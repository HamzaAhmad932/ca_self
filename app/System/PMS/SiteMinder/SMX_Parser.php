<?php

namespace App\System\PMS\SiteMinder;

use SimpleXMLElement;
use App\System\PMS\SiteMinder\SMX_Room;
use App\System\PMS\SiteMinder\SMX_Guest;
use App\System\PMS\SiteMinder\SMX_Reservation;
use Exception;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\PMS;
use Illuminate\Support\Facades\Log;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\Property;

/**
 * Description of ReservationParser
 *
 * @author mmammar
 */
class SMX_Parser {
    
    private $reservations = [];
    private $nameSpaceOpenTravel = 'http://www.opentravel.org/OTA/2003/05';

    /**
     * @param string $content
     * @return array
     * @throws PmsExceptions
     */
    public function parseReservation(string $content) {
        
        $bookings = [];
        
        if(empty($content))
            return $bookings;
        
        try {
            
            /**
             * @var $xml SimpleXMLElement
             */
            $xml = simplexml_load_string($content);

            $xml->registerXPathNamespace('ns', $this->nameSpaceOpenTravel);

            $reservations = $xml->xpath('//ns:OTA_HotelResNotifRQ/ns:HotelReservations');

            if(empty($reservations)) {
                $ex = new PmsExceptions("Error parsing reservation notification.", PmsExceptions::SMX_ERR_Unable_to_process);
                $ex->setErrorType(PmsExceptions::SMX_EWT_Processing_exception);
                throw $ex;
            }


            $this->reservations = $reservations;

            foreach($reservations as $reservation) {

                $res = $reservation->HotelReservation;
                $attributes = $res->attributes();

                $b = new SMX_Reservation();

                $b->UniqueID = (string) $res->UniqueID->attributes()->ID;
                $b->CreateDateTime = (string) $attributes->CreateDateTime;
                $b->LastModifyDateTime = (string) $attributes->LastModifyDateTime;
                $b->ResStatus = (string) $attributes->ResStatus;

                foreach($res->POS->children() as $source) {

                    if(isset($source->RequestorID))
                        $b->RequestorID = (string) $source->RequestorID->attributes()->ID;

                    if($source->BookingChannel->CompanyName) {
                        $b->BookingChannelCompanyName = (string) $source->BookingChannel->CompanyName;
                        $b->BookingChannelType = (string) $source->BookingChannel->attributes()->Type;
                        $b->BookingChannelPrimary = (string) $source->BookingChannel->attributes()->Primary;
                    }
                }

                foreach($res->RoomStays->children() as $room) {

                    $r = new SMX_Room();

                    if($room->RoomTypes->RoomType !== null) {
                        $r->RoomType = (string) $room->RoomTypes->RoomType->attributes()->RoomType;
                        $r->RoomTypeCode = (string) $room->RoomTypes->RoomType->attributes()->RoomTypeCode;
                        $r->RoomTypeDescription = (string) $room->RoomTypes->RoomType->RoomDescription->Text;
                    }

                    if($room->RatePlans->RatePlan !== null) {
                        $r->RatePlanName = (string) $room->RatePlans->RatePlan->attributes()->RatePlanName;                
                        $r->RatePlanCode = (string) $room->RatePlans->RatePlan->attributes()->RatePlanCode;
                        $r->RatePlanEffectiveDate = (string) $room->RatePlans->RatePlan->attributes()->EffectiveDate;
                        $r->RatePlanExpireDate = (string) $room->RatePlans->RatePlan->attributes()->ExpireDate;
                        $r->RatePlanDescription = (string) $room->RatePlans->RatePlan->RatePlanDescription->Text;
                    }

                    if($room->RoomRates->RoomRate !== null) {
                        $r->RoomRateNumberOfUnits = (string) $room->RoomRates->RoomRate->attributes()->NumberOfUnits;

                        if($room->RoomRates->RoomRate->Rates !== null) {
                            $r->RoomRateUnitMultiplier = (string) $room->RoomRates->RoomRate->Rates->Rate->attributes()->UnitMultiplier;
                            $r->RoomRateAmountAfterTax = (string) $room->RoomRates->RoomRate->Rates->Rate->Base->attributes()->AmountAfterTax;
                            $r->RoomRateCurrencyCode = (string) $room->RoomRates->RoomRate->Rates->Rate->Base->attributes()->CurrencyCode;
                        }
                    }
                    
                    foreach($room->GuestCounts->children() as $guestCount) {
                        $ageCode = (string) $guestCount->attributes()->AgeQualifyingCode;
                        $gCount = (string) $guestCount->attributes()->Count;
                        $r->setGuestCount($ageCode, $gCount);
                    }

                    $r->Start = (string) $room->TimeSpan->attributes()->Start;
                    $r->End = (string) $room->TimeSpan->attributes()->Start;
                    $r->TotalAmountAfterTax = (string) $room->Total->attributes()->AmountAfterTax;

                    $b->rooms[] = $r;

                }


                foreach($res->ResGuests->children() as $guest) {

                    $g = new SMX_Guest();
                    $p = $guest->Profiles->ProfileInfo->Profile;

                    $g->ProfileType = (string) $p->attributes()->ProfileType;
                    $g->VIP_Indicator = (string) $p->Customer->attributes()->VIP_Indicator;
                    $g->GivenName = (string) $p->Customer->PersonName->GivenName;
                    $g->Surname = (string) $p->Customer->PersonName->Surname;
                    $g->PhoneTechType = (string) $p->Customer->Telephone->attributes()->PhoneTechType;
                    $g->PhoneNumber = (string) $p->Customer->Telephone->attributes()->PhoneNumber;
                    $g->Email = (string) $p->Customer->Email;
                    $g->AddressLine = (string) $p->Customer->Address->AddressLine;
                    $g->CityName = (string) $p->Customer->Address->CityName;
                    $g->PostalCode = (string) $p->Customer->Address->PostalCode;
                    $g->StateProv = (string) $p->Customer->Address->StateProv;
                    $g->CountryName = (string) $p->Customer->Address->CountryName;
                    $g->CompanyName = (string) $p->CompanyInfo->CompanyName;

                    if(!empty($guest->Comments)) {
                        foreach ($guest->Comments->children() as $comment) {

                            $c = new SMX_Comment();

                            $c->isGuestViewable = filter_var($comment->attributes()->GuestViewable, FILTER_VALIDATE_BOOLEAN);
                            $c->comment = (string)$comment->Text;

                            $b->comments[] = $c;
                        }
                    }

                    $b->guests[] = $g;
                }

                $b->End = (string) $res->ResGlobalInfo->TimeSpan->attributes()->End;
                $b->Start = (string) $res->ResGlobalInfo->TimeSpan->attributes()->Start;
                $b->TotalAmountAfterTax = (string) $res->ResGlobalInfo->Total->attributes()->AmountAfterTax;
                $b->CurrencyCode = (string) $res->ResGlobalInfo->Total->attributes()->CurrencyCode;
                $b->TotalTaxAmount = (string) $res->ResGlobalInfo->Total->Taxes->attributes()->Amount;
                $b->ResID_Type = (string) $res->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Type;
                $b->ResID_Source = (string) $res->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Source;
                $b->ResID_Value = (string) $res->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Value;
                $b->HotelCode = (string) $res->ResGlobalInfo->BasicPropertyInfo->attributes()->HotelCode;


                $bookings[] = $b;
            }
        
        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__, 'Trace' => $e->getTraceAsString()]);
            $ex = new PmsExceptions($e->getMessage(), PmsExceptions::SMX_ERR_Unable_to_process);
            $ex->setErrorType(PmsExceptions::SMX_EWT_Processing_exception);
            throw $ex;
        }
        
        return $bookings;
    }

    /**
     * @param string $publishersJson
     * @return array
     * @throws PmsExceptions
     */
    public function parsePublishers(string $publishersJson) {
        
        $publishers = [];
        
        try {
            
            $publishers_array = json_decode($publishersJson, true);
            
            foreach($publishers_array as $pub) {
                
                $p = new SMX_Publisher();
                
                if(key_exists('code', $pub))
                    $p->setCode($pub['code']);
                
                if(key_exists('name', $pub))
                    $p->setName($pub['name']);
                
                if(key_exists('messageTypes', $pub))
                    $p->setMessageTypes ($pub['messageTypes']);
                
                $publishers[] = $p;
                
            }
            
        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__]);
            $ex = new PmsExceptions($e->getMessage(), PmsExceptions::SMX_ERR_Unable_to_process);
            $ex->setErrorType(PmsExceptions::SMX_EWT_Processing_exception);
            throw $ex;
        }
        
        return $publishers;
        
    }

    /**
     * @param string $hotelsJson
     * @return array
     * @throws PmsExceptions
     */
    public function parsePublisherHotels(string $hotelsJson) {
        
        $hotels = [];
        
        try {
            
            // [{"code":"CHR0001","name":"Charge Automation","currency":"CAD","timezone":"America/Vancouver","messageTypes":["Reservations"]}]
            
            $hotels_array = json_decode($hotelsJson, true);
            
            foreach($hotels_array as $hotel){
                
                $h = new SMX_Hotel();
                
                if(key_exists('code', $hotel))
                    $h->setCode($hotel['code']);
                
                if(key_exists('name', $hotel))
                    $h->setName($hotel['name']);
                
                if(key_exists('currency', $hotel))
                    $h->setCurrency($hotel['currency']);
                
                if(key_exists('timezone', $hotel))
                    $h->setTimezone($hotel['timezone']);
                
                if(key_exists('messageTypes', $hotel))
                    $h->setMessageTypes($hotel['messageTypes']);
                
                $hotels[] = $h;
                
            }
            
            
        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__]);
            $ex = new PmsExceptions($e->getMessage(), PmsExceptions::SMX_ERR_Unable_to_process);
            $ex->setErrorType(PmsExceptions::SMX_EWT_Processing_exception);
            throw $ex;
        }
        
        return $hotels;
        
    }

    /**
     * @param string $roomsJson
     * @return array
     * @throws PmsExceptions
     */
    public function parseHotelRooms(string $roomsJson) {
        
        $rooms = [];
        
        try {
            
            $roomArray = json_decode($roomsJson, true);
            
            foreach($roomArray as $room) {
                
                $smx_room = new SMX_Room();
                
                if(key_exists('code', $room))
                    $smx_room->RoomTypeCode = $room['code'];
                
                if(key_exists('name', $room))
                    $smx_room->RoomType = $room['name'];
                
                if(key_exists('description', $room))
                    $smx_room->RoomTypeDescription = $room['description'];
                
                $rooms[] = $smx_room;
                
            }
            
        } catch(\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Function' => __FUNCTION__]);
            $ex = new PmsExceptions($e->getMessage(), PmsExceptions::SMX_ERR_Unable_to_process);
            $ex->setErrorType(PmsExceptions::SMX_EWT_Processing_exception);
            throw $ex;
        }
        
        return $rooms;
    }
    
    public function parseException(\Exception $e) {
        
        // if url is incorrect then html is returned in error.
        /*
         * Client error: `GET https://smx-pilot-subscriberx.dev.siteminderlabs.com/inventory/v1/publishers` 
         * resulted in a `401 Unauthorized` response: {"message":"No authorization token was found","name":"UnauthorizedError"} 
         */

        // Normal error response
        // {"message":"No authorization token was found","name":"UnauthorizedError"}
        
        if($e instanceof \Exception)
            return $e;
        
        if($e->getCode() == PMS::ERROR_UNKNOWN_ERROR) {
            return $e;
        }
        
        $code = $e->getCode();
        $content = json_decode($e->getResponse()->getBody()->getContents(), true);
        
        $message = "Something went wrong.";
        $name = "Error";
        $code = 400;
        
        if(!empty($content)) {
            
            if(key_exists('message', $content))
                $message = $content['message'];

            if(key_exists('name', $content))
                $name = $content['name'];
        } else {
            $message = $e->getMessage();
        }
        
        return new PmsExceptions($name . ": " . $message, $code, $e);
    }
    
    public function successResponseForReservationNotification(string $content, array $bookings) {
        
        try {
            
            /**
             * @var $xml SimpleXMLElement
             */
            $xml = simplexml_load_string($content);
            $xml->registerXPathNamespace('ns', $this->nameSpaceOpenTravel);
            $RS = $xml->xpath('//ns:OTA_HotelResNotifRQ')[0];

            $OTA_HotelResNotifRS = new SimpleXMLElement("<OTA_HotelResNotifRS></OTA_HotelResNotifRS>");

            $OTA_HotelResNotifRS->addAttribute('xmlns', $this->nameSpaceOpenTravel);
            $OTA_HotelResNotifRS->addAttribute('EchoToken', $RS->attributes()->EchoToken);
            $OTA_HotelResNotifRS->addAttribute('Version', $RS->attributes()->Version);
            $OTA_HotelResNotifRS->addChild('Success');

            $HotelReservations = $OTA_HotelResNotifRS->addChild('HotelReservations');

            foreach($bookings as $booking) {

                $HotelReservation = $HotelReservations->addChild('HotelReservation');
                $UniqueID = $HotelReservation->addChild('UniqueID');
                $UniqueID->addAttribute('ID', $booking->UniqueID);

                $ResGlobalInfo = $HotelReservation->addChild('ResGlobalInfo');
                $HotelReservationIDs = $ResGlobalInfo->addChild('HotelReservationIDs');
                $HotelReservationID = $HotelReservationIDs->addChild('HotelReservationID');
                $HotelReservationID->addAttribute('ResID_Type', $booking->ResID_Type);
                $HotelReservationID->addAttribute('ResID_Value', $booking->ResID_Value);

            }

            /*
             * Change OTA_HotelResNotifRQ to $OTA_HotelResNotifRS on Incoming request and send it back
             * Incoming XML request has all header and authorization credential
             * So use same request to sent it back else we have to manually set headers and credentials
             */
            $body_of_coming_xml = $xml->xpath("/soap-env:Envelope/soap-env:Body/ns:OTA_HotelResNotifRQ")[0];
            $comingXML = dom_import_simplexml($body_of_coming_xml);
            $domReplace  = dom_import_simplexml($OTA_HotelResNotifRS);
            $nodeImport  = $comingXML->ownerDocument->importNode($domReplace, TRUE);
            $comingXML->parentNode->replaceChild($nodeImport, $comingXML);

//            $xml->asXml('./xml_test.xml');
//            dd($xml);
//            header('Content-type: application/xml');
//            echo $xml->asXML();
//            die();

            return $xml->asXML();

        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__,'Function' => __FUNCTION__]);
        }
        
        return '';
        
    }

    public function errorResponseForReservationNotification(string $content, array $errors) {

        /**
         * @var $xml SimpleXMLElement
         */
        $xml = simplexml_load_string($content);
        $xml->registerXPathNamespace('ns', $this->nameSpaceOpenTravel);
        $RS = $xml->xpath('//ns:OTA_HotelResNotifRQ')[0];

        $OTA_HotelResNotifRS = new SimpleXMLElement("<OTA_HotelResNotifRS></OTA_HotelResNotifRS>");

        $OTA_HotelResNotifRS->addAttribute('xmlns', $this->nameSpaceOpenTravel);
        $OTA_HotelResNotifRS->addAttribute('EchoToken', $RS->attributes()->EchoToken);
        $OTA_HotelResNotifRS->addAttribute('Version', $RS->attributes()->Version);
        $Errors = $OTA_HotelResNotifRS->addChild('Errors');

        foreach($errors as $k => $error){
            $Error = $Errors->addChild('Error', $error['message']);
            $Error->addAttribute('Type', $error['type']); //mandatory attribute

            //add Error Codes (ERR) -- optional
            if(isset($error['code']) && !empty($error['code']))
                $Error->addAttribute('Code', $error['code']);

        }


        /*
         * Change OTA_HotelResNotifRQ to $OTA_HotelResNotifRS on Incoming request and send it back
         * Incoming XML request has all header and authorization credential
         * So use same request to sent it back else we have to manually set headers and credentials
         */
        $body_of_coming_xml = $xml->xpath("/soap-env:Envelope/soap-env:Body/ns:OTA_HotelResNotifRQ")[0];
        $comingXML = dom_import_simplexml($body_of_coming_xml);
        $domReplace  = dom_import_simplexml($OTA_HotelResNotifRS);
        $nodeImport  = $comingXML->ownerDocument->importNode($domReplace, TRUE);
        $comingXML->parentNode->replaceChild($nodeImport, $comingXML);

//            $xml->asXml('./xml_test.xml');
//            dd($xml);
//            header('Content-type: application/xml');
//            echo $xml->asXML();
//            die();

        return $xml->asXML();
    }
    
    public function mapReservation2Booking(SMX_Reservation $reservation, Booking &$booking) {
        
        /**
         * Note: BA is sending integer values as site-minder sends string values for following attributes.
         * id (pms_booking_id)
         * propertyId (pms_property_id)
         */
        $booking->id = $reservation->UniqueID;
        $booking->propertyId = $reservation->HotelCode;
        
        $booking->bookingTime = $reservation->CreateDateTime;
        $booking->bookingModifyTime = $reservation->LastModifyDateTime;
        $booking->firstNight = $reservation->Start;
        $booking->lastNight = $reservation->End;
        $booking->currencyCode = $reservation->CurrencyCode;
        
        $booking->price = $reservation->TotalAmountAfterTax;
        $booking->balancePrice = $reservation->TotalAmountAfterTax;
        
        $booking->refererOriginal = $reservation->BookingChannelCompanyName;
        
        $gComments = $reservation->getComments(true);
        $hComments = $reservation->getComments(false);
        
        $booking->hostComments = $hComments;
        $booking->guestComments = $gComments;
        $booking->notes = $hComments;
        
        $booking->numberOfAdults = $reservation->getNumberOfAdults();
        $booking->numNight = $reservation->getNumberOfNights();
        
        if(!empty($reservation->guests)) {

            /**
             * @var $guest SMX_Guest
             */
            $guest = $reservation->guests[0];
            
            $booking->guestEmail = $guest->Email;
            $booking->guestPhone = $guest->PhoneNumber;
            $booking->guestFirstName = $guest->GivenName;
            $booking->guestLastName = $guest->Surname;
            $booking->guestPostcode = $guest->PostalCode;
            $booking->guestAddress = $guest->AddressLine;
            $booking->guestCity = $guest->CityName;
            $booking->guestCountry = $guest->CountryName;
            
        }

        $booking->bookingStatusCode = 0;
        $booking->bookingStatus = 1;
        $booking->channelCode = 19; // TODO: remove or add actual code or add zero "0"
        $booking->guestArrivalTime = '';
        $booking->channelReference = $reservation->BookingChannelCompanyName;;
        $booking->bookingReferer = $reservation->BookingChannelCompanyName;;
        $booking->unitId = 0;
        $booking->roomId = '';
        $booking->guestTitle = '';
        $booking->guestMobile = '';
        $booking->guestFax = '';
        $booking->invoiceNumber = '';
        $booking->invoiceDate = '';
        $booking->apiMessage = '';
        $booking->flagColor = '';
        $booking->flagText = '';
        $booking->bookingIp = '';
        $booking->message = '';
        
    }
    
    public function mapHotel2Property(SMX_Hotel $hotel, Property &$property) {

        $property->id = $hotel->getCode();
        $property->propertyName = $hotel->getName();
        $property->propertyKey = 'dummy-key';
        $property->currencyCode = $hotel->getCurrency();
        $property->caNotifyURL = '';
        $property->ownerId = '';
        $property->rooms = $hotel->getRooms();
        $property->longitude = '';
        $property->latitude = '';
        $property->city = '';
        $property->country ='';
        $property->address = '';
        
    }
     
}