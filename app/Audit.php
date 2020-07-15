<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table = 'audits';

    public  function getOldValuesAttribute($old_values){
        return json_decode($old_values);
    }
    public  function getNewValuesAttribute($new_values){
        return json_decode($new_values);
    }

    public function property_info(){
        return $this->belongsTo(PropertyInfo::class, 'auditable_id', 'id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creditCardInfo(){
        return $this->belongsTo(CreditCardInfo::class);
    }
}
