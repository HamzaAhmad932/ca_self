<?php

namespace App\Http\Controllers\v2\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\TermsAndConditionsStoreRequest;
use App\Http\Resources\General\TermsAndConditions\TermsAndConditionsListingResource;
use App\PropertyInfo;
use App\Repositories\TermsAndConditions\TermsAndConditionsRepository;
use App\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermsAndConditionsController extends Controller
{
    public const TAC_ROUTE='v2.client.terms_and_conditions';
    private  $terms;


    /**
     * TermsAndConditionsController constructor.
     * @param TermsAndConditionsRepository $tac
     */
    public function __construct(TermsAndConditionsRepository $tac)
    {
        $this->terms=$tac;
    }

    public function index()
    {
        return view(self::TAC_ROUTE.'.terms-and-conditions-list');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view(self::TAC_ROUTE.'.terms-and-conditions-add');
    }

    /**
     * @param TermsAndConditionsStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(TermsAndConditionsStoreRequest $request){
        try {
            $validated=$request->validated();
            $newTerm=$this->terms->create($validated);
            $response=$this->apiSuccessResponse('200',"","New Terms and Conditions Added");
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
        $term=$this->terms->getOne($request->all()['serve_id']);
        return $this->apiSuccessResponse('200',$term);
    }

    /**
     * @param TermsAndConditionsStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TermsAndConditionsStoreRequest $request){
        try {
            $validated=$request->validated();
            $this->terms->update($validated);
            $response=$this->apiSuccessResponse('200',"","Terms and Conditions Updated");
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
            if (!empty($filters['property_info_id']) && $filters['property_info_id'] != 'all') {
                if (!empty($filters['room_info_id']) && $filters['room_info_id'] != 'all') {
                    $where = [['user_account_id', auth()->user()->user_account_id]];
                    $ids = get_related_records(['id'], PropertyInfo::TAC, $where, $filters['property_info_id'], $filters['room_info_id']);
                    array_push($filters['whereHas'], array('col' => 'id', 'values' => $ids->toArray()));
                } else {
                    $where = [['user_account_id', auth()->user()->user_account_id]];
                    $ids = get_related_records(['id'], PropertyInfo::TAC, $where, $filters['property_info_id']);
                    array_push($filters['whereHas'], array('col' => 'id', 'values' => $ids->toArray()));
                }
            }

            array_push($filters['constraints'], ['user_account_id', auth()->user()->user_account_id]);

            /** End Formation */

            $tac = get_collection_by_applying_filters($filters, TermsAndCondition::class);


            return TermsAndConditionsListingResource::collection($tac);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load data', 501);
        }
    }

    public  function updateStatus(Request $request)
    {
        try {
            $valid_ids = auth()->user()->user_account->termsAndConditions->pluck('id')->toArray();
            $this->validate($request, [
                'id' => ['required', Rule::in($valid_ids)],
                'updateWhat' => ['required',Rule::in(['status','required'])],
                'updateWith' => 'required|bool',
            ]);

            if ($this->terms->updateStatus($request->id, [$request->updateWhat=>$request->updateWith]))
                return $this->apiSuccessResponse(200, "Updated Successfully", 'success');
            else
                return $this->apiErrorResponse('Request not Valid', 501);
        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to update', 501);
        }
    }


    /**   RAW QUERY
     * SELECT pf.name,pf.id, pr.name,pr.id FROM property_infos pf LEFT JOIN room_infos pr on pr.property_info_id = pf.id where pf.user_account_id=1112 and NOT EXISTS(SELECT property_info_id FROM terms_and_condition_properties_bridges where pf.id = terms_and_condition_properties_bridges.property_info_id and terms_and_condition_properties_bridges.room_info_ids IS NULL ) and NOT EXISTS(SELECT property_info_id FROM terms_and_condition_properties_bridges where terms_and_condition_properties_bridges.property_info_id = pf.id and terms_and_condition_properties_bridges.room_info_ids LIKE CONCAT('%',pr.id,'%') )
     */

}
