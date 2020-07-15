<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SecurityDepositForm extends Model implements Auditable
{
	use AuditableTrait;
	
    public function user_security_deposit()
		{

    			return $this->hasOne('App\UserSecurityDeposit');
		}
}
