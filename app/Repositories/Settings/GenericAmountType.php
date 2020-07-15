<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 26-Dec-18
 * Time: 2:41 PM
 */

namespace App\Repositories\Settings;


abstract class GenericAmountType {

    const AMOUNT_TYPE_FIXED = 1;
    const AMOUNT_TYPE_BOOKING_AMOUNT_PERCENTAGE = 2;
    const AMOUNT_TYPE_FIRST_NIGHT = 3;
    const AFTER_BOOKING=1;
    const BEFORE_CHECK_IN=2;
}