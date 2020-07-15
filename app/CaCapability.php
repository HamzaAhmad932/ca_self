<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaCapability extends Model
{
    /**
     * @property BookingSourceCapability booking_source_capabilities
     */
     const FETCH_BOOKING = 'FETCH_BOOKING';
     const AUTO_PAYMENTS = 'AUTO_PAYMENTS';
     const MANUAL_PAYMENTS = 'MANUAL_PAYMENTS';
     const SECURITY_DEPOSIT = 'SECURITY_DEPOSIT';
     const GUEST_EXPERIENCE = 'GUEST_EXPERIENCE';

    const CA_CAPABILITIES_WITH_DESCRIPTION = [
            self::FETCH_BOOKING             => 'Fetch Bookings from PMS',
            self::AUTO_PAYMENTS             => 'Auto Charge Booking Payments',
            self::MANUAL_PAYMENTS           => 'Manually Charge Booking Payments',
            self::SECURITY_DEPOSIT     => 'Auto Booking Security Damage Deposit',
            self::GUEST_EXPERIENCE          => 'Use Guest Experience'
    ];

    /**
     * @var array
     */
    protected $fillable = ['name', 'description','priority', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookingSourceCapabilities()
    {
       return $this->hasMany('App\BookingSourceCapability');
    }
}
