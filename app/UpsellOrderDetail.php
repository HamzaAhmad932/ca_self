<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UpsellOrderDetail extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['upsell_order_id', 'upsell_id', 'upsell_price_settings_copy', 'amount', 'persons'];

    public function UpsellOrder() {
        return $this->belongsTo(UpsellOrder::class);
    }

    public function upsell(){
        return $this->belongsTo(Upsell::class);
    }
}
