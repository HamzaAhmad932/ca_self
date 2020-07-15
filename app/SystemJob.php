<?php

namespace App;

use App\Http\Controllers\ApiPmsBookingAutomation;
use  App\SystemJobDetail;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use OwenIt\Auditing\Auditable as AuditableTrait;


class SystemJob extends Model  implements Auditable
{
    use AuditableTrait;

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_VOID = 2;
    const TOTAL_ATTEMPTS_PMS_PREFERENCES = 3;
    const TOTAL_ATTEMPTS_PMS_LIMIT_EXCEED = 3;
    /**
     * Description and Model Name Unique for Preference System Jobs
     */
    const PMS_PREFERENCES_DESCRIPTION = 'update pms preferences';
    const PMS_PREFERENCES_MODEL_NAME = UserPreference::class;
    const PMS_PREFERENCES_NEXT_ATTEMPT_AFTER_MINTS = 5;

    const PMS_LIMIT_EXCEED_NEW_BOOKING_DESCRIPTION = 'Respond to New Booking API Request';
    const PMS_LIMIT_EXCEED_NEW_BOOKING_MODEL_NAME = ApiPmsBookingAutomation::class;

    const PMS_LIMIT_EXCEED_MODIFY_BOOKING_DESCRIPTION = 'Respond to Modify Booking API Request';
    const PMS_LIMIT_EXCEED_MODIFY_BOOKING_MODEL_NAME = ApiRequestDetail::class;

    const PMS_LIMIT_EXCEED_CANCEL_BOOKING_DESCRIPTION = 'Respond to Cancel Booking API Request';
    const PMS_LIMIT_EXCEED_CANCEL_BOOKING_MODEL_NAME = ApiRequestDetail::class;

    const PMS_LIMIT_EXCEED_GET_CARD_DESCRIPTION = 'BA Get Card API Call';
    const PMS_LIMIT_EXCEED_GET_CARD_BOOKING_MODEL_NAME = ApiRequestDetail::class;


    const PMS_LIMIT_EXCEED_NEXT_ATTEMPT_AFTER_MINTS = 5;

    protected $fillable = ['user_account_id', 'booking_info_id', 'pms_booking_id', 'pms_property_id', 'model_name', 'model_id', 'dispatch_description', 'due_date', 'json_data', 'attempts', 'status', 'lets_process'];

    public function system_job_details()
    {
        return $this->hasMany(SystemJobDetail::class);
    }

    public function user_account() {
        return $this->belongsTo('App\UserAccount');
    }
}
