<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserPaymentSchedule extends Model implements Auditable
{
    use AuditableTrait;
    /**
     * @property User|null user
     * @property ErrorLog|null error_logs
     */
    public function payment_schedule_form()
    {
        return $this->belongsTo('App\PaymentScheduleForm');
    }
	public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get all of the PMS's Error.
     */
    public function error_logs()
    {
        return $this->morphMany('App\ErrorLog', 'errorable');
    }

}
