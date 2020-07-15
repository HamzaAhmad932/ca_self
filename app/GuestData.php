<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class GuestData
 * @package App
 * @property string phone
 * @property string country_code
 */
class GuestData extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['booking_id', 'email', 'phone', 'arrivaltime', 'country_code', 'adults', 'childern'];

    public function booking_info(){
        return $this->hasOne(BookingInfo::class, 'id', 'booking_id');
    }

    public function getFullPhoneAttribute() {
        return $this->phone.$this->country_code;
    }

}
