<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PmsRequestLogs extends Model
{
    protected $fillable = ['user_account_id', 'user_name', 'pms_form_id', 'pms_function', 'meta_data'];
}
