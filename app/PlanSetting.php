<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PlanSetting extends Model implements Auditable
{
    use AuditableTrait;

    const TYPE_TRAIL = 1;
    const TYPE_VOLUME = 2;
    const TYPE_SUBSCRIBE = 3;
    const TYPE_TRANSACTION = 4;
    const TYPE_FLATEFEE = 5;

    const DEFAULT = 0;
    const CUSTOM = 1;

    protected $fillable = [
        'plan_type', 'settings', 'type', 'status', 'model_type'
    ];


    public function setSettingsAttribute($settings){
        $this->attributes['settings'] = json_encode($settings);
    }

    public function getSettingsAttribute($settings){
        return json_decode($settings, true);
    }

    public function getModel($attributes = null)
    {
    }

}
