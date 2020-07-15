<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class FetchBookingSetting extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'user_id', 'user_account_id', 'booking_source_form_id', 'status',
    ];

    public function bookingSourceForm()
    {
        return $this->belongsTo('App\BookingSourceForm');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function userAccount()
    {
        return $this->belongsTo('App\UserAccount');
    }
}
