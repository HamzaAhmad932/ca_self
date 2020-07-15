<?php
namespace App;


use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

/**
 * @property UserAccount user_account
 */
class User extends Authenticatable implements Auditable, MustVerifyEmail
{
    
    use AuditableTrait;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    use LogsActivity;
    /**
     * @property array|null messages
     * @property BookingInfo collection|null bookings_info
     * @property PropertyInfo collection|null properties_info
     * @property UserBookingSource collection|null user_bookings_source
     * @property ErrorLog collection|null error_logs 
     */

    //protected static $logAttributes = ['name'];
    protected $dates = ['deleted_at'];
    public $guard_name = 'client';
    protected static $logFillable = true;
    protected static $logName = 'UserRelatedLog';

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'username', 'user_account_id', 'parent_user_id',
        'is_activated','city','state','country'
    ];

    protected $casts = [
        'is_activated' => 'boolean'
    ];

    /**
    * The attributes that should be hidden for arrays.
    *	
    * @var array

    */
    protected $hidden = [
        'password', 'remember_token',
    ];

    const ROLE_ADMINISTRATOR   = 'Administrator';
    const ROLE_MANAGER         = 'Manager';
    const ROLE_MEMBER          = 'Member';

    public function user_account() {
        return $this->belongsTo('App\UserAccount');
    }
    public function messages(){

        return $this->hasMany('App\GuestCommunication');
    }

    public function routeNotificationForTwilio() {
        return $this->phone;
    }

    public function bookings_info()
    {
        return $this->hasMany('App\BookingInfo');
    }
    public function properties_info(){
        
        return $this->hasMany('App\PropertyInfo');
    }
    public function user_bookings_source(){

        return $this->hasMany('App\UserBookingSource');
    }
    public function error_logs(){

        return $this->hasMany('App\ErrorLog');
    }

    public function getAllPermissionsAttribute() {
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if (Auth::user()->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }
        return $permissions;
    }

    public function fetchBookingSettings()
    {
        return $this->hasMany('App\FetchBookingSetting');
    }

    public function getNameAttribute($value) {
        return strip_tags(handleSpecialCharacters($value));
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] =  strip_tags(handleSpecialCharacters($value));
    }

    /**
     * Sends the password reset notification.
     *
     * @param  string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        //send email to client
        event(new EmailEvent(config('db_const.emails.heads.password_reset.type'), $this->id, [ 'token' => $token ] ));
    }
}
