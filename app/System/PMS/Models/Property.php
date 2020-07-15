<?php

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/4/18
 * Time: 3:34 PM
 */

namespace App\System\PMS\Models;


class Property {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'propId' => 'id',
                'name' => 'propertyName',
                'propKey' => 'propertyKey',
                'currency' => 'currencyCode',
                'notifyUrl' => 'caNotifyURL',
                'ownerId' => 'ownerId',
                'action' => 'action',
                'address' => 'address',
                'city' => 'city',
                'country'	=> 'country',
                'latitude' => 'latitude',
                'longitude' => 'longitude'
            ),
            'json_sub'=>array(
                'roomTypes' => array(
                    'var' => 'rooms',
                    'type' => Room::class
                )
            ),
            'xmlAttributes' => array(
                'id' => 'id',
                'action' => 'action'
            ),
            'xml' => array(
                'propertyName' => 'propertyName',
                'notifyUrl' => 'caNotifyURL',
                'currencyCode' => 'currencyCode'
            ),
            'xml_sub' => array(
                'rooms' => array(
                    'var' => 'rooms',
                    'type' => Room::class,
                    'child' => 'room'
                )
            )
        )
    );

    const BA_ACTION_MODIFY = 'modify';
    const BA_ACTION_NEW = 'new';
    const BA_ACTION_DELETE = 'delete';

    public $id;
    public $propertyName;
    public $propertyKey = -1;
    public $currencyCode;
    public $caNotifyURL;
    public $ownerId;
    /**
     * @var array of Room
     */
    public $rooms;

    /*
     * only xml attribute
     */
    public $action;
    public $longitude = '';
    public $latitude = '';
    public $city = '';
    public $country ='';
    public $address = '';
}