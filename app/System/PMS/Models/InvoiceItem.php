<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/5/18
 * Time: 4:18 PM
 */

namespace App\System\PMS\Models;


class InvoiceItem {

    public static $M_KEYS = array(
        'BookingAutomation' => array(
            'json' => array(
                'invoiceId' => 'id',
                'description' => 'description',
                'status' => 'status',
                'qty' => 'quantity',
                'price' => 'price',
                'vatRate' => 'vatRate',
                'type' => 'type'
            ),
            'xmlAttributes' => array(
                'id' => 'id',
                'type' => 'type',
                'action' => 'action'
            ),
            'xml' => array(
                'quantity' => 'quantity',
                'itemPrice' => 'price',
                'vatRate' => 'vatRate',
                'description' => 'description',
                'status' => 'status'
            )
        )
    );

    public $id;
    public $description;
    public $status;
    public $quantity;
    public $price;
    public $vatRate;
    public $type;

    /*
     * only XML attribute
     */
    public $action;


}