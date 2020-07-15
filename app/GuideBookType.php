<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class GuideBookType extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    protected $fillable = ['title','icon','user_account_id','user_id','is_user_defined','priority'];

    public  function  guideBooks(){
        return $this->hasMany(GuideBook::class);
    }

    public function  user_account(){
        return $this->belongsTo(UserAccount::class);
    }

    public function delete()
    {
        $this->guideBooks()->delete();
        return parent::delete();
    }
}
