<?php

namespace App;

use App\CreditCardAuthorization;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;

class Siteminder extends Model {

    protected $table = 'siteminders';

}