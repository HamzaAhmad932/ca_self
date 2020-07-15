<?php
/**
 * Created by PhpStorm.
 * User: Usman
 * Date: 11/23/18
 * Time: 2:50 PM
 */

namespace App\Repositories\Admin;

use App\User;
use Notification;
use App\BookingInfo;
use App\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Events\PaymentAttemptEvent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\RegistersUsers;

class Admin
{

    public function ShowAdminList()
    {

        $id = auth()->user()->id;

        // todo permission check show user or not
        // also check permission in view

        $team = User::whereHas('roles', function ($query) {
            return $query->whereIn('name', ['Admin']);
        })->paginate(5);

        $adminPermission = Permission::where('guard_name', 'admin')->get();
        $adminRole = Role::where('guard_name', 'admin')->get();
        $company = UserAccount::all();
        $data = array(
            'teamList' => $team,
            'adminPermission' => $adminPermission,
            'adminRole' => $adminRole,
            'company' => $company

        );

        return $data;

    }



    public function UpdateStatus($id, $st){

        $adminStatus = User::find($id);



        $adminStatus->status = $st;

        $adminStatus->save();

        $res = array('status' => $st, );

        if($st == config('db_const.user.status.active.value')){
            $sts = config('db_const.user.status.active.label');
        }elseif ($st == config('db_const.user.status.deactive.value')){
            $sts = config('db_const.user.status.deactive.label');
        }
        $adminStatus->notify(new MemberStatusNotification($sts));

        return $res;
    }
}