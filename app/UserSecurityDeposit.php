<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserSecurityDeposit extends Model implements Auditable
{
	use AuditableTrait;
	/**
	 * @property SecurityDepositForm|null security_deposit_form
	 * @property User|null User
	 */
    public function security_deposit_form()
	{
		return $this->belongsTo('App\SecurityDepositForm');
	}
	public function User()
	{
		return $this->belongsTo('App\User');
	}
}
