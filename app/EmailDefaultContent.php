<?php

namespace App;


use App\Repositories\EmailComponent\EmailContent;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailDefaultContent
 * @package App
 * @property $email_receiver_id
 * @property $content
 */
class EmailDefaultContent extends Model
{
    protected $fillable = ['email_type_head_id', 'email_receiver_id', 'content'];


    public function getContentAttribute($value) {
        return new EmailContent($value);
    }

    public function setContentAttribute($value) {
         $parser = new EmailContent();
        $this->attributes['content'] = $parser->toJSON($value);
    }

    public function typeHead(){
        return $this->belongsTo(EmailTypeHead::class);
    }

    public function receiver(){
        return $this->belongsTo(EmailReceiver::class, 'email_receiver_id', 'receiver_id');
    }
}
