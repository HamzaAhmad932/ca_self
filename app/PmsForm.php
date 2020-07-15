<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class PmsForm
 * @package App
 * @property $logo
 * @property $name
 * @property $backend_name
 * @property $pms_parent_id
 * @property $status
 * @property $instruction_page
 * @property $page_configuration
 * @property PmsParent $parent_pms
 */
class PmsForm extends Model implements Auditable
{
    use AuditableTrait;
    protected $fillable = ['logo', 'name', 'backend_name', 'pms_parent_id', 'status', 'instruction_page', 'page_configuration'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user_pms() {
		return $this->hasOne('App\UserPms');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function booking_source_forms() {
        return $this->hasMany('App\BookingSourceForm');
    }

    public function isActive() {
        return $this->status == 1;
    }

    public function transaction_init() {
        return $this->hasMany('App\TransactionInit');
    }

    public function propertyInfos()
    {
        return $this->hasMany(PropertyInfo::class, 'pms_id', 'id');
    }

    /* Accessors */
    public function getInstructionPageAttribute($instruction_page) {
        if(!empty($instruction_page))
            return route($instruction_page);
        return '#';
    }

    public function parent_pms() {
        return $this->hasOne(PmsParent::class, 'id', 'pms_parent_id');
    }
}
