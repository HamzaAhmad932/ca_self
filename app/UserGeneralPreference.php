<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class UserGeneralPreference extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = [
        'name', 'description', 'user_account_id', 'form_id', 'form_data', 'booking_source_form_id'
    ];

    public function userAccount() {
        return $this->belongsTo('App\UserAccount');
    }
}
