<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/24/18
 * Time: 12:46 PM
 */

namespace App\System\PaymentGateway\Models;


abstract class CardMeta {

    public $statement_descriptor;
    public $general_description;
    public $postalCode;
    public $address1;
    public $address2;
    public $street;
    public $city;
    public $state;
    public $country;
    public $eMail;
    public $phone;
    public $mobile;
    /**
     * @var array
     *
     * Key : Value pare
     */
    public $metadata;

}