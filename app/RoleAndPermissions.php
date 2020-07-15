<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RoleAndPermissions extends Model implements Auditable
{
    use AuditableTrait;
    
    public static $adminRoles = ['SuperAdmin','Admin'];
    public static $userRoles = ['Administrator','Manager', 'TeamMember'];
  
    public static $adminPermission = ['full','editProperty','deleteProperty','viewProperty','syncProperties',
                                      'chargeBooking',  
                                      'editBooking','deleteBooking','viewBooking', 
                                      'editSetting','deleteSetting','viewSetting',
                                      'editBookingSource','deleteBookingSource','viewBookingSource',
                                      'editUser','deleteUser','viewUser',
                                      'editPMS','deletePMS','viewPMS',
                                      'editTransaction','deleteTransaction','viewTransaction'];

    public static $userPermission = ['full client', 'bookings', 'properties', 'guestExperience', 'preferences', 'accountSetup'];

    public static $designV2ClientPermissions = ['bookings', 'properties', 'guestExperience', 'preferences', 'accountSetup'];
}
