<?php

namespace App\Http\Controllers\admin;


use App\BookingSourceForm;
use App\Http\Controllers\Controller;
use App\PropertyInfo;
use App\RoomInfo;
use App\System\PMS\BookingAutomation\NotificationUrlBookingAutomation;
use App\System\PMS\BookingSources\BS_Agoda;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\BookingSources\BS_CTrip;
use App\System\PMS\BookingSources\BS_Expedia;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\System\PMS\PMS;
use App\UserAccount;
use App\UserPms;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Faker\Factory;

class CreateTestBooking extends Controller {

    public $bookResponse = array();

    public function __construct() {
        $this->middleware('auth');
        $this->bookResponse = array();
    }

    public function index() {

        $faker = Factory::create();

        $userAccounts = UserAccount::where('account_type', 1)->get()->toArray();

        $_emails = DB::select('select email from users group by email');
        $_emails2 = DB::select('select guest_email from booking_infos group by guest_email');

        $emails = [];
        for($i = 0; $i < count($_emails); $i++)
            $emails[] = $_emails[$i]->email;

        for($i = 0; $i < count($_emails2); $i++)
            $emails[] = $_emails2[$i]->guest_email;

        $bs_all = BookingSourceForm::all(['channel_code', 'name'])->toArray();
        $bs_airBnb = null;
        $bs_for_front_end = [];
        for($i = 0; $i < count($bs_all); $i++) {
            if ($bs_all[$i]['name'] == 'Airbnb') {
                $bs_airBnb = $bs_all[$i];
                continue;
            }
            $bs_all[$i]['name'] = $bs_all[$i]['name'] . ' - ' . $bs_all[$i]['channel_code'];
            $bs_for_front_end[] = $bs_all[$i];
        }

        if($bs_airBnb !== null) {
            $bs_airBnb['channel_code'] = 10;
            $bs_airBnb['name'] = 'Airbnb - 10';
            $bs_for_front_end[] = $bs_airBnb;

            $bs_airBnb['channel_code'] = 46;
            $bs_airBnb['name'] = 'Airbnb - 46';
            $bs_for_front_end[] = $bs_airBnb;
        }


        return view('admin.pages.create_test_bookings', [
            'bookingResponse' => $this->bookResponse,
            'userAccounts'=>$userAccounts,
            'gTitle'=>$faker->title,
            'fName'=>$faker->firstName,
            'lName'=>$faker->lastName,
            'emails'=>$emails,
            'postalCode'=>$faker->postcode,
            'address'=>$faker->address,
            'city'=>$faker->city,
            'country'=>$faker->country,
            'mobile'=>$faker->phoneNumber,
            'price'=>$faker->numberBetween(500, 4000),
            'bookingSources' => $bs_for_front_end

        ]);
    }

    public function getUserProperties($user_account_id)
    {
        $properties = PropertyInfo::where('available_on_pms', 1)->where('status', 1)->where('user_account_id', $user_account_id)->select('id', 'name')->get();

        if (!empty($properties)) {
            return $this->apiSuccessResponse(200, $properties->toArray(), 'success');
        } else {
            return $this->apiErrorResponse('Data not found', 400);
        }
    }

    public function getPropertyRooms($property_info_id)
    {
        $rooms = RoomInfo::where('available_on_pms', 1)->where('property_info_id', $property_info_id)->select('id', 'name')->get();
        if (!empty($rooms)) {
            return $this->apiSuccessResponse(200, $rooms->toArray(), 'success');
        } else {
            return $this->apiErrorResponse('Data not found', 400);
        }
    }
    public function store(Request $request) {

        $cin = Carbon::parse($request->get("check-in-date"));
        $cout = Carbon::parse($request->get("check-out-date"));
        $apiMessage = '';

        $userAccount = UserAccount::where('id', $request->get("select-account"))->first();
        $property = PropertyInfo::where('id', $request->get("select-property"))->first();
        $userPms = UserPms::where('user_account_id', $request->get("select-account"))->first();

        try {
            $this->updateNotifyURL($userAccount, $property, $request);
        } catch (PmsExceptions $e) {
            dd($e->getMessage());
        }

        $credentials = json_decode($userPms->form_data, true)['credentials'];
        $user = '';
        $pass = '';

        for($i = 0; $i < count($credentials); $i++) {
            if($credentials[$i]['name'] == 'api-key')
                $pass = $credentials[$i]['value'];
            elseif($credentials[$i]['name'] == 'username')
                $user = $credentials[$i]['value'];
        }

        $cardExpiry = $request->get("card-expiry");
        $infoItems = array();
        $cardType = $request->get('card-type');
        switch ($request->get("select-booking-source")){
            case 14: // Expedia
                switch ($cardType) {
                    case 'CC':
                        $infoItems[] = array('code'=>BS_Expedia::CODE_HOTELCOLLECT, 'text'=>'text not used here');
                        if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                            $cardExpiry2 = explode('/', $request->get("card-expiry"));
                            $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                        }
                        break;
                    case 'VC':
                        $infoItems[] = array('code'=>BS_Expedia::CODE_EXPEDIACOLLECT, 'text'=>'Virtual Card');
                        if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                            $cardExpiry2 = explode('/', $request->get("card-expiry"));
                            $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                        }
                        break;
                    case 'BT':
                        if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                            $cardExpiry2 = explode('/', $request->get("card-expiry"));
                            $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                        }
                        break;
                }
                break;
            case 17: // Agoda
                switch ($cardType) {
                    case 'CC':
                        break;
                    case 'VC':
                        $infoItems[] = array('code'=>BS_Agoda::CODE_AGODACOLLECT, 'text'=>'Virtual Card');
                        if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                            $cardExpiry2 = explode('/', $request->get("card-expiry"));
                            $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                        }
                        break;
                    case 'BT':
                        break;
                }
                break;
            case 19: // Booking.com
                if($request->get("non-refundable") == 1){
                    $apiMessage = "Deposit Policy: The total price of the reservation may be charged anytime after booking. Cancellation Policy: Please note, if cancelled, modified or in case of no-show, the total price of the reservation will be charged.";
                }
                switch ($cardType) {
                    case 'CC':
                        break;
                    case 'VC':
                        $infoItems[] = array('code'=>BS_BookingCom::CODE_BOOKINGCOMVIRTCARD, 'text'=>'text not used here');
                        break;
                    case 'BT':
                        $infoItems[] = array('code'=>BS_BookingCom::CODE_BOOKINGCOMBANKTRANS, 'text'=>'text not used here');
                        if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                            $cardExpiry2 = explode('/', $request->get("card-expiry"));
                            $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                        }
                        break;
                }
                break;
            case 53: // CTrip
                switch ($cardType) {
                    case 'CC':
                    case 'VC':
                    case 'BT':
                        $infoItems[] = array('code'=>BS_CTrip::CODE_CTRIPCOLLECT, 'text'=>'text not used here');
                        break;
                }
                if($request->get("card-expiry") != '' && $request->get("card-expiry") != null) {
                    $cardExpiry2 = explode('/', $request->get("card-expiry"));
                    $cardExpiry = $cardExpiry2[0] . '' . $cardExpiry2[1];
                }
                break;
            case 10:
            case 46:
                switch ($cardType) {
                    case 'CC':
                    case 'VC':
                    case 'BT':
                        $infoItems[] = array();
                        $cardExpiry = '';
                        break;
                }
                break;
        }

        try {


            $body = array(
                "authentication" => array (
                    "apiKey" => $pass,
                    "propKey" => $property->property_key
                )
            );

            $body_part = [
                "status" => "1",
                "firstNight" => $request->get("check-in-date"),
                "lastNight" => $request->get("check-out-date"),
                "apiSource" => $request->get("select-booking-source"),
                "numAdult" => "2",
                "numChild" => "0",
                "guestTitle" => $request->get("guest-title"),
                "guestFirstName" => $request->get("guest-first-name"),
                "guestName" => $request->get("guest-last-name"),
                "guestEmail" => $request->get("guest-email"),
                "guestPhone" => $request->get("guest-mobile"),
                "guestMobile" => $request->get("guest-mobile"),
                "guestAddress" => $request->get("guest-address"),
                "guestCity" => $request->get("guest-city"),
                "guestPostcode" => $request->get("guest-postal-code"),
                "guestCountry" => $request->get("guest-country"),
                "guestComments" => $request->get("guest-comments"),
                "notes" => "VIP",
                "flagColor" => "ff0000",
                "flagText" => "Show booking in red",
                "price" => $request->get("total-price"),
                "deposit" => "0.00",
                "tax" => "0.00",
                "commission" => "0.00",
                "refererEditable" => "ch:".$request->get("select-booking-source"),
                "referer" => "ch:".$request->get("select-booking-source"),
                "bookingReferer" => "ch:".$request->get("select-booking-source"),
                "notifyUrl" => "true",
                "notifyGuest" => false,
                "notifyHost" => false,
                "assignBooking" => false,
                "deleteInvoice" => false,
                "apiMessage" => $apiMessage,
                "message" => $apiMessage,
                "invoice" => array(
                    array(
                        "description" => "Total Price",
                        "status" => "1",
                        "qty" => "1",
                        "price" => $request->get("total-price"),
                        "vatRate" => "0",
                        "type" => "199",
                    )
                )
            ];

            $body_part_card = [
                "guestCardNumber" => $request->get("guest-card"),
                "guestCardName" => $request->get("guest-first-name").' '. $request->get("guest-last-name"),
                "guestCardExpiry" => $cardExpiry,
                "guestCardCVV" => $request->get("card-cvv"),
            ];

            if($request->get('booking-type', 'single') == 'single') {
                $room = RoomInfo::where('id', $request->get("select-room"))->first();
                $body_part["roomId"] = $room->pms_room_id;
                $body = array_merge($body, $body_part);
                $body = array_merge($body, $body_part_card);
                $body["infoItems"] = $infoItems;

            } elseif($request->get('booking-type', 'single') == 'group') {

                $roomGroup = $request->get('roomGroup');
                $roomArray = [];

                for($i = 0; $i < count($roomGroup); $i++) {

                    $room = RoomInfo::where('id', $roomGroup[$i])->first();
                    $rJson = $body_part;
                    $rJson["roomId"] = $room->pms_room_id;

                    // Just to simulate that only master will have card.
                    if($i == 0) {
                        $rJson = array_merge($rJson, $body_part_card);
                        $rJson["infoItems"] = $infoItems;
                    }

                    $roomArray[] = $rJson;
                }
                $body["groupArray"] = $roomArray;
            }

            // Log::debug('Create Test Booking', ['json'=>json_encode($body)]);

            $url = 'https://api.beds24.com/json/setBooking';
            $client = new Client();
            $response = $client->post($url, [RequestOptions::JSON => $body]);
            $content = $response->getBody()->getContents();
            $content = json_decode($content, true);

            if(is_array($content) && count($content) > 0 && $request->get('booking-type', 'single') == 'group') {
                $this->bookResponse = array();
                $msg = '';
                $bookId = '';
                $error = false;
                $length = count($content);
                for($i = 0; $i < $length; $i++) {
                    if(key_exists('success', $content[$i]) && key_exists('bookId', $content[$i])) {
                        $comma = ($i < ($length - 1)) ? ", " : "";
                        $msg .= $content[$i]['success'] . $comma;
                        $bookId .= $content[$i]['bookId'] . $comma;
                    } else {
                        $error = true;
                    }
                }
                if(!$error) {
                    $this->bookResponse['message'] = $msg;
                    $this->bookResponse['bookingId'] = $bookId;
                    $this->bookResponse['status'] = "1";
                } else {
                    $this->bookResponse['status'] = "0";
                    $this->bookResponse['error'] = json_encode($content);
                }

            } elseif(key_exists('success', $content) && key_exists('bookId', $content)) {
                $this->bookResponse = array();
                $msg = $content['success'];
                $bookId = $content['bookId'];
                $this->bookResponse['message'] = $msg;
                $this->bookResponse['bookingId'] = $bookId;
                $this->bookResponse['status'] = "1";
            } else {
                $this->bookResponse['status'] = "0";
                $this->bookResponse['error'] = json_encode($content);
            }

        } catch (\Exception $e) {
            dd(["Message"=>$e->getMessage(), "StackTrace"=>$e->getTrace()]);
        }

        return $this->index();
    }

    public function store2(Request $request) {

        $cin = Carbon::parse($request->get("check-in-date"));
        $cout = Carbon::parse($request->get("check-out-date"));

        $userAccount = UserAccount::where('id', $request->get("select-account"))->first();
        $property = PropertyInfo::where('id', $request->get("select-property"))->first();
        $room = RoomInfo::where('id', $request->get("select-room"))->first();
        $userPms = UserPms::where('user_account_id', $request->get("select-account"))->first();

        try {
            $this->updateNotifyURL($userAccount, $property, $request);
        } catch (PmsExceptions $e) {
            dd($e->getMessage());
        }

        $credentials = json_decode($userPms->form_data, true)['credentials'];
        $user = '';
        $pass = '';

        for($i = 0; $i < count($credentials); $i++) {
            if($credentials[$i]['name'] == 'api-key')
                $pass = $credentials[$i]['value'];
            elseif($credentials[$i]['name'] == 'username')
                $user = $credentials[$i]['value'];
        }

        $body = '<request>
<auth><username>' . $user . '</username><password>' . $pass . '</password></auth>
<bookings propid="' . $property->pms_property_id . '" action="new">
    <booking action="new">
        <propId>' . $property->pms_property_id . '</propId>
        <status>Confirmed</status>
        <firstNight>' . $request->get("check-in-date") . '</firstNight>
        <lastNight>' . $request->get("check-out-date") . '</lastNight>
        <roomId>' . $room->pms_room_id . '</roomId>
        <numNight>'.$cout->diff($cin)->days.'</numNight>
        <numAdult>2</numAdult>
        <numChild>0</numChild>
        <rateDesc>2019-0</rateDesc>
        <price>'.$request->get("total-price").'</price>
        <deposit>0.00</deposit>
        <tax>0.00</tax>
        <commission>26.41</commission>
        <guestTitle>'.$request->get("guest-title").'</guestTitle>
        <guestFirstName>'.$request->get("guest-first-name").'</guestFirstName>
        <guestName>'.$request->get("guest-last-name").'</guestName>
        <guestEmail>'.$request->get("guest-email").'</guestEmail>
        <guestPhone>'.$request->get("guest-mobile").'</guestPhone>
        <guestAddress>'.$request->get("guest-address").'</guestAddress>
        <guestCity>'.$request->get("guest-city").'</guestCity>
        <guestCountry>'.$request->get("guest-country").'</guestCountry>
        <guestPostcode>'.$request->get("guest-postal-code").'</guestPostcode>
        <guestComments>'.$request->get("guest-comments").'</guestComments>
        <bookingReferer>ch:'.$request->get("select-booking-source").'</bookingReferer>
        <originalReferer>ch:'.$request->get("select-booking-source").'</originalReferer>
        <reference>ch:'.$request->get("select-booking-source").'</reference>
        <flagColor>7fff00</flagColor>
        <flagText>InvoiceDone</flagText>
        <statusCode>0</statusCode>
        <channelCode>'.$request->get("select-booking-source").'</channelCode>
        <cardName>'.$request->get("guest-first-name").' '. $request->get("guest-last-name") .'</cardName>
        <cardNumber>'.$request->get("guest-card").'</cardNumber>
        <cardExpire>'.$request->get("card-expiry").'</cardExpire>
        <cardCvv>'.$request->get("card-cvv").'</cardCvv>
        <invoice>
            <item  type="200" action="new">
                <description>Price</description>
                <status/>
                <quantity>1</quantity>
                <itemPrice>'.$request->get("total-price").'</itemPrice>
                <vatRate>0.00</vatRate>
            </item>
            <currency/>
            <balance>0.00</balance>
        </invoice>
        <infoItems>
        <item action="new">
        <code>code</code>
        <text>text</text>
        </item>
        </infoItems>
    </booking>
</bookings>
</request>';

        try {

            $url = 'https://api.beds24.com/xml/putBookings';
            $client = new Client();
            $options = ['headers' => ['Content-Type' => 'application/xml; charset=UTF8',], 'body' => $body,];
            $client->post($url, $options);

        } catch (\Exception $e) {
            dd(["Message"=>$e->getMessage(), "StackTrace"=>$e->getTrace()]);
        }

        return $this->index();
    }

    private function getNotifyURL($bookingSource, $userAccountId) {
        $urlGen = new NotificationUrlBookingAutomation();
        $urlGen->enableChannelCode(true, $bookingSource);
        $urlGen->enablePropertyId(true);
//        $urlGen->enableToken(true);
        $urlGen->enableMetaUserAccountId(true, $userAccountId);
        $urlGen->enableGroupId(true);
        return $urlGen->generateURL();
    }

    /**
     * @param UserAccount $userAccount
     * @param PropertyInfo $property
     * @param Request $request
     * @throws PmsExceptions
     */
    private function updateNotifyURL(UserAccount $userAccount, PropertyInfo $property, Request $request) {
        $pms = new PMS($userAccount);
        $pmsOptions = new PmsOptions();
        $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
        $pmsOptions->propertyKey = $property->property_key;
        $pmsOptions->propertyID = $property->pms_property_id;
        $prop = new Property();
        $prop->id = $property->pms_property_id;
        $prop->action = 'modify';
        $prop->caNotifyURL = $this->getNotifyURL($request->get("select-booking-source"), $request->get("select-account"));
        $pms->update_properties($pmsOptions, [$prop]);
    }

}
