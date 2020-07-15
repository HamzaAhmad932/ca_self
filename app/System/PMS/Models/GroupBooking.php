<?php


namespace App\System\PMS\Models;


class GroupBooking {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'bookId' => 'id'
            ),
            'xmlAttributes' => array(
                'id' => 'id'
            ),
            'xml' => array(

            )
        )
    );

    public $id = null;

}