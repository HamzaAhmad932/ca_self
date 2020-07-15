<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PreferencesForm extends Model implements Auditable
{
	use AuditableTrait;

    protected $fillable = ['id', 'name', 'form_id', 'form_data'];

     public function user_preference()
		{

    			return $this->hasOne('App\UserPreference');
		}
}
