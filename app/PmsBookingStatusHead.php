<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PmsBookingStatusHead extends Model implements Auditable
{
    use AuditableTrait;
    
    public function booking_info()
    {


        return $this->hasMany('App\BookingInfo', 'pms_booking_status', 'code');

    }

}
