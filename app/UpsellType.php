<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UpsellType extends Model implements Auditable
{
    use AuditableTrait;
    const STATUS_ACTIVE=1;
    const STATUS_IN_ACTIVE=0;
    protected $fillable = ['title','user_account_id','user_id','is_user_defined','priority','status'];
    //

    public function upsells() {
        return $this->hasMany(Upsell::class);
    }

    public function  user_account(){
        return $this->belongsTo(UserAccount::class);
    }

}
