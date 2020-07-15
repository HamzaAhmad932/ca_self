<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRole;

class AdminController extends Controller
{
    public function __construct(){

        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.admins.admin-list');
    }

    public function getAdmins(Request $request)
    {
        try {
            $filterArr = [];
            $sortColumn  = 'name';
            $sortOrder   = 'Asc';

            if ($request->has('filters')) {
                if (isset($request->filters['search']) && ($request->filters['search'] != null))
                    $searchStr = "%{$request->filters['search']}%";
                if (isset($request->filters['sortColumn']) && ($request->filters['sortColumn'] != null))
                    $sortColumn = $request->filters['sortColumn'];
                if (isset($request->filters['sortOrder']) && ($request->filters['sortOrder'] != null))
                    $sortOrder = $request->filters['sortOrder'];
                if (isset($request->filters['per_page']) && !empty($request->filters['per_page'])){
                    $per_page = $request->filters['per_page'];
                } else {
                    $per_page = 10;
                }
            } else {
                $per_page=10;
            }

            if (isset($searchStr)) {
                $admins = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('users.*')
                    ->whereNotIn('users.id', [auth()->user()->id])
                    ->where('users.deleted_at','=', Null)
                    ->whereIn('roles.name', ['SuperAdmin','Admin'])
                    ->where($filterArr)
                    ->where( function( $query ) use ($searchStr) {
                        $query->where('users.id','LIKE', $searchStr)
                        ->orWhere('users.name','LIKE', $searchStr)
                        ->orWhere('users.phone','LIKE', $searchStr)
                        ->orWhere('users.email','LIKE', $searchStr);
                    })
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            } else {
                $admins = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('users.*')
                    ->whereNotIn('users.id', [auth()->user()->id])
                    ->where('users.deleted_at','=', Null)
                    ->whereIn('roles.name', ['SuperAdmin','Admin'])
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            }


            $loged_in_user_access = false;
            $loged_in_user = auth()->user()->user_account;

            if ($loged_in_user->account_type == 4 && Auth::user()->hasRole('SuperAdmin')) {
                $loged_in_user_access = true;
            }

            return $this->apiSuccessResponse(200, ['admins' => $admins, 'loged_in_user_access' => $loged_in_user_access], 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function getAdminRoles()
    {
        try {
            $adminRole = Role::where('guard_name', 'admin')->get();
            return $this->apiSuccessResponse(200, $adminRole, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
                'phone' => 'required|max:25',
                'admin_role' => 'required'
            ])->validate();

            $parent_id = auth()->user()->id;
            $company_id = auth()->user()->user_account_id;

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'password' => Hash::make($request->get('password')),
                'user_account_id' => $company_id,
                'parent_user_id' => $parent_id,

            ]);

            $user->guard_name='admin';
            $user->assignRole($request->get('admin_role'));
            $user->givePermissionTo('full');
            $user->notify(new \App\Notifications\Users\ActivateEmailNotification($user));

            return $this->apiSuccessResponse(200, [], 'User Added Successfully');
        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function changeAdminStatus(Request $request)
    {
        try {
            if (auth::user()->hasRole('SuperAdmin')) {
                $admin_status = User::find($request->user_id);
                if (!empty($admin_status)) {
                    $admin_status->status = $request->status;

                    if ($admin_status->save()) {
                        return $this->apiSuccessResponse(200, [], 'success');
                    } else {
                        return $this->apiErrorResponse('error', 404);
                    }

                } else {
                    return $this->apiErrorResponse('Admin does not found', 404);
                }
            } else {
                return $this->apiErrorResponse('You can not perform this action', 404);
            }
        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function destroy($user_id)
    {
        try {
            if (auth::user()->hasRole('SuperAdmin')) {

                if (User::find($user_id)->delete()) {
                    return $this->apiSuccessResponse(200, [], 'success');
                } else {
                    return $this->apiErrorResponse('some error', 404);
                }

            } else {
                return $this->apiErrorResponse('You can not perform this action', 404);
            }
        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function show($user_id)
    {
        try {
            $user = User::where('id', $user_id)->first();
            dd($user);
            if (!empty($user)) {
                return $this->apiSuccessResponse(200, $user, 'success');
            } else {
                return $this->apiErrorResponse('User does not fiend', 404);
            }
        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }

    }

}


