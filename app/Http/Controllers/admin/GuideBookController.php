<?php

namespace App\Http\Controllers\admin;

use App\GuideBook;
use App\GuideBookType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GuideBooks\GuideBooksListingResource;
use App\Http\Resources\General\GuideBooks\GuidBookTypesResource;
use App\PropertyInfo;
use Illuminate\Http\Request;
use Notification;
use Spatie\Permission\Traits\HasRole;

class GuideBookController extends Controller
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
        return view('admin.guide_books.guide-book-list');
    }

    public function getAllGuideBookTypes(Request $request)
    {
        $types = '';
        if ($request->user_account_id != 'all') {
            $types = GuideBookType::Where('user_account_id', $request->user_account_id)
                ->get(['id', 'title', 'is_user_defined']);
        } else {
            $types = GuideBookType::get(['id', 'title', 'is_user_defined']);
        }


        return $this->apiSuccessResponse(200, $types, 'success');
    }

    public function getAllGuideBooks(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);

            /** Filters Formation */
            $filters = $request->filters;
            $where = [];

            if (!empty($filters['type_id']) && $filters['type_id'] != "all") {
                array_push($filters['constraints'],['guide_book_type_id',$filters['type_id']]);
            }

            if (!empty($filters['user_account_id']) && $filters['user_account_id'] != "all") {
                $where=[['user_account_id',$filters['user_account_id']]];
            }

            if (!empty($filters['property_info_id'])  && $filters['property_info_id'] != 'all') {

                if (!empty($filters['room_info_id'])  && $filters['room_info_id'] != 'all') {
                    $ids = get_related_records(['id'],PropertyInfo::GUIDE_BOOK,$where,$filters['property_info_id'],$filters['room_info_id']);
                    array_push($filters['whereHas'],array('col'=>'id','values'=>$ids->toArray()));
                } else {
                    $ids = get_related_records(['id'],PropertyInfo::GUIDE_BOOK,$where,$filters['property_info_id']);
                    array_push($filters['whereHas'],array('col'=>'id','values'=>$ids->toArray()));
                }

            }

            if (!empty($filters['user_account_id']) && $filters['user_account_id'] != "all") {
                array_push($filters['constraints'], ['user_account_id',$filters['user_account_id']]);
            }

            array_push($filters['relations'],'guideBookType');

            /** End Formation */
            $guideBook = get_collection_by_applying_filters($filters, GuideBook::class);

            return GuideBooksListingResource::collection($guideBook);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

    /** Guide Book Types Methods */

    /**
     *  Redirect To Types List Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function guideBooksType()
    {
        return view('admin.guide_books.guide-book-type-list');
    }

    /**
     * Get All User Defined Types By Applying Filters from Types List Page
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getGuideBookTypesList(Request $request){
        try {
            $this->validate($request, ['filters' => 'required|array']);


            /** Filters Formation */
            $filters = $request->filters;
            array_push($filters['constraints'], ['is_user_defined',1]);
            /** End Formation */
            $guideBook = get_collection_by_applying_filters($filters, GuideBookType::class);

            return GuidBookTypesResource::collection($guideBook);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

}


