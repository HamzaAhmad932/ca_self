<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermsAndConditionRentalBridge extends Model
{
    protected $table = 'terms_and_condition_properties_bridges';
    protected $fillable = ['terms_and_condition_id','property_info_id','room_info_ids'];
    public $timestamps = true;
    use RoomInfoParser;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function termsAndConditions(){
        return $this->belongsTo(TermsAndCondition::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property_info(){
        return $this->belongsTo(PropertyInfo::class);
    }
}
