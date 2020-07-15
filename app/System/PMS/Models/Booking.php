<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/5/18
 * Time: 4:17 PM
 */

namespace App\System\PMS\Models;


use App\BookingInfo;
use App\System\PMS\BookingSources\BS_Agoda;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\BookingSources\BS_CTrip;
use App\System\PMS\BookingSources\BS_Direct;
use App\System\PMS\BookingSources\BS_Expedia;
use App\System\PMS\BookingSources\BS_Generic;
use App\System\PMS\BookingSources\BS_HomeawayICal;
use App\System\PMS\BookingSources\BS_HomeawayXML;
use Carbon\Carbon;
use function GuzzleHttp\default_ca_bundle;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Log;

class Booking extends UtilClass {

    /* json_sub_with_keys:
     * This rule is to ignore keys and parse objects.
     * "group": {
     *     "16712438": {
     *       "bookId": "16712438"
     *     }
     *   }
     *
     * Key (apiKey) => Class variable
     *
     * e.g.
     * 'apiReference' => 'channelReference',
     * 'channelRef' => 'channelReference,
     */

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'bookId' => 'id',
                'roomId' => 'roomId',
                'unitId'=> 'unitId',
                'status' => 'bookingStatus',
                'firstNight' => 'firstNight',
                'lastNight' => 'lastNight',
                'guestTitle' => 'guestTitle',
                'guestFirstName' => 'guestFirstName',
                'guestName' => 'guestLastName',
                'guestEmail' => 'guestEmail',
                'guestPhone' => 'guestPhone',
                'guestMobile' => 'guestMobile',
                'guestArrivalTime'=>'guestArrivalTime',
                'guestFax' => 'guestFax',
                'guestAddress' => 'guestAddress',
                'guestCity' => 'guestCity',
                'guestPostcode' => 'guestPostcode',
                'guestCountry' => 'guestCountry',
                'notes' => 'notes',
                'flagColor' => 'flagColor',
                'flagText' => 'flagText',
                'statusCode' => 'bookingStatusCode',
                'price' => 'price',
                'currency' => 'currencyCode',
                'referer' => 'bookingReferer',
                'apiSource' => 'channelCode',
                'refererEditable' => 'refererOriginal',
                'bookingTime' => 'bookingTime',
                'modified' => 'bookingModifyTime',
                'invoiceDate' => 'invoiceDate',
                'invoiceNumber' => 'invoiceNumber',
                'guestComments' => 'guestComments',
                'apiMessage' => 'apiMessage',
                'message' => 'message',
                'masterId' => 'masterId',
                'numAdult' => 'numberOfAdults',
                'apiReference' => 'channelReference'
            ),
            'json_sub'=>array(
                'invoice' => array(
                    'var' => 'invoice',
                    'type' => InvoiceItem::class
                ),
                'infoItems' => array(
                    'var' => 'infoItems',
                    'type' => InfoItems::class
                )
            ),
            'json_sub_with_keys' => array(
                'group' => array(
                    'var' => 'groupBookings',
                    'type' => GroupBooking::class
                )
            ),
            'xmlAttributes' => array(
                'id' => 'id',
                'action' => 'action'
            ),
            'xml' => array(
                'propId' => 'propertyId',
                'roomId' => 'roomId',
                'unitId'=> 'unitId',
                'status' => 'bookingStatus',
                'firstNight' => 'firstNight',
                'lastNight' => 'lastNight',
                'price' => 'price',
                'guestTitle' => 'guestTitle',
                'guestFirstName' => 'guestFirstName',
                'guestName' => 'guestLastName',
                'guestEmail' => 'guestEmail',
                'guestPhone' => 'guestPhone',
                'guestMobile' => 'guestMobile',
                'guestFax' => 'guestFax',
                'guestAddress' => 'guestAddress',
                'guestCity' => 'guestCity',
                'guestPostcode' => 'guestPostcode',
                'guestCountry' => 'guestCountry',
                'bookingIp' => 'bookingIp',
                'bookingTime' => 'bookingTime',
                'modifiedTime' => 'bookingModifyTime',
                'bookingReferer' => 'bookingReferer',
                'originalReferer' => 'refererOriginal',
                'flagColor' => 'flagColor',
                'flagText' => 'flagText',
                'statusCode' => 'bookingStatusCode',
                'channelCode' => 'channelCode',
                'cardType' => 'cardType',
                'cardName' => 'cardName',
                'cardNumber' => 'cardNumber',
                'cardExpire' => 'cardExpire',
                'cardCvv' => 'cardCvv',
                'hostComments' => 'hostComments',
                'guestComments' => 'guestComments',
                'numNight' => 'numNight',
                'masterId' => 'masterId',
                'numAdult' => 'numberOfAdults',
                'channelRef' => 'channelReference'
            ),
            'xml_sub' => array(
                'invoice' => array(
                    'var' => 'invoice',
                    'type' => InvoiceItem::class,
                    'child' => 'item',
                    'single_elements' => array(
                        'currency' => 'currencyCode',
                        'balance' => 'balancePrice'
                    )
                ),
                'group' => array(
                    'var' => 'groupBookings',
                    'type' => GroupBooking::class,
                    'child' => 'booking',
                )
            )
        )
    );

    /**
     * @var $id int BookingInfo's id, Primary Key
     */
    public $id = null;
    public $roomId = null;
    public $unitId = null;
    public $bookingStatus = null;
    public $firstNight = null;
    /**
     * Last Night parameter is being used as checkout date, as we are adding 1 day in it at parsing/fetching time.
     * @var null
     */
    public $lastNight = null;
    public $guestTitle = null;
    public $guestFirstName = null;
    public $guestLastName = null;
    public $guestEmail = null;
    public $guestPhone = null;
    public $guestMobile = null;
    public $guestFax = null;
    public $guestAddress = null;
    public $guestCity = null;
    public $guestPostcode = null;
    public $guestCountry = null;
    public $notes = null;
    public $flagColor = null;
    public $flagText = null;
    public $bookingStatusCode = null;
    public $price = null;
    public $currencyCode = null;
    public $bookingReferer = null;
    public $refererOriginal = null;
    public $bookingTime = null;
    public $bookingModifyTime = null;
    public $guestComments = null;
    public $guestArrivalTime = null;
    public $numNight = null;
    /**
     * @var array|null
     */
    public $invoice = null;
    public $invoiceNumber = null;
    public $invoiceDate = null;
    public $apiMessage = null;
    public $message = null;
    public $masterId = null;
    public $numberOfAdults = null;
    public $channelReference = null;

    /**
     * Array of GroupBooking
     * @var array
     */
    public $groupBookings = [];



    /*
     * Only in XML
     */
    public $propertyId = null;
    public $bookingIp = null;
    public $channelCode = null;
    public $action = null;
    public $balancePrice = null;
    public $cardType = null;
    public $cardName = null;
    public $cardNumber = null;
    public $cardExpire = null;
    public $cardCvv = null;
    public $hostComments = null;

    /*
     * Only in JSON
     */

    /**
     * @var array of InfoItems
     */
    public $infoItems;

    public function getCardFirstName() {
        if($this->cardName != null && $this->cardName != '') {
            $split = explode(' ', $this->cardName);
            if(count($split) > 1)
                return $split[0];
        }
        return $this->guestFirstName;
    }

    public function getCardLastName() {
        if($this->cardName != null && $this->cardName != '') {
            $split = explode(' ', $this->cardName);
            if(count($split) > 1) {
                $last = '';
                for($i = 1; $i < count($split); $i++)
                    $last .= ' ' . $split[$i];
                return trim($last);
            }
        }
        return $this->guestLastName;
    }

    /**
     * @return array
     */
    public function getExpiryMonthAndYear() {
        if(is_numeric($this->cardExpire)) {
            return $this->getExpiryBySubStr($this->cardExpire);
        } else {
            return $this->getExpiryByExplode($this->cardExpire);
        }
    }

    /**
     * Returns type of Payment Source e.g.
     * <b>VC</b> as <i>Virtual Card</i>,
     * <b>CC</b> as <i>Credit Card</i>,
     * <b>BT</b> as <i>Bank Transfer</i>
     *
     * @return string
     */
    public function getTypeofPaymentSource() {

        if($this->infoItems !== null && is_array($this->infoItems)) {

            switch ($this->channelCode) {

                case BS_Expedia::BA_CHANNEL_CODE:

                    return (new BS_Expedia())->getTypeofPaymentSource($this->infoItems);

                case BS_CTrip::BA_CHANNEL_CODE:
                    return (new BS_CTrip())->getTypeofPaymentSource($this->infoItems);

                case BS_BookingCom::BA_CHANNEL_CODE:
                    return (new BS_BookingCom())->getTypeofPaymentSource($this->infoItems);

                case BS_Agoda::BA_CHANNEL_CODE:
                    return (new BS_Agoda())->getTypeofPaymentSource($this->infoItems);
            }

        }

        if($this->channelCode != null && $this->channelCode != '') {

            switch ($this->channelCode) {
                case BS_CTrip::BA_CHANNEL_CODE:
                case BS_Agoda::BA_CHANNEL_CODE:
                    return BS_Generic::PS_VIRTUAL_CARD;
            }
        }

        return BS_Generic::PS_CREDIT_CARD;
    }

    /**
     * Returns due date as string, Or false. In case of false use check-in date as <b>Due Date</b>
     * @return bool|string
     */
    public function getDueDateFromGuestComments() {

        if($this->guestComments == null || !isset($this->guestComments))
            return $this->firstNight;

        switch ($this->channelCode) {

            case BS_Expedia::BA_CHANNEL_CODE:
                $bs = new BS_Expedia();
                $due = $bs->getDueDate($this->guestComments);
                return $due === false ? $this->firstNight : $due;

            case BS_CTrip::BA_CHANNEL_CODE:
                $bs = new BS_CTrip();
                $due = $bs->getDueDate($this->guestComments);
                return $due === false ? $this->firstNight : $due;

            case BS_BookingCom::BA_CHANNEL_CODE:
                $bs = new BS_BookingCom();
                $due = $bs->getDueDate($this->guestComments);
                return $due === false ? $this->firstNight : $due;

            case BS_Agoda::BA_CHANNEL_CODE:
                $bs = new BS_Agoda();
                $due = $bs->getDueDate($this->guestComments);
                return $due === false ? $this->firstNight : $due;

            default: // If nothing found then use CheckIn date as Due date.
                return $this->firstNight;
        }

    }

    public function adjustIfTestBooking() {
        if($this->channelCode == null || $this->channelCode == 0) {
            if(isset($this->refererOriginal) && str_contains($this->refererOriginal, "ch:")) {
                $split = explode(":", $this->refererOriginal);
                $this->channelCode = $split[1];

            } elseif(isset($this->bookingReferer) && str_contains($this->bookingReferer, "ch:")) {
                $split = explode(":", $this->bookingReferer);
                $this->channelCode = $split[1];
            }
        }
    }

    public function adjustCheckoutDate() {
        // Adding 1 day in LastNight date date.
        // Because we are using lastNight parameter as checkout date and its 1 day behind from actual checkout date.
        // For proper calculation we had to add 1 day in LastNight date.
        try {
            $this->lastNight = Carbon::make($this->lastNight)->addDay(1)->toDateString();
        } catch (\Exception $e) {
            Log::critical("Date Format issue in last night for booking: " . $this->id);
        }
    }

    public function adjustBookingStatusForXmlTextToInteger() {
        switch ($this->bookingStatus) {
            case 'Cancelled':
            case 'cancelled':
                $this->bookingStatus = 0;
                break;
            case 'Confirmed':
            case 'confirmed':
                $this->bookingStatus = 1;
                break;
            case 'New':
            case 'new':
                $this->bookingStatus = 2;
                break;
            case 'Request':
            case 'request':
                $this->bookingStatus = 3;
                break;
            case 'Black':
            case 'black':
                $this->bookingStatus = 4;
                break;
        }
    }

    public function getBookingStatusAsText() {
        switch ($this->bookingStatus) {
            case 0:
                return 'Cancelled';
            case 1:
                return 'Confirmed';
            case 2:
                return 'New';
            case 3:
                return 'Request';
            case 4:
                return 'Black';
            default:
                return 'default';
        }
    }


    public function isNonRefundableBooking(){

        if (($this->getTypeofPaymentSource() == BS_Generic::PS_CREDIT_CARD) && ($this->channelCode == BS_BookingCom::BA_CHANNEL_CODE)) {

            $nonRefundableString = 'Please note, if cancelled, modified or in case of no-show, the total price of the reservation will be charged';
            $nonRefundableString2 = 'The guest will be charged the total price of the reservation if they cancel anytime';
            return ($this->apiMessage != null && (strpos($this->apiMessage, $nonRefundableString) !== false
                    || strpos($this->apiMessage, $nonRefundableString2) !== false));
        }
        return false;
    }


    /**
     * @param $amountToChargeByCA
     * @return array
     */

    public function isValidToChargeByCheckingBalanceOnPMS(float $amountToChargeByCA) {


        $result = ['status' => false, 'isRoundError' => false, 'amountToCharge' => $amountToChargeByCA];
        $toBeChargeAmountOnPMS = 0.0;
        $chargedAmountOnPMS = 0.0;

        if($this->invoice != null && is_array($this->invoice) && count($this->invoice) > 0) {

            /**
             * @var $item InvoiceItem
             */
            foreach ($this->invoice as $item) {
                if( ((int) $item->type) < 200) {
                    $toBeChargeAmountOnPMS += (float) ((float)$item->price) * ((int)$item->quantity);

                } elseif (((int) $item->type) >= 200) {
                    $chargedAmountOnPMS += ((float)$item->price) * abs(((int)$item->quantity));
                }
            }
              //$chargedAmountOnPMS         =>  paid amount             => 327.00
              //$toBeChargeAmountOnPMS      =>  total payable amount    => 2327.00
              //$amountToChargeByCA         =>  total (billing) amount  => 2327.00
              //abs($guestCredit)           =>  remaining amount        => 2000.00

            /**
             * Some amount has been charged by client already.
             */
            if($chargedAmountOnPMS > 0) {

                $guestCredit = ($chargedAmountOnPMS - $toBeChargeAmountOnPMS);
                //Guest Credit amount if (guestCredit = 0 means no balance remaining on PMS) |
                // (guestCredit > 0 means already charged extra amount on PMS)

                if ($guestCredit >= 0) {
                    $result['status'] = false;
                    $result['code-block'] = "1";
                    return $result;
                }


                $flag = (abs($guestCredit) > $amountToChargeByCA) || float_compare(abs($guestCredit), $amountToChargeByCA);

                $this->adjustDifferenceIfAny($amountToChargeByCA, abs($guestCredit), $result);

                $result['code-block'] = "2";
                $result['status'] = $flag;
                return $result;

            }
            else {

                $flag = ($toBeChargeAmountOnPMS > $amountToChargeByCA) || float_compare($toBeChargeAmountOnPMS, $amountToChargeByCA);

                $this->adjustDifferenceIfAny($amountToChargeByCA, $toBeChargeAmountOnPMS, $result);

                $result['code-block'] = "3";
                $result['status'] = $flag;
                return $result;
            }

        } else {

            $flag = ($amountToChargeByCA < $this->price) || float_compare($amountToChargeByCA, $this->price);

            $this->adjustDifferenceIfAny($amountToChargeByCA, $this->price, $result);

            $result['code-block'] = "4";
            $result['status'] = $flag;
            return $result;

        }
    }

    private function adjustDifferenceIfAny(float $amountToChargeByCA, float $otherAmount, array &$result) {

        $difference = round(abs($amountToChargeByCA - $otherAmount), 2);

        if($difference > 0 && $difference <= 0.05) {
            $result['isRoundError'] = true;
            $result['amountToCharge'] = $amountToChargeByCA - $difference;
            $result['difference'] = $difference;

        } else {

            $priceDifference = (float) number_format(abs($amountToChargeByCA - $this->price), 2);

            if($priceDifference > 0.0 && $priceDifference <= 0.05) {
                $result['isRoundError'] = true;
                $result['amountToCharge'] = $amountToChargeByCA - $priceDifference;
                $result['difference'] = $priceDifference;
            }
        }

    }

    /**
     * For a Booking to be master it should have master-id and it should match with booking-id and also
     * groupBookings should have minimum 1 record.
     * @return bool
     */
    public function isMasterBooking() {
        return !empty($this->groupBookings) && !empty($this->masterId) && $this->masterId == $this->id;
    }

    /**
     * For a Booking to be groupBooking it should not have any groupBooking-Records and Master-ID should not match
     * with booking's ID
     * @return bool
     */
    public function isGroupBooking() {
        return empty($this->groupBookings) && !empty($this->masterId) && $this->masterId != $this->id;
    }

    /**
     * This function returns value for master_id column for booking_infos table.
     * @return int
     */
    public function getMasterId() {

        if($this->isMasterBooking())
            return 0;

        if($this->isGroupBooking())
            return $this->masterId;

        return -1;
    }


    /**
     * To get expiry for those Channels who send card expiry in 022019 format (Expedia, CTrip, ...)
     * @param $expiry
     * @return array
     */
    public function getExpiryBySubStr($expiry) {
        $expiryMonthYear = array('month'=>'00', 'year'=>'0000');
        if(strlen($expiry) < 4) {
            $expiry = str_pad($expiry, 4, '0', STR_PAD_LEFT);
        }
        $month = substr($expiry, 0, 2);
        $year = substr($expiry, 2, 4);
        $expiryMonthYear['month'] = $month;
        $expiryMonthYear['year'] = '20' . $year;
        return $expiryMonthYear;
    }

    /**
     * To get expiry for those Channels who send card expiry in 02/2019 format (Booking.com)
     * @param $expiry
     * @return array
     */
    public function getExpiryByExplode($expiry) {
        $expiryMonthYear = array('month'=>'00', 'year'=>'0000');
        if($expiry !== null && $expiry != '') {
            $expiry = stripcslashes($expiry);
            //$split = explode('/', $expiry);
            $split = preg_split('/[\s\\\\\/,-]/', $expiry);
            $expiryMonthYear['month'] = $split[0] ?? $expiryMonthYear['month'];
            $expiryMonthYear['year'] = $split[1] ?? $expiryMonthYear['year'];
        }
        return $expiryMonthYear;
    }

}
