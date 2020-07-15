<?php

namespace App\Http\Controllers\BA\Client;

use App\Jobs\SyncProperties\BASyncPropertyJob;
use App\PropertyInfo;
use App\Repositories\Properties\Properties;
use App\System\PMS\exceptions\PmsExceptions;
use App\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PmsIntegrationController extends Controller
{
    /**
     * Return Properties List By Applying Datatable Filters and search
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesListForMasterSettings(Request $request)
    {
        $this->isPermissioned('accountSetup'); //having Permission to do this act

        try {

            $validator = Validator::make($request->all(), ['filters' => 'required|array']);
            if ($validator->passes()) {
                $filters = $request->filters;
                $filters['page'] = isset($request->page) ? $request->page : 1;
                $filters['constraints'][] = ['user_account_id', '=', auth()->user()->user_account_id];

                $userAccount = UserAccount::with('properties_info')->where('id',
                    auth()->user()->user_account_id)->first();
                $userProperties = $userAccount->properties_info;

                try {
                    /**If New User Sync Properties Form PMS*/
                    if ($userProperties->count() == 0 || !empty($request->sync)) {
                        // Sync Properties
                        BASyncPropertyJob::dispatchNow($userAccount->id);
                    }
                } catch (\Exception $exception) {

                    return $this->apiErrorResponse($exception->getMessage());
                }


                $properties = get_collection_by_applying_filters($filters, PropertyInfo::class);

                if (empty($userAccount->integration_completed_on) && !empty($properties->count())) {
                    $userAccount->update(
                        [
                            'integration_completed_on' => now()->toDateTimeString(),
                            'status' => config('db_const.user_account.status.active.value')
                        ]
                    );

                    createDefaultBillingCustomer($userAccount);
                }

                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'success',
                    'data' => $properties,
                    'total_before_sync' => $userProperties->count()
                ]);

            } else {
                return $this->apiErrorResponse(ucwords($validator->errors()->first()), 422, $validator->errors());
            }
        } catch (PmsExceptions $e) {
            report($e);
            return $this->apiErrorResponse($e->getMessage(), $e->getPMSCode());
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 500);
        }
    }


    /** Connect | Disconnect All Properties In One XML Request
     * @param Request $request
     * @return array
     */
    public function bulkConnectDisconnectProperties(Request $request)
    {

        $this->isPermissioned('accountSetup');
        $userAccount = auth()->user()->user_account;
        $repoProperties = new Properties($userAccount->id);
        $status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN);
        return $repoProperties->bulkConnectDisconnectProperties($status, $userAccount->properties_info->pluck('id')->toArray());
    }

}
