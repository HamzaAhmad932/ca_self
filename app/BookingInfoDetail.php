<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingInfoDetail extends Model
{
    protected $fillable = [
        'booking_info_id', 'cc_auth_settings', 'security_deposit_settings', 'payment_schedule_settings',
        'cancellation_settings', 'payment_gateway_settings', 'full_response', 'use_bs_settings', 'use_pg_settings',
    ];


    public function bookingInfo() {
        return $this->belongsTo(BookingInfo::class);
    }

}
