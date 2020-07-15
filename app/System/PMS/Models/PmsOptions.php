<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/5/18
 * Time: 10:55 AM
 */

namespace App\System\PMS\Models;


class PmsOptions {

    public const REQUEST_TYPE_JSON = 'json';
    public const REQUEST_TYPE_XML = 'xml';
    
    /**
     * Can store any thing, But added for site-minder class, to pass XML response of reservation notification.
     * @var string
     */
    public $dump = '';

    /**
     * @var string 'json' or 'xml'
     */
    public $requestType = null;

    /**
     * @var integer|string|null
     */
    public $bookingID = null;

    /**
     * @var string|null;
     */
    public $bookingToken = null;


    /**
     * @var string|null;
     */
    public $cardCvv = null;


    /**
     * @var boolean
     */
    public $includeCard = false;

    /**
     * @var integer|string|null
     */
    public $propertyID = null;

    /**
     * @var integer|string|null
     */
    public $roomID = null;

    /**
     * @var integer|string|null
     */
    public $masterID = null;

    /**
     * @var string|null
     */
    public $modifiedDate = null;

    /**
     * @var string|null
     */
    public $dateFrom = null;

    /**
     * @var string|null
     */
    public $dateTo = null;

    // --------------- added for JSON request -----------------

    /**
     * @var bool|null
     */
    public $includeInvoice = null;

    /**
     * @var bool|null
     */
    public $includeInfoItems = null;

    /**
     * @var string|null
     *
     * Sample Data example: 20131001 12:30:00
     */
    public $arrivalFrom = null;

    /**
     * @var string|null
     *
     * Sample Data example: 20131001 12:30:00
     */
    public $arrivalTo = null;

    /**
     * @var string|null
     *
     * Sample Data example: 20131001 12:30:00
     */
    public $departureFrom = null;

    /**
     * @var string|null
     *
     * Sample Data example: 20131001 12:30:00
     */
    public $departureTo = null;

    /**
     * @var string|null
     *
     * Sample Data example: 20131001 12:30:00
     */
    public $modifiedSince = null;

    /**
     * @var null|string
     */
    public $propertyKey = null;

    /**
     * @var boolean
     *
     * Report Invalid Card to Booking.com
     * use this key for auth json : bookingcomInvalidCard
     */
    public $bookingInvalidCard;

    /**
     * @var boolean
     *
     * Report No Show to Booking.com
     * use this key for auth json: bookingcomNoShow
     */
    public $bookingNoShow;

    /**
     * @var boolean
     *
     * Report Cancellation Request to Booking.com
     * use this key for auth json: bookingcomReportCancel
     */
    public $bookingReportCancel;

    /**
     * @var boolean
     * IF Updating Multiple Properties with XML Request and wants to get Full XML Response on Fail or Success Turn $getFullXmlResponse = true
     * Enabling getFullXmlResponse PMS Will not throw any exception it always return XML Object.
     *
     */
    public  $getFullXmlResponse = false;


}
