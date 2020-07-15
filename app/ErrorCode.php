<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ErrorCode extends Model implements Auditable
{
    use AuditableTrait;
     public function error_logs()
    {

        return $this->hasMany('App\ErrorLog');

    }

}