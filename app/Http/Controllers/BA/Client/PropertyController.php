<?php

namespace App\Http\Controllers\BA\Client;

use App\Jobs\SyncProperties\BASyncPropertyJob;
use App\PropertyInfo;
use App\Repositories\Properties\Properties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PropertyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function connectDisconnectProperty(Request $request) {

        $this->isPermissionedBulk(['properties', 'accountSetup']);

        $validator = Validator::make($request->all(), [
            'propertyInfoId' => 'required|integer',
            'status' => 'required|bool',]);

        if ($validator->passes()) {
            $request_ = new Request(['propertyInfoIds' => [$request->propertyInfoId], 'status'=> $request->status]);
            return $this->bulkConnectDisconnectProperties($request_);
        } else {
            return $this->errorResponse($validator->errors()->first(),422);
        }
    }
    /**
     * PMS Wise
     * @param Request $request
     * @return array|JsonResponse
     */
    public function bulkConnectDisconnectProperties(Request $request) {

        $this->isPermissionedBulk(['properties', 'accountSetup']); //having Permission to do this act

        $validator = Validator::make($request->all(),
            ['propertyInfoIds' => 'required|array',
                'status' => 'required|bool',]);
        if ($validator->passes()) {
            $repoProperties = new Properties(auth()->user()->user_account_id);
            return $repoProperties->bulkConnectDisconnectProperties($request->status, $request->propertyInfoIds);
        } else {
            return $this->errorResponse($validator->errors()->first(),422);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function syncPropertyInfo(Request $request) {

        $this->isPermissioned('properties'); //having Permission to do this act

        $validator = Validator::make($request->all(), ['propertyInfoId' => 'required',]);
        if ($validator->fails())
            return $this->apiErrorResponse($validator->errors()->first(), 422);

        try {
            BASyncPropertyJob::dispatchNow(auth()->user()->user_account_id);
        } catch (\Exception $exception) {
            return $this->apiErrorResponse($exception->getMessage(), 422);
        }


        return $this->getPropertyInfoData($request->propertyInfoId, true);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertyInfo(Request $request) {
        $this->isPermissioned('properties');
        return $this->getPropertyInfoData($request->propertyInfoId);
    }

    /**
     * @param $propertyInfoId
     * @param bool $check_prop_key
     * @return JsonResponse
     */
    private function getPropertyInfoData($propertyInfoId, $check_prop_key = false){
        $propertyInfo = PropertyInfo::where('id', $propertyInfoId)->where('user_Account_id', auth()->user()->user_account_id)->select('property_key', 'user_account_id', 'time_zone', 'currency_code', 'name', 'logo', 'property_email', 'longitude', 'latitude')->first();
        if (!is_null($propertyInfo)){
            $check_property_image = checkImageExists( $propertyInfo->logo, $propertyInfo->name, config('db_const.logos_directory.property.value') );
            $propertyInfo->property_initial = $check_property_image['property_initial'];
            $propertyInfo->property_image = $check_property_image['property_image'];

            if ($check_prop_key) {
                if (!empty($propertyInfo->property_key)) {
                    return $this->apiSuccessResponse(200, $propertyInfo->toArray(), 'success');
                } else {
                    return $this->apiErrorResponse('Property key is missing', 422);
                }
            } else {
                return $this->apiSuccessResponse(200, $propertyInfo->toArray(), 'success');
            }
        }
        else{
            return $this->apiErrorResponse('PropertyInfo ID not Valid', 422);
        }

    }

    /**
     * PMS Wise
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getProperties(Request $request){

        $this->isPermissioned('properties'); //Having Permission to perform this act

        try {

            if (!empty($request->sync))
                BASyncPropertyJob::dispatchNow(auth()->user()->user_account_id);

            $filter = datatable_query_filter();

            array_push($filter['constraints'], ['user_account_id', auth()->user()->user_account_id], ['available_on_pms', 1]);
            array_push($filter['search']['searchInColumn'], 'name', 'pms_property_id', 'address', 'currency_code');


            $filter['page'] = $request->page ?? 1;
            $filter['recordsPerPage'] = $request->filters['per_page'] ?? 10;
            $filter['sort'] = [
                'sortOrder' => $request->filters['sortOrder'] ?? 'ASC',
                'sortColumn' => $request->filters['sortColumn'] ?? 'id'
            ];

            if (!empty($request->filters['search']))
                $filter['search']['searchStr'] =  $request->filters['search'];

            if (!empty($request->filters['city']) && ($request->filters['city'] != 'all'))
                array_push($filter['constraints'], ['city', $request->filters['city']]);

            return $this->apiSuccessResponse(
                200,
                get_collection_by_applying_filters($filter, PropertyInfo::class),
                'success'
            );

        }  catch (\Exception $exception) {
            return $this->apiErrorResponse($exception->getMessage());
        }
    }
}
