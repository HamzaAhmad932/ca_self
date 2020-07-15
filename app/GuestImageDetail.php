<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuestImageDetail extends Model
{

    protected $fillable = ['guest_image_id', 'booking_info_id','image','type','user_account_id','user_id', 'description', 'status'];

    public function booking_info() {
        return $this->belongsTo(BookingInfo::class);
    }
}
