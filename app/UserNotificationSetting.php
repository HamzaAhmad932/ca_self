<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserNotificationSetting extends Model implements Auditable
{
    use AuditableTrait;
    /**
     * @property UserAccount|null user_account
     * @property Activity|null activity
     */
    protected $fillable = [
        'user_account_id','activity_id','sms','email','to_email','cc_email','bcc_email',
    ];

     public function user_account()
    {
        //return $this->belongsTo('App\User_account', 'user_notification_setting.user_account_id','user_account.id');
        //above line is same link blow line. it's just for understangin
        //relationship in laravel

        return $this->belongsTo('App\UserAccount');
    }
    
    public function activity()
    {
        //return $this->belongsTo('App\Activity', 'user_notification_setting.activity_id','activity.id');
        //above line is same link blow line. it's just for understangin
        //relationship in laravel
        return $this->belongsTo('App\Activity');
    }
}
