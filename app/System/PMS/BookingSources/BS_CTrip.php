<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/1/19
 * Time: 9:12 AM
 */

namespace App\System\PMS\BookingSources;


use App\System\PMS\Models\InfoItems;

class BS_CTrip extends BS_Generic {

    const BA_CHANNEL_CODE = 53;
    const CODE_CTRIPCOLLECT = 'CTRIPCOLLECT';

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

            if(str_contains(strtolower($infoItem->code), strtolower(self::CODE_CTRIPCOLLECT)))
                return self::PS_VIRTUAL_CARD;

        }

        /*
         * CTrip always give virtual card
         */
        return self::PS_VIRTUAL_CARD;
    }

    /**
     * Returns date in string if found else it returns false, <b>false</b indicates to use checkIn date as due date.
     *
     * @param string $comment
     * @return bool|string
     */
    public function getDueDate(string $comment) {
        /*
         * In case of CTrip Due date is always Checking date or First Night date
         */
        return false;
    }
}