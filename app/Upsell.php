<?php

namespace App;

use App\Repositories\Upsells\UpsellListingMetaParser;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Upsell extends Model implements Auditable
{
    use AuditableTrait;
    const STATUS_ACTIVE=1;
    const STATUS_IN_ACTIVE=0;
    const UPSELL_TYPE_FOR_3DS = 4;

    protected $fillable = [
        'user_account_id','user_id','upsell_type_id','internal_name','meta','value_type','value',
        'per','period', 'notify_guest','status'
    ];

    public function getMetaAttribute($value) {
        return new UpsellListingMetaParser($value);
    }

    public function upsellType() {
        return $this->belongsTo(UpsellType::class);
    }

    public function upsellPropertiesBridge(){
        return $this->hasMany(UpsellPropertiesBridge::class);
    }

    public function userAccount() {
        return $this->belongsTo(UserAccount::class);
    }

    public function upsellOrderDetails(){
        return $this->hasMany(UpsellOrderDetail::class);
    }

}
