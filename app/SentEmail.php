<?php

namespace App;

use App\CreditCardAuthorization;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;

class SentEmail extends Model implements Auditable {
    use AuditableTrait;
    use LogsActivity;

    protected $table = 'sent_emails';
    protected $fillable = ['booking_info_id', 'model_id', 'model_class', 'email_subject', 'email_type', 'sent_to', 'encoded_data'];


    public function booking_info(){
        return $this->belongsTo('App\BookingInfo');
    }
}