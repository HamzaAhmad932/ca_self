<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @property PropertyInfo|null property_info
 */

class RoomInfo extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['name', 'property_info_id', 'pms_room_id', 'available_on_pms'];

    public function property_info() {
        return $this->belongsTo('App\PropertyInfo');
    }
    public function booking_info(){
        return $this->hasOne(BookingInfo::class, 'room_id', 'pms_room_id');
    }

    /**
     *  Get All Upsell or Room_info
     * @return mixed
     */
    public function upsells(){
        return Upsell::whereIn('id', $this->upsellBridge->pluck('upsell_id')->toArray())->with('upsellType')->get();
    }

    /**
     * did't work with eager Loading
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function upsellBridge()
    {
        return $this->hasMany(UpsellPropertiesBridge::class, 'property_info_id', 'property_info_id')
            ->where('room_info_ids', null)->orWhere("room_info_ids", 'LIKE',
                '%"'.$this->getAttribute('id').'"%')->where('property_info_id', $this->getAttribute('property_info_id'));
    }

    /**
     *  Get  Term and condition regarding to Room_info
     * @return mixed
     */
    public function termsAndCondition()
    {

        $like = $this->termsAndCondtionBridge->where("room_info_ids", 'LIKE', '%"'.$this->getAttribute('id').'"%');
        if ($like->count())
            return $like; //  $like->relation TODO Make Relation
        else
            return $this->termsAndCondtionBridge->where("room_info_ids", null); // ->relation TODO Make Relation
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function termsAndCondtionBridge()
    {
        return $this->hasMany(TermsAndConditionRentalBridge::class, 'property_info_id', 'property_info_id')
            ->where('room_info_ids', null)->orWhere("room_info_ids", 'LIKE',
                '%"'.$this->getAttribute('id').'"%')->where('property_info_id', $this->getAttribute('property_info_id'));
    }

}
