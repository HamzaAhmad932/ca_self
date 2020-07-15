<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailReceiver extends Model
{
    public static $ADMIN = 1;
    public static $CLIENT = 2;
    public static $GUEST = 3;

    protected $fillable = ['name','receiver_id'];

    public function defaultContents(){
        return $this->hasMany(EmailDefaultContent::class, 'email_receiver_id', 'receiver_id');
    }

    public function customContents(){
        return $this->hasMany(EmailCustomContent::class, 'email_receiver_id', 'receiver_id');
    }
}
