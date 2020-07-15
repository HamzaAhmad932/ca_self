<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class BookingSourceForm extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * @var array
     */
    protected $fillable = ['logo', 'name', 'channel_code', 'type', 'form_data', 'pms_form_id', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_booking_source()
     {
         return $this->hasMany('App\UserBookingSource');
     }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user_accounts()
    {
        return $this->belongsToMany('App\UserAccount', 'user_booking_sources');
    }

    /**
     *  Won't Work With Eager Loading
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookingInfos(){
        return $this->hasMany(BookingInfo::class, 'pms_id', 'pms_form_id')
            ->where('channel_code', $this->getAttribute('channel_code'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function bookingSourceCapabilities()
    {
        return $this->hasMany('App\BookingSourceCapability');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public  function pms_form()
    {
        return $this->belongsTo('App\PmsForm');
    }

    public function getLogoAttribute($value) {
        return !empty($value) ? $value: getInitialsFromString($this->attributes['name'], 2);
    }

    public function fetchBookingSettings()
    {
        return $this->hasMany('App\FetchBookingSetting');
    }

    public function isCCBookingsSupported()
    {
        return in_array(
            $this->getAttributeValue('type'),
            config('db_const.booking_source_forms.type.cc_supported_types')
        );
    }

    public function isVCBookingsSupported()
    {
        return in_array(
            $this->getAttributeValue('type'),
            config('db_const.booking_source_forms.type.vc_supported_types')
        );
    }

    public function isBTBookingsSupported()
    {
        return in_array(
            $this->getAttributeValue('type'),
            config('db_const.booking_source_forms.type.bt_supported_types')
        );
    }
}
