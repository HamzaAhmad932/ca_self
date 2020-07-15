<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/1/19
 * Time: 9:13 AM
 */

namespace App\System\PMS\BookingSources;


abstract class BS_Generic {

    const TEXT_VIRTUAL = 'Virtual';
    const TEXT_CARD = 'Card';

    const PS_VIRTUAL_CARD = 'VC';
    const PS_CREDIT_CARD = 'CC';
    const PS_BANK_TRANSFER = 'BT';

    /**
     * Returns type of Payment Source e.g.
     * <b>VC</b> as <i>Virtual Card</i>,
     * <b>CC</b> as <i>Credit Card</i>,
     * <b>BT</b> as <i>Bank Transfer</i>
     *
     * @param array $infoItems
     * @return string
     */
    abstract public function getTypeofPaymentSource(array $infoItems);

}