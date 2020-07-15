<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PaymentGatewayParent extends Model implements Auditable
{
	use AuditableTrait;
    protected $fillable = ['name', 'backend_name', 'credentials', 'status'];

    public function payment_gateway_form()
		{

    			return $this->hasOne('App\PaymentGatewayForm');
		}
}
