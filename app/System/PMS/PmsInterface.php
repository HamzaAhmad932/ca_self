<?php

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 9/27/18
 * Time: 2:52 PM
 */

namespace App\System\PMS;

use App\System\PMS\Models\Booking;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\UserAccount;

interface PmsInterface {

    /**
     * Retrieve User Properties
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     */
    function fetch_properties(UserAccount $user, PmsOptions $options);

    /**
     * Retrieve User Properties
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     */
    function fetch_properties_json_xml(UserAccount $user, PmsOptions $options);

    /**
     * Retrieve User's single Property
     * Set PropertyKey and Property ID
     *
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     */
    function fetch_property(UserAccount $user, PmsOptions $options);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     */
    function fetch_Booking_Details(UserAccount $user, PmsOptions $options);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return array|null
     */
    function fetch_Booking_Details_json_xml(UserAccount $user, PmsOptions $options);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param Booking $bookingToUpdateData
     * @return mixed
     */
    function update_booking(UserAccount $user, PmsOptions $options, Booking $bookingToUpdateData);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @param array $propertiesToUpdateData Array of Property to be update
     * @return mixed
     */
    function update_properties(UserAccount $user, PmsOptions $options, array $propertiesToUpdateData);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return mixed
     */
    function fetch_user_account(UserAccount $user, PmsOptions $options);

    /**
     * @param UserAccount $user
     * @param PmsOptions $options
     * @return mixed
     */
    function fetch_card_for_booking(UserAccount $user, PmsOptions $options);

    /**
     * Returns response of api in form of string, json or xml
     * @return null|string
     */
    function getActualResponse();

}