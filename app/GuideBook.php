<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class GuideBook extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $guarded = ['id','serve_id','selected_properties'];
    protected $fillable = ['user_id','user_account_id','guide_book_type_id','icon','internal_name','text_content', 'status' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_account(){
        return $this->belongsTo(UserAccount::class);
    }

    public function guideBookType() {
        return $this->belongsTo(GuideBookType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guideBookPropertiesBridge(){
        return $this->hasMany(GuideBookPropertiesBridge::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function property_info(){
        return $this->hasManyThrough(
            PropertyInfo::class,
            GuideBookPropertiesBridge::class,
            'guide_book_id',
            'id',
            'id',
            'property_info_id'
        );
    }

}
