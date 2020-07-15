<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UserNotificationActivity extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = [
        'user_account_id','activity_id','sms','email',
    ];
}
