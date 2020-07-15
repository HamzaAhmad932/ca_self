<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserAccount\UserAccountCollection;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\User;
use App\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Image;
use Notification;

class UserAccountController extends Controller
{
    public function __construct(){

         $this->middleware('auth');
    }

    public function index()
    {
        return view('admin.user_accounts.user-account-list');
    }

    public function getUserAccounts(Request $request)
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
                $user_Accounts = UserAccount::withCount(
                    [
                        'properties_info as connected_properties' => function($query)
                        {
                            $query->where('status', 1);
                        },
                        'properties_info as disconnected_properties' => function($query)
                        {
                            $query->where('status', 0);
                        },
                        'user_payment_gateways as user_payment_gateways_total' => function($query)
                        {
                            $query->where('is_verified',1);
                        },
                        'pms as pms_total' => function($query)
                        {
                            $query->where('is_verified',1);
                        },
                        'team_members as total_team_members'
                    ]
                )
                    ->with('successful_transactions', 'failed_transactions', 'scheduled_transactions', 'team_members')
                    ->where('account_type', 1)
                    ->where($filterArr)
                    ->where( function( $query ) use ($searchStr) {$query->where('id','LIKE', $searchStr)
                        ->orWhere('name','LIKE', $searchStr)
                        ->orWhere('contact_number','LIKE', $searchStr)
                        ->orWhere('email','LIKE', $searchStr);})
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            } else {
                $user_Accounts = UserAccount::withCount(
                    [
                        'properties_info as connected_properties' => function($query)
                        {
                            $query->where('status', 1);
                        },
                        'properties_info as disconnected_properties' => function($query)
                        {
                            $query->where('status', 0);
                        },
                        'user_payment_gateways as user_payment_gateways_total' => function($query)
                        {
                            $query->where('is_verified',1);
                        },
                        'pms as pms_total' => function($query)
                        {
                            $query->where('is_verified',1);
                        },
                        'team_members as total_team_members'
                    ]
                )
                    ->with('successful_transactions', 'failed_transactions', 'scheduled_transactions', 'team_members')
                    ->where('account_type', 1)
                    ->where($filterArr)
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            }

            return new UserAccountCollection($user_Accounts);

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function verifyAPIKey(Request $request)
    {
        try {

            $user_account = UserAccount::find($request->id);
            $pms = new PMS($user_account);

            $options = new PmsOptions();
            $options->requestType = PmsOptions::REQUEST_TYPE_JSON;

            $properties = $pms->fetch_properties($options);

            if (!empty($properties[0]->id) && $properties[0]->propertyKey != -1) {

                return $this->apiSuccessResponse(200, [], 'User account is verify');

            } elseif (!empty($properties[0]->id) && $properties[0]->propertyKey == -1) {

                return $this->apiSuccessResponse(404, [], 'CA IP is not white listed');

            }

        } catch (PmsExceptions $e) {
            return $this->apiErrorResponse($e->getCADefineMessage(), 404);
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }

        return $this->apiSuccessResponse(404, [], 'User account is not verify');
    }

    public function users($user_account_id)
    {
        return view('admin.user_accounts.user-list', ['user_account_id' => $user_account_id]);
    }

    public function getUsers(Request $request)
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
                $users = User::where('user_account_id', $request['user_account_id'])
                    ->where($filterArr)
                    ->where( function( $query ) use ($searchStr) {$query->where('id','LIKE', $searchStr)
                        ->orWhere('name','LIKE', $searchStr)
                        ->orWhere('phone','LIKE', $searchStr)
                        ->orWhere('email','LIKE', $searchStr);})
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            } else {
                $users = User::where('user_account_id', $request['user_account_id'])
                    ->where($filterArr)
                    ->orderBy($sortColumn, $sortOrder)
                    ->paginate($per_page);
            }

            return $this->apiSuccessResponse(200, $users, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function adminViewClientDashboard(Request $request)
    {
        $user = User::where('user_account_id', $request->id)->first();
        Auth::login($user);
        return redirect(route('adminUserAccounts'));
    }

}


