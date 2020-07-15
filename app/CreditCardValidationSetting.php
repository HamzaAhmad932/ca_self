<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CreditCardValidationSetting extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = [
      'form_id', 'settings'
    ];

    public function user_settings_bridge()
   {
        return $this->belongsTo('App\UserSettingsBridge');
    }

    

}
