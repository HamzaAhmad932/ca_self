<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UpsellPropertiesBridge extends Model implements Auditable
{
    use AuditableTrait;
    use RoomInfoParser;
    const STATUS_ACTIVE=1;
    const STATUS_IN_ACTIVE=0;

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function propertyInfo() {
        return $this->belongsTo(PropertyInfo::class);
    }
    public function upsell() {
        return $this->belongsTo(Upsell::class, 'upsell_id', 'id');
    }

}
