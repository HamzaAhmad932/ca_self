<?php


namespace App\System\PMS\Models;


class BookingCard {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'cardType' => 'cardType',
                'cardName' => 'cardName',
                'cardNumber' => 'cardNumber',
                'cardExpire' => 'cardExpire',
                'cardCvv' => 'cardCvv',
                'derivedExpireYear' => 'derivedExpireYear',
                'derivedExpireMonth' => 'derivedExpireMonth'
            )
        )
    );

    public $cardType;
    public $cardName;
    public $cardNumber;
    public $cardExpire;
    public $cardCvv;
    public $derivedExpireYear = null;
    public $derivedExpireMonth = null;

}