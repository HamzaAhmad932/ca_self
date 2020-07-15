<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class StripeUsageDetail extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = ['user_account_id', 'model_name', 'model_id', 'description','response', 'status'];
}
