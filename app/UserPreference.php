<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserPreference extends Model implements Auditable
{
	use AuditableTrait;
	protected $fillable = ['user_id','user_account_id','name','preferences_form_id','form_data','status','created_at','updated_at'];
	/**
	 * @property PreferencesForm|null preferences_form
	 * @property User|null User
	 * @property ErrorLog|null error_logs
	 */
    public function preferences_form()
	{
		return $this->belongsTo('App\PreferencesForm');
	}

	public function User()
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
