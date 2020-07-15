<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralPreferencesForm extends Model
{
	use SoftDeletes;

    const EMAIL_TO_GUEST_OPTION = 1;
    const ENABLE_STATUS = 1;

    protected $fillable = [
        'name',
        'description',
        'status'
    ];
}
