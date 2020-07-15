<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/1/19
 * Time: 9:11 AM
 */

namespace App\System\PMS\BookingSources;


use App\System\PMS\Models\InfoItems;

class BS_Expedia extends BS_Generic {

    const BA_CHANNEL_CODE = 14;
    const CODE_EXPEDIACOLLECT = 'EXPEDIACOLLECT';
    const CODE_HOTELCOLLECT = 'HOTELCOLLECT';

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

            if (str_contains(strtolower($infoItem->code), strtolower(self::CODE_HOTELCOLLECT)))
                return self::PS_CREDIT_CARD;

            if (str_contains(strtolower($infoItem->code), strtolower(self::CODE_EXPEDIACOLLECT))) {

                if(str_contains(strtolower($infoItem->text),
                    array(strtolower(self::TEXT_VIRTUAL),
                        strtolower(self::TEXT_VIRTUAL) . ' ' . strtolower(self::TEXT_CARD))))
                    return self::PS_VIRTUAL_CARD;

            }

        }

        /*
         * In case of Expedia Bank transfer has no information for identification, so we assume if nothing
         * is found its bank transfer
         */

        return self::PS_BANK_TRANSFER;
    }

    /**
     * Returns date in string if found else it returns false, <b>false</b indicates to use checkIn date as due date.
     *
     * @param string $comment
     * @return bool|string
     */
    public function getDueDate(string $comment) {
        /*
         * In case of Expedia Due date is always Checking date or First Night date
         */
        return false;
    }

}