<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/4/18
 * Time: 3:34 PM
 */

namespace App\System\PMS\Models;


class Room {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'name' => 'roomName',
                'qty' => 'quantity',
                'roomId' => 'id',
                'unitNames'=> 'unitNames'
            ),
            'xmlAttributes' => array(
                'id' => 'id',
                'action' => 'action'
            ),
            'xml' => array(
                'roomName' => 'roomName',
                'roomQty' => 'quantity',
                'unitNames'=> 'unitNames'
            )
        )
    );

    public $roomName;
    public $quantity;
    public $id;
    public $unitNames;

    /*
     * only xml attribute
     */
    public $action;


}
