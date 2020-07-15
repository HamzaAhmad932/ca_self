<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model {

    protected $fillable = ['message', 'stack_trace', 'file', 'line', 'user_id', 'user_account_id', 'booking_info_id',
        'booking_pms_id', 'meta_data'];

}
