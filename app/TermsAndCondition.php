<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const REQUIRED = 1;
    const NOT_REQUIRED = 0;

    protected $table = "terms_and_conditions";
    protected $guarded = ['id','serve_id','selected_properties'];
    protected $fillable = ['user_id','user_account_id','internal_name','text_content','checkbox_text','required', 'status' ];

    public function user_account(){
        return $this->belongsTo(UserAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function termsAndConditionPropertiesBridge(){
        return $this->hasMany(TermsAndConditionRentalBridge::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function property_info(){
        return $this->hasManyThrough(
            PropertyInfo::class,
            TermsAndConditionRentalBridge::class,
            'terms_and_condition_id',
            'id',
            'id',
            'property_info_id'
        );
    }


}
