<?php

namespace App;

use App\SystemJob;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SystemJobDetail extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['system_job_id', 'exception_object', 'response_msg'];

    public function system_job(){
        return $this->belongsTo(SystemJob::class);
    }
}
