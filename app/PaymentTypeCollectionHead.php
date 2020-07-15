<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PaymentTypeCollectionHead extends Model implements Auditable
{
    use AuditableTrait;
    public function pivot_table()
		{

    			return $this->hasMany('App\PaymentTypePivotTable');
		}
}
