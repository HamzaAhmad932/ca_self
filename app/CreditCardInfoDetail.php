<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCardInfoDetail extends Model {
    
    
    protected $fillable = ['cc_info_id', 'user_id', 'user_account_id', 'message', 'response', 'status'];
    
    
}
