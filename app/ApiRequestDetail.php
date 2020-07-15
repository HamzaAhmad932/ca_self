<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @property $full_request_url
 */
class ApiRequestDetail extends Model
{
    //
    protected $fillable = [ 'user_account_id' , 'channel_code' , 'pms_property_id' , 'pms_booking_id' , 'full_request_url' , 'status' , 'client_ip' ];
}


