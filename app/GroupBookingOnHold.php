<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GroupBookingOnHold
 * @package App
 * @property int user_account_id
 * @property int pms_booking_id
 * @property int master_id
 * @property string booking_status
 * @property int channel_code
 * @property int pms_property_id
 * @property string token
 * @property int cvv
 * @property string|null caller
 */
class GroupBookingOnHold extends Model {

    use SoftDeletes;

    protected $fillable = ['user_account_id', 'pms_booking_id', 'master_id', 'booking_status', 'channel_code',
        'pms_property_id', 'token', 'cvv', 'caller'];
}
