<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ReattemptPolicy extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = [
        'user_id', 'user_account_id', 'attempts', 'hours', 'stop_after',

    ];
}
