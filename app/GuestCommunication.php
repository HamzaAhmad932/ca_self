<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class GuestCommunication extends Model implements Auditable
{

    use AuditableTrait;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [

        'user_id', 'user_account_id', 'booking_info_id', 'is_guest', 'message',
        'message_read_by_guest','message_read_by_user', 'pms_booking_id', 'alert_type', 'action_performed_by', 'action_performed', 'action_required'

    ];

    public function user_accounts()
    {
        return $this->belongsTo('App\UserAccount');

    }

    public function user()
    {
        return $this->belongsTo('App\User');

    }


    public function booking_info()
    {
        return $this->belongsTo('App\BookingInfo','booking_info_id');

    }


}
