<?php

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 9/27/18
 * Time: 4:59 PM
 */

namespace App\System\PMS\BookingAutomation;

use App\System\PaymentGateway\Models\Card;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\Account;
use App\System\PMS\Models\Booking;
use App\System\PMS\Models\BookingCard;
use App\System\PMS\Models\InvoiceItem;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\System\PMS\PmsInterface;
use App\UserAccount;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class BookingAutomation implements PmsInterface {

    /**
     * @var BAClient
     */
    private $client;

    /**
     * @var BAParser
     */
    private $bAParser;

    private $pmsName = 'BookingAutomation';

    private $actualResponse = null;

    public function __construct(BAClient $client, BAParser $bAParser) {
        $this->client = $client;
        $this->bAParser = $bAParser;
    }

    /**
     * Retrieve User Properties with JSON type request
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     *
     * @throws PmsExceptions
     */
    public function fetch_properties(UserAccount $user, PmsOptions $options) {

        $api = '/getProperties';

        if($options->requestType === PmsOptions::REQUEST_TYPE_JSON) {

            $content = $this->client->postJSON($user, $options, $api);
            $this->actualResponse = $content;
            if(key_exists('getProperties', $content))
                return $this->bAParser->parseJsonResponse($this->pmsName, Property::class, $content['getProperties']);
            else
                throw new PmsExceptions('getProperties key not found for JSON Request');


        } elseif($options->requestType === PmsOptions::REQUEST_TYPE_XML) {

            $content = $this->client->postXML($user, $options, $api);
            $this->actualResponse = $content;

            if(count($properties = $content->xpath('/properties/property')) > 0)
                return $this->bAParser->parseXmlResponse($this->pmsName, Property::class, $properties);
            else
                return []; //throw new PmsExceptions('Properties not found');

        } else {
            throw new PmsExceptions('Request Type not defined for fetch Properties');
        }


    }

    /**
     * Retrieve User Properties with JSON type request
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     *
     * @throws PmsExceptions
     */
    public function fetch_properties_json_xml(UserAccount $user, PmsOptions $options) {

        $options->requestType = PmsOptions::REQUEST_TYPE_JSON;
        $jsonResponse = $this->fetch_properties($user, $options);

        $options->requestType = PmsOptions::REQUEST_TYPE_XML;
        $xmlResponse = $this->fetch_properties($user, $options);

        if($xmlResponse == null || $jsonResponse == null)
            return [];

        $xmlCount = count($xmlResponse);
        $jsonCount = count($jsonResponse);

        if($xmlCount > 0 && $jsonCount > 0 && $jsonCount == $xmlCount) {

//            for($x = 0; $x < $xmlCount; $x++) {
//                /**
//                 * @var $xProp Property
//                 */
//                $xProp = $xmlResponse[$x];
//                for($j = 0; $j < $jsonCount; $j++) {
//                    /**
//                     * @var $jProp Property
//                     */
//                    $jProp = $jsonResponse[$j];
//                    if($xProp->id == $jProp->id) {
//                        $xProp->propertyKey = $jProp->propertyKey;
//                        $xmlResponse[$x] = $xProp;
//                    }
//                }
//            }
//
//            // This response is merged/mutated
//            return $xmlResponse;

            for($j = 0; $j < $jsonCount; $j++) {
                $jProp = $jsonResponse[$j];
                for($x = 0; $x < $xmlCount; $x++) {
                    $xProp = $xmlResponse[$x];
                    if($xProp->id == $jProp->id) {
                        $jProp->caNotifyURL = $xProp->caNotifyURL;
                        $jsonResponse[$j] = $jProp;
                    }
                }
            }

            // This response is merged/mutated
            return $jsonResponse;

        }

        return [];

    }

    /**
     * Retrieve User's single Property
     * Set PropertyKey and Property ID
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     * @throws PmsExceptions
     */
    public function fetch_property(UserAccount $user, PmsOptions $options) {

        $api = '/getProperty';
        $content = $this->client->postJSON($user, $options, $api);
        $this->actualResponse = $content;
        if(key_exists('getProperty', $content))
            return $this->bAParser->parseJsonResponse($this->pmsName, Property::class, $content['getProperty']);
        else
            throw new PmsExceptions('getProperty key not found for JSON Request');

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     *
     * Retrieve user bookings with XML response
     */
    public function fetch_Booking_Details_json_xml(UserAccount $user, PmsOptions $options) {

        $api = '/getBookings';
        $jsonBookingsArray = array();
        $xmlBookingsArray = array();


        /**
         * Fetching Booking with JSON API
         */
        $options->requestType = PmsOptions::REQUEST_TYPE_JSON;
        $content = $this->client->postJSON($user, $options, $api);
        $this->actualResponse = $content;
        $jsonBookingsArray = $this->bAParser->parseJsonResponse($this->pmsName, Booking::class, $content);
        /**
         * @var $_b Booking
         */
        foreach ($jsonBookingsArray as $_b) {
            $_b->adjustIfTestBooking(); // can be removed safely
            $_b->adjustCheckoutDate(); // consider carefully before removing
        }

        /**
         * Fetching Booking with XML API
         */
        $options->propertyID = null;
        $options->requestType = PmsOptions::REQUEST_TYPE_XML;
        $content = $this->client->postXML($user, $options, $api);
        $this->actualResponse = $content;
        if(count($bookings = $content->xpath('/bookings/booking')) > 0) {
            $xmlBookingsArray = $this->bAParser->parseXmlResponse($this->pmsName, Booking::class, $bookings);
            /**
             * @var $_b Booking
             */
            foreach ($xmlBookingsArray as $_b) {
                $_b->adjustIfTestBooking(); // can be removed safely
                $_b->adjustCheckoutDate(); // consider carefully before removing
                $_b->adjustBookingStatusForXmlTextToInteger(); // consider carefully before removing
            }

        }

        /**
         * This check is added because before adding following merge code, we are considering booking-fetch as failed if
         * any one request failed or returned no booking.
         */
        $xmlCount = count($xmlBookingsArray);
        $jsonCount = count($jsonBookingsArray);
        if($xmlCount == 0 || $jsonCount == 0) {
//            Log::error("Booking not Found.",
//                [
//                    'user_account_id' => $user->id,
//                    'name' => $user->name,
//                    'xmlBookingCount' => $xmlCount,
//                    'jsonBookingCount' => $jsonCount,
//                    'pms_options' => json_decode(json_encode($options), true)
//                ]);
            return array();
        }

        /**
         * Combining json and xml booking requests
         */
        for($j = 0; $j < $jsonCount; $j++) {
            for($x = 0; $x < $xmlCount; $x++) {
                if($jsonBookingsArray[$j]->id == $xmlBookingsArray[$x]->id) {
                    $xmlBookingsArray[$x]->apiMessage = $jsonBookingsArray[$j]->apiMessage;
                    $xmlBookingsArray[$x]->message = $jsonBookingsArray[$j]->message;
                    $xmlBookingsArray[$x]->infoItems = $jsonBookingsArray[$j]->infoItems;
                    $xmlBookingsArray[$x]->currencyCode = $jsonBookingsArray[$j]->currencyCode;

                    /**
                     * Calculating Balance price
                     */
                    $balance = $xmlBookingsArray[$x]->balancePrice;
                    $invoiceItems = $xmlBookingsArray[$x]->invoice;

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
                            $balance = $xmlBookingsArray[$x]->balancePrice;

                    }

                    $xmlBookingsArray[$x]->balancePrice = $balance;

                    continue;
                }
            }
        }

        // Combined result of xml and json api
        return $xmlBookingsArray;

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     *
     * Retrieve user bookings with XML response
     */
    public function fetch_Booking_Details(UserAccount $user, PmsOptions $options) {

        $api = '/getBookings';

        if($options->requestType === PmsOptions::REQUEST_TYPE_JSON) {

            $content = $this->client->postJSON($user, $options, $api);
            $this->actualResponse = $content;
            $parsedBookingsArray = $this->bAParser->parseJsonResponse($this->pmsName, Booking::class, $content);
            /**
             * @var $_b Booking
             */
            foreach ($parsedBookingsArray as $_b) {
                $_b->adjustIfTestBooking(); // can be removed safely
                $_b->adjustCheckoutDate(); // consider carefully before removing
            }

            return $parsedBookingsArray;

        } elseif($options->requestType === PmsOptions::REQUEST_TYPE_XML) {

            $content = $this->client->postXML($user, $options, $api);
            $this->actualResponse = $content;
            if(count($bookings = $content->xpath('/bookings/booking')) > 0) {
                $parsedBookingsArray = $this->bAParser->parseXmlResponse($this->pmsName, Booking::class, $bookings);
                /**
                 * @var $_b Booking
                 */
                foreach ($parsedBookingsArray as $_b) {
                    $_b->adjustIfTestBooking(); // can be removed safely
                    $_b->adjustCheckoutDate(); // consider carefully before removing
                    $_b->adjustBookingStatusForXmlTextToInteger(); // consider carefully before removing
                }

                return $parsedBookingsArray;
            }
            else
                return array();

        } else {
            throw new PmsExceptions('Request Type not defined for fetch Booking Details');
        }

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param Booking $bookingToUpdateData
     * @return boolean|string
     * @throws PmsExceptions
     */
    public function update_booking(UserAccount $user, PmsOptions $options, Booking $bookingToUpdateData) {

        if($options->requestType === PmsOptions::REQUEST_TYPE_JSON) {

            $api = '/setBooking';
            return $this->client->postJSON_withData($user, $options, $api, $bookingToUpdateData, Booking::class, $this->pmsName);

        } elseif($options->requestType === PmsOptions::REQUEST_TYPE_XML) {

            $api = '/putBookings';
            return $this->client->postXML_withData($user, $options, $api, $bookingToUpdateData, Booking::class, $this->pmsName, 'bookings', 'booking');

        } else {
            throw new PmsExceptions('Request Type not defined for Update Booking Details');
        }
    }

    protected function spitXMLAndDie(SimpleXMLElement $xml) {
        header('Content-type: text/xml');
        echo $xml->asXML();
        die();
    }

    protected function spitJsonAndDie(array $json, $decodeToJSON = false) {
        if($decodeToJSON) {
            header('Content-type: application/json');
            echo json_encode($json);
        } else {
            echo "<pre>";
            print_r($json);
        }
        die();
    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return mixed
     * @throws PmsExceptions
     */
    public function fetch_user_account(UserAccount $user, PmsOptions $options) {

        $api = '/getAccount';

        if($options->requestType === PmsOptions::REQUEST_TYPE_JSON) {

            $content = $this->client->postJSON($user, $options, $api);
            $this->actualResponse = $content;
            return $this->bAParser->parseJsonResponse($this->pmsName, Account::class, $content);

        } elseif($options->requestType === PmsOptions::REQUEST_TYPE_XML) {

            $content = $this->client->postXML($user, $options, $api);
            $this->actualResponse = $content;
            return $this->bAParser->parseXmlResponse($this->pmsName, Account::class, array($content));

        } else {
            throw new PmsExceptions('Request Type not defined for fetch Properties');
        }

    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param array $propertiesToUpdateData Array of Property Objects which needs to be updated
     * @return bool
     * @throws PmsExceptions
     */
    public function update_properties(UserAccount $user, PmsOptions $options, array $propertiesToUpdateData) {

        if($propertiesToUpdateData == null)
            throw new PmsExceptions("Properties array can't be null");

        if(count($propertiesToUpdateData) > 1 && $options->requestType === PmsOptions::REQUEST_TYPE_JSON)
            throw new PmsExceptions('You cannot update multiple Properties with JSON request');

        if($options->requestType === PmsOptions::REQUEST_TYPE_JSON) {

            $api = '/modifyProperty';
            return $this->client->postJSON_withData($user, $options, $api, $propertiesToUpdateData[0], Property::class, $this->pmsName, array('modifyProperty'=>'array'));

        } elseif($options->requestType === PmsOptions::REQUEST_TYPE_XML) {

            $api = '/putProperties';
            return $this->client->postXML_withArrayData($user, $options, $api, $propertiesToUpdateData, Property::class, $this->pmsName, 'properties', 'property');

        } else {
            throw new PmsExceptions('Request Type not defined for Update Booking Details');
        }
    }

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array
     * @throws PmsExceptions
     */
    public function fetch_card_for_booking(UserAccount $user, PmsOptions $options) {

        if($options->bookingID == null)
            throw new PmsExceptions('Missing Booking Id for card Fetching');

        if($options->bookingToken == null)
            throw new PmsExceptions('Token missing for card Fetching');

        if($options->propertyKey == null)
            throw new PmsExceptions('Missing Property key for Booking ID: ' . $options->bookingID . ' to Fetch card');


        $api = '/getCard';
        $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

        $content = $this->client->postJSON($user, $options, $api);
        $this->actualResponse = $content;
        return $this->bAParser->parseJsonResponse($this->pmsName, BookingCard::class, $content);

    }

    /**
     * Returns response of api in form of string, json or xml
     * @return null|string
     */
    function getActualResponse() {
        return $this->actualResponse;
    }
}
