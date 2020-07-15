<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class UserPms
 * @package App
 * @property $id
 * @property PmsForm pms_form
 * @property int $pms_form_id
 */
class UserPms extends Model implements Auditable
{
    use AuditableTrait;
    /**
     * @property mixed pms_form
     * @property string form_data
     * @property User|null User 
     * @property ErrorLog|null error_logs
     * @property PropertyInfo|null properties_info
    */

    protected $fillable = ['id', 'name', 'pms_form_id', 'user_id', 'user_account_id', 'form_data','unique_key','is_verified','created_at', 'updated_at'];

    public function pms_form()
    {
        return $this->belongsTo('App\PmsForm');
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

    public function properties_info()
    {
        return $this->hasMany('App\PropertyInfo');
    }

    public function isVerified() {
        return $this->is_verified == 1;
    }

}
