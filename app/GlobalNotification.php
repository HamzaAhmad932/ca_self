<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class GlobalNotification extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = [
'role_id','message','status'
];
}
