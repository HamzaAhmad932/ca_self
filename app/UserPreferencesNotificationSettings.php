<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class UserPreferencesNotificationSettings extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = ['user_account_id','activity_id','notify_settings','to_email','cc_email','bcc_email',];

    public function user_account()
    {
        return $this->belongsTo('App\UserAccount');
    }

    public function activity()
    {

        return $this->belongsTo('App\Activity');
    }

}
