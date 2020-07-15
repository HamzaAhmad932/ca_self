<?php

namespace App\Http\Controllers\v2\client;

use App\GuideBook;
use App\GuideBookType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuideBooksStoreRequest;
use App\Http\Requests\GuideBookTypeRequest;
use App\Http\Resources\General\GuideBooks\GuidBookTypesResource;
use App\Http\Resources\General\GuideBooks\GuideBooksListingResource;
use App\PropertyInfo;
use App\Repositories\GuideBooks\GuideBooksRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuideBookController extends Controller
{
    public const GUIDE_BOOKS_ROUTE='v2.client.guideBooks';
    private  $guideBooks;

    /**
     * GuideBookController constructor.
     * @param GuideBooksRepository $guideBooks
     */
    public function __construct(GuideBooksRepository $guideBooks){
        $this->guideBooks=$guideBooks;
    }

    public function index()
    {
        return view(self::GUIDE_BOOKS_ROUTE .'.guide-books-list');
    }

    public function getGuideBookTypes(){
        $types = $this->guideBooks->getTypes(auth()->user()->user_account_id);
        return $this->apiSuccessResponse(200, $types, 'success');
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view(self::GUIDE_BOOKS_ROUTE.'.guide-books-add');
    }


    /**
     * @param GuideBooksStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(GuideBooksStoreRequest $request){
        try {
            $validated=$request->validated();
            $new=$this->guideBooks->create($validated);
            $response=$this->apiSuccessResponse('200',"","Guide Book Added Successfully");
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public  function getOldData(Request $request){
        $term=$this->guideBooks->getOne($request->all()['serve_id']);
        return $this->apiSuccessResponse('200',$term);
    }


    /**
     * @param GuideBooksStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(GuideBooksStoreRequest $request){
        try {
            $validated=$request->validated();
            $this->guideBooks->update($validated);
            $response=$this->apiSuccessResponse('200',"","Guide Book Updated");
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function  getAll(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);


            /** Filters Formation */
            $filters = $request->filters;

            if(!empty($filters['type_id']) && $filters['type_id'] != "all"){
                array_push($filters['constraints'],['guide_book_type_id',$filters['type_id']]);

            }
            if(!empty($filters['property_info_id'])  && $filters['property_info_id'] != 'all'){

                $where=[['user_account_id',auth()->user()->user_account_id]];

                if (!empty($filters['room_info_id'])  && $filters['room_info_id'] != 'all'){
                    $ids=get_related_records(['id'],PropertyInfo::GUIDE_BOOK,$where,$filters['property_info_id'],$filters['room_info_id']);
                    array_push($filters['whereHas'],array('col'=>'id','values'=>$ids->toArray()));
                }else{
                    $ids=get_related_records(['id'],PropertyInfo::GUIDE_BOOK,$where,$filters['property_info_id']);
                    array_push($filters['whereHas'],array('col'=>'id','values'=>$ids->toArray()));
                }

            }
            array_push($filters['constraints'], ['user_account_id',auth()->user()->user_account_id]);
            array_push($filters['relations'],'guideBookType');

            /** End Formation */
            $guideBook = get_collection_by_applying_filters($filters, GuideBook::class);
            return GuideBooksListingResource::collection($guideBook);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public  function updateStatus(Request $request)
    {
        try {
            $valid_ids = auth()->user()->user_account->guideBooks->pluck('id')->toArray();
            $this->validate($request, [
                'id' => ['required', Rule::in($valid_ids)],
                'updateWhat' => ['required'],
                'updateWith' => 'required|bool',
            ]);

            if ($this->guideBooks->updateStatus($request->id, [$request->updateWhat=>$request->updateWith]))
                return $this->apiSuccessResponse(200,"", 'Updated Successfully');
            else
                return $this->apiErrorResponse('Request not Valid', 501);
        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to update', 501);
        }
    }

    /** Guide Book Types Methods */

    /**
     *  Redirect To Types List Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewGuideBookTypesList()
    {
        return view(self::GUIDE_BOOKS_ROUTE .'.types.guide-books-types-list');
    }

    /**
     * Redirect To Create Type Form Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createType(){
        return view(self::GUIDE_BOOKS_ROUTE .'.types.guide-books-types-add');
    }

    /**
     * Get A Single Type Data Against given Type_Id.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTypeOldData(Request $request){
        $term=$this->guideBooks->getOneType($request->all()['serve_id']);
        return $this->apiSuccessResponse('200',$term);
    }

    /**
     * Delete Record
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteType(Request $request){
        try {
            $term=$this->guideBooks->deleteType($request->all()['serve_id']);
            $response=$this->apiSuccessResponse('200',"","Deleted Successfully");
        }catch (\Exception $e){
            dump($e->getMessage());
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }

        return $response;

    }

    /**
     * Update Record in Database
     * @param GuideBookTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function  updateType(GuideBookTypeRequest $request){
        try {
            $validated=$request->validated();
            $this->guideBooks->updateType($validated);
            $response=$this->apiSuccessResponse('200',"","Updated Successfully");
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;

    }

    /**
     * Add Type Record To Database
     * @param GuideBookTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveType(GuideBookTypeRequest $request){
        try {
            $validated=$request->validated();
            $new=$this->guideBooks->createType($validated);
            $response=$this->apiSuccessResponse('200',"","Added Successfully");
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;

    }

    /**
     * Get All User Defined Types By Applying Filters from Types List Page
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAllTypes(Request $request){
        try {
            $this->validate($request, ['filters' => 'required|array']);


            /** Filters Formation */
            $filters = $request->filters;
            array_push($filters['constraints'], ['user_account_id',auth()->user()->user_account_id]);
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
