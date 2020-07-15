<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UpsellCart extends Model implements Auditable
{

    use AuditableTrait;
    protected $table = 'upsell_carts';

    public function bookingInfo() {
        return $this->belongsTo(BookingInfo::class);
    }

}
