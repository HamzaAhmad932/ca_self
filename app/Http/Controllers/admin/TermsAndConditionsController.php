<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TermsAndConditions\TermsAndConditionsListingResource;
use App\PropertyInfo;
use App\RoomInfo;
use App\TermsAndCondition;
use Illuminate\Http\Request;
use Notification;
use Spatie\Permission\Traits\HasRole;

class TermsAndConditionsController extends Controller
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
        return view('admin.terms_and_conditions.term-and-condition-list');
    }

    public function getTermsAndConditions(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);

            /** Filters Formation */
            $filters = $request->filters;
            $where = [];

            if (!empty($filters['user_account_id']) && $filters['user_account_id'] != 'all') {
                $where = [['user_account_id', $filters['user_account_id']]];
            }

            if (!empty($filters['property_info_id']) && $filters['property_info_id'] != 'all') {
                if (!empty($filters['room_info_id']) && $filters['room_info_id'] != 'all') {
                    $ids = get_related_records(['id'], PropertyInfo::TAC, $where, $filters['property_info_id'], $filters['room_info_id']);
                    array_push($filters['whereHas'], array('col' => 'id', 'values' => $ids->toArray()));
                } else {
                    $ids = get_related_records(['id'], PropertyInfo::TAC, $where, $filters['property_info_id']);
                    array_push($filters['whereHas'], array('col' => 'id', 'values' => $ids->toArray()));
                }
            }

            if (!empty($filters['user_account_id']) && $filters['user_account_id'] != 'all') {
                array_push($filters['constraints'], ['user_account_id', $filters['user_account_id']]);
            }

            /** End Formation */

            $tac = get_collection_by_applying_filters($filters, TermsAndCondition::class);


            return TermsAndConditionsListingResource::collection($tac);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

    public function getPropertiesNames(Request $request)
    {
        if ($request->user_account_id != 'all') {
            return PropertyInfo::where('user_account_id', $request->user_account_id)
                ->where('available_on_pms', 1)->select('pms_property_id','id', 'name')->get();
        } else {
            return PropertyInfo::where('available_on_pms', 1)->select('pms_property_id','id', 'name')->get();
        }
    }

    /**
     * This Will Return Room_infos against Selected Property
     * @param Request $request
     * @return array
     */
    public function getRoomInfo(Request $request)
    {
        if(!empty($request->property_info_id) && $request->property_info_id!="all"){
            $data=RoomInfo::where('property_info_id',$request->property_info_id)->select('id','name')->get();
        }else{
            $data=[];
        }
        return $data;
    }

}


