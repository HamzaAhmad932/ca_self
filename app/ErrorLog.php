<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\Relation;

class ErrorLog extends Model implements Auditable
{
    
    use AuditableTrait;
	 /**
     * Get all of the owning commentable models.
     */
    public function errorable()
    {
        return $this->morphTo();
    }

     public function user()
    
    {


        return $this->belongsTo('App\User');
    }

    public function user_account()
    
    {

        //return $this->belongsTo('App\User_account', 'user_account_id','id');


        return $this->belongsTo('App\UserAccount');
    }

    public function error_code()
    
    {

        //return $this->belongsTo('App\User_account', 'user_account_id','id');


        return $this->belongsTo('App\ErrorCode');
    }

    


}
