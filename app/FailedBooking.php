<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class FailedBooking extends Model
{
    //
    protected $fillable = [ 'user_account_id' , 'channel_code' , 'pms_property_id' , 'pms_booking_id' ,  'status' , 'exception' ];
}
