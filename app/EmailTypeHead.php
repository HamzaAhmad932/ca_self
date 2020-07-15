<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailTypeHead
 * @package App
 * @property $to_admin
 * @property $to_client
 * @property $to_guest
 * @property $type
 * @property $status
 * @property $defaultContents
 * @property $customContents
 */

class EmailTypeHead extends Model
{
    protected $fillable = ['title', 'type', 'icon', 'to_admin', 'to_client' ,'to_guest', 'customizable','status'];

    public function defaultContents(){
        return $this->hasMany(EmailDefaultContent::class);
    }
    public function customContents(){
        return $this->hasMany(EmailCustomContent::class);
    }
    
}
