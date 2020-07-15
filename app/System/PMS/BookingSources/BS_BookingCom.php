<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/1/19
 * Time: 9:12 AM
 */

namespace App\System\PMS\BookingSources;


use App\System\PMS\Models\InfoItems;

class BS_BookingCom extends BS_Generic {

    const BA_CHANNEL_CODE = 19;
    const CODE_BOOKINGCOMVIRTCARD = 'BOOKINGCOMVIRTCARD';
    const CODE_BOOKINGCOMBANKTRANS = 'BOOKINGCOMBANKTRANS';

    /**
     * Returns type of Payment Source e.g.
     * <b>VC</b> as <i>Virtual Card</i>,
     * <b>CC</b> as <i>Credit Card</i>,
     * <b>BT</b> as <i>Bank Transfer</i>
     *
     * @param array $infoItems
     * @return string
     */
    public function getTypeofPaymentSource(array $infoItems) {

        for ($i = 0; $i < count($infoItems); $i++) {
            /**
             * @var $infoItem InfoItems
             */
            $infoItem = $infoItems[$i];

            if (str_contains(strtolower($infoItem->code), strtolower(self::CODE_BOOKINGCOMVIRTCARD)))
                return self::PS_VIRTUAL_CARD;

            if (str_contains(strtolower($infoItem->code), strtolower(self::CODE_BOOKINGCOMBANKTRANS)))
                return self::PS_BANK_TRANSFER;
        }

        /*
         * For Credit card no information found in InfoItems, to identify it as credit card
         */

        return self::PS_CREDIT_CARD;
    }

    /**
     * Returns date in string if found else it returns false, <b>false</b indicates to use checkIn date as due date.
     *
     * @param string $comment
     * @return bool|string
     */
    public function getDueDate(string $comment) {

        // Sample 1: You have received a virtual credit card for this reservation.You may charge it as of 2019-01-17.
        // Sample 2: You've received a virtual credit card for this reservation.You can charge it as of 2019-07-03.\nNon Smoking Requested

        $result = $this->dueDateScraper($comment, 'You may charge it as of ');
        if($result !== false)
            return $result;

        $result = $this->dueDateScraper($comment, 'You can charge it as of ');
        if($result !== false)
            return $result;

        /**
         * When language is other than "English"
         * NOTE: this can cause problem if there are multiple dates and 1st date is not due-date!!!
         */
        $date = $this->searchWithRegularExpression($comment);
        if($date !== false)
            return $date;

        return false;

    }

    private function dueDateScraper(string $comment, string $needle) {

        $haystack = strtolower($comment);
        $needle = strtolower($needle);
        $startPosition = strpos($haystack, $needle);

        if($startPosition !== false) {
            return substr($haystack, $startPosition + strlen($needle), 10);
        }

        return false;
    }

    private function searchWithRegularExpression(string $comment) {

        $matches = [];

        $op = preg_match('/\d{4}-\d{2}-\d{2}/',  $comment, $matches);

        if($op === 1)
            return $matches[0];

        return false;
    }
    /*
     GuestMessage:
    You've received a virtual credit card for this reservation.You can charge it as of 2020-01-14.
    \n
    You can charge virtual credit cards (VCC) for up to six months from the date on which the guest checks out, after which the VCC will expire; if the VCC expires, the period to charge the VCC can be extended for another six-month period running from the date on which the card expired. After the expiration of this second six-month period, you will not have any entitlement to the funds and from that point in time, the funds will belong to Booking.com.
    \n
    Hi! I am Maria Rodriguez, I would liek to know where I can park my car as well as the fees.
    \n
    Thank you.
    \n
    Non Smoking Requested

    ApiMessage: 2020-01-14 18:23:54
    \n
    One-Bedroom Apartment with Balcony
    \n
    Non Smoking Requested\ncommissionamount=40.7184\ncurrencycode=CAD\ntotalprice=213.90\ncustomer first_name=Maria\ncustomer last_name=Rodriguez\ncredit card type=MasterCard (virtual credit card)\nroom commissionamount=40.7184\nroom extra_info=This apartment features a tumble dryer, washing machine and kitchenware.\nroom facilities=Private Bathroom, View, Accessible by elevator\nroom info=No meal is included in this room rate. Children and Extra Bed Policy: All children are welcome. There is no capacity for cribs in the room. There is no capacity for extra beds in the room.  Deposit Policy: The guest will be charged a prepayment of the total price of the reservation anytime.  Cancellation Policy: The guest will be charged the total price of the reservation if they cancel anytime. \nroom meal_plan=No meal is included in this room rate.\nroom roomreservation_id=2770169153
     */

    public function detectVC(string $guestComment, string $apiComment) {

        /**
         * This function was added when Booking.Com implemented feature that first time it sends CC and afterwards
         * Guest can change it to VC if any problem occurs with first CC.
         *
         * Following logic is our own!
         */

        $string1 = strtolower("virtual credit card");
        $string2 = strtolower("virtual credit card");
        $string3 = strtolower("You've received a virtual credit card for this reservation");
        $comment = strtolower($guestComment);

        if(strpos($apiComment, $string1) !== false) // First check in ApiComments
            if(strpos($comment, $string2) !== false || strpos($comment, $string3) !== false) // Then Check Guest Comments
                return true;

        return false;

    }

}