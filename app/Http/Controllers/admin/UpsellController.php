<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UpsellListing\UpsellListingResource;
use App\Http\Resources\Admin\UpsellListing\UpsellTypeResource;
use App\Upsell;
use App\UpsellOrder;
use App\UpsellType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Notification;
use Spatie\Permission\Traits\HasRole;

class UpsellController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.upsells.upsell-list');
    }

    /**
     * @return JsonResponse
     */
    public function getUpsellTypes($for_filters = false, $serve_id = 0, $user_account_id = 0)
    {
        if ($user_account_id == 'all') {
            $user_account_id = 0;
        }

        /**  For filters we need all types either active or inactive */
        $get_active_only = !filter_var($for_filters,FILTER_VALIDATE_BOOLEAN);
        $types = '';

        if ($get_active_only) {
            if ($user_account_id == 'all' || $user_account_id == 0) {
                $types = UpsellType::where('status', config('db_const.upsell_type.status.active.value'))->get(['id', 'title', 'is_user_defined']);
            } else {
                $types = UpsellType::where('status', config('db_const.upsell_type.status.active.value'))->where('user_account_id', $user_account_id)->get(['id', 'title', 'is_user_defined']);
            }
        } else {
            if ($user_account_id == 'all' || $user_account_id == 0) {
                $types = UpsellType::get(['id', 'title', 'is_user_defined']);
            } else {
                $types = UpsellType::where('user_account_id', $user_account_id)->get(['id', 'title', 'is_user_defined']);
            }
        }

        return $this->apiSuccessResponse(200, $types, 'success');
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getUpsellList(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);
            $filters = $request->filters;

            if ($filters['user_account_id'] != 'all') {
                array_push($filters['constraints'], ['user_account_id', $filters['user_account_id']]);
            }

            array_push($filters['relations'],
                'upsellType',
                'upsellPropertiesBridge',
                'upsellPropertiesBridge.propertyInfo',
                'upsellPropertiesBridge.propertyInfo.room_info'
            );

            if (!empty($filters['upsell_type'])  && $filters['upsell_type'] != 'all')
                array_push($filters['constraints'], ['upsell_type_id', $filters['upsell_type']]);

            $upsells = get_collection_by_applying_filters($filters, Upsell::class);


            return UpsellListingResource::collection($upsells);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load upsells', 501);
        }
    }

    /** Upsell Types Methods */

    /**
     *  Redirect To Types List Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function upsellType()
    {
        return view('admin.upsells.upsell-type-list');
    }

    /**
     * Get All User Defined Types By Applying Filters from Types List Page
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getUpsellTypeList(Request $request){
        try {
            $this->validate($request, ['filters' => 'required|array']);


            /** Filters Formation */
            $filters = $request->filters;

            if ($filters['user_account_id'] != 'all') {
                array_push($filters['constraints'], ['user_account_id', $filters['user_account_id']]);
            }

            array_push($filters['constraints'], ['is_user_defined',1]);
            array_push($filters['constraints'], ['status',1]);
            /** End Formation */
            $data = get_collection_by_applying_filters($filters, UpsellType::class);

            return UpsellTypeResource::collection($data);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

    public function upsellOrders()
    {
        return view('admin.upsells.upsell-order-list');
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function  getUpsellOrderList(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);
            $filters = $request->filters;

            if ($filters['user_account_id'] != 'all') {
                array_push($filters['constraints'], ['user_account_id', $filters['user_account_id']]);
            }

            array_push($filters['relations'], 'bookingInfo', 'upsellOrderDetails'); //'upsellListing', 'upsellListing.upsellType',

            $orders = get_collection_by_applying_filters($filters, UpsellOrder::class);

            return \App\Http\Resources\UpsellListing\UpsellOrderListingResource::collection($orders);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load upsell orders', 501);
        }
    }

}


