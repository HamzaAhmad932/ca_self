<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/10/18
 * Time: 5:18 PM
 */

namespace App\System\PMS\Models;


class InfoItems {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'code' => 'code',
                'text' => 'text'
            ),
            'xmlAttributes' => array(

            ),
            'xml' => array(

            )
    )
    );

    public $code = null;
    public $text = null;

}