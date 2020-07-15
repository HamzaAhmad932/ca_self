<?php

namespace App\Http\Controllers;

use App\UserAccount;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function successResponse($msg, $code, $data = []) {
    	return response()->json([
    		'status' => true,
    		'status_code' => $code,
    		'message'=> $msg,
            'data' => $data,
    	]);
    }
    public function apiSuccessResponse($code, $data, $message=null){
        return response()->json([
            'status'=> true,
            'status_code'=> $code,
            'message'=> $message,
            'data'=> $data
        ]);
    }

    public function apiErrorResponse($message, $code=500, $data=null){
        return response()->json([
            'status'=> false,
            'status_code'=> $code,
            'message'=> $message,
            'data'=> $data
        ]);
    }

    public function errorResponse($msg, $code) {
    	return response()->json([
    		'status'=> false,
    		'status_code'=>$code,
    		'message'=> $msg
    	]);
    }

    public function provisionResponse($msg, $code){
        return response()->json([
            'status'=> true,
            'status_code' => $code,
            'message'=> $msg
        ]);
    }

    /**
     * Is Logged User have permission to do specific act
     * @param $permission
     */
    protected function isPermissioned($permission)
    {
        if(!auth()->user()->can($permission))
            abort(401, "Can't perform this actions");
    }

    protected function hasAccess($role)
    {
        $user = auth()->user();
        if(!$user->hasRole($role))
            abort(401, "Can't perform this actions");
    }

    protected function userHasAnyoneRole($roles)
    {
        $user = auth()->user();
        if(!$user->hasAnyRole($roles))
            abort(401, "Can't perform this actions");
    }

    /**
     * Is Logged User have permission to do specific act Check By Bulk Permissions,
     * IF any One Permission granted in array listed then user can do this act.
     * @param array $permissionArr
     */
    protected function isPermissionedBulk(array $permissionArr) {
        $can = false ;
        foreach ($permissionArr as $key => $value) {
            if (auth()->user()->can($value)) {
                $can = true;
                break;
            }
        }
        if(!$can) { abort(401, "Can't perform this actions"); }
    }

    public function isSuperClient(UserAccount $userAccount) {
        if(!$userAccount->users->first()->id === auth()->user()->id){
            abort(401, "Can't perform this actions");
        }
    }
}
