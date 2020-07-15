<?php

namespace App\Http\Controllers\v2\client;

use App\BookingSourceForm;
use App\CreditCardInfo;
use App\GuestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpsellStoreRequest;
use App\Http\Requests\UpsellTypeRequest;
use App\Http\Resources\General\Properties\PropertiesBridges\PropertiesBridgesResource;
use App\Http\Resources\General\UpsellListing\UpsellListingResource;
use App\Http\Resources\General\UpsellListing\UpsellOrderListingResource;
use App\Http\Resources\General\UpsellListing\UpsellTypeResource;
use App\BookingInfo;
use App\PropertyInfo;
use App\Repositories\Upsells\UpsellListingMetaParser;
use App\Repositories\Upsells\UpsellRepositoryInterface;
use App\RoomInfo;
use App\Upsell;
use App\UpsellOrder;
use App\UpsellType;
use App\User;
use App\UserAccount;
use App\UserPaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Log;
use App\System\PaymentGateway\PaymentGateway;

class UpsellController extends Controller
{
    public const UP_SELL_ROUTE='v2.client.upsell';

    private $upsell;

    public function __construct(UpsellRepositoryInterface $upsell)
    {
        $this->upsell = $upsell;
    }

    public function index()
    {
        $disableUpSell = false;
        
        try {
            
            $userAccount = auth()->user()->user_account;
            $userPaymentGateways = $userAccount->user_payment_gateways;
            
            foreach($userPaymentGateways as $upg) {
                
                $gateway = new PaymentGateway();
                $disableUpSell = $gateway->isSupportedForApplicationFee($upg);
                
                if($disableUpSell == false)
                    break;
            }
            
        } catch (Exception $ex) {
            Log::error($ex->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Stack' => $ex->getTraceAsString()
                ]);
        }
        
        return view(self::UP_SELL_ROUTE.'.upsell-list', ['disableUpSell' => $disableUpSell]);
    }

    public function create()
    {
        return view(self::UP_SELL_ROUTE.'.upsell-add');
    }

    public function upsellOrders()
    {
        return view(self::UP_SELL_ROUTE.'.upsell-order-list');
    }



    /**
     * @return JsonResponse
     */
    public function  getUpsellTypes($for_filters=false,$serve_id=0)
    {
        /**  For filters we need all types either active or inactive */
        $get_active_only = !filter_var($for_filters,FILTER_VALIDATE_BOOLEAN);
        $types = $this->upsell->getUpsellTypes(auth()->user()->user_account_id,$get_active_only,$serve_id);
        return $this->apiSuccessResponse(200, $types, 'success');
    }


    public function  storeUpsellListing(UpsellStoreRequest $request)
    {
        try {

            $request->validated();
            $user_payment_gateway = UserPaymentGateway::where('user_account_id', auth()->user()->user_account_id)->where('is_verified', 1)->first();

            if (empty($user_payment_gateway))
                return $this->apiErrorResponse('Please activate your Payment Gateway first.',422);

            $parser = new UpsellListingMetaParser();
            $request->meta_ = $parser->toJSON($request->meta);
            $upsell = $this->upsell->storeUpsells($request);
            $message = 'Upsell '.(empty($request->serve_id) ? ' added ' : ' updated '). 'successfully';
            return $this->apiSuccessResponse(200, $upsell, $message);

        } catch (\Exception $e) {
            log_exception_by_exception_object($e, null, 'error');

            return $this->apiErrorResponse('Failed to save.',500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function  allPropertiesWithRooms(Request $request)
    {
        $this->validate($request,
            ['serve_id' => 'required',   // Default Zero Serve Id => upsell_id, terms_id, guide_book_id
            'model_name' => ['required', Rule::in([PropertyInfo::UP_SELLS,PropertyInfo::GUIDE_BOOK,PropertyInfo::TAC]),]]
        );

        $properties = $this->upsell->bridgeAllPropertiesWithRooms(
            auth()->user()->user_account_id,
            $request->model_name,
            $request->serve_id
        );
        return PropertiesBridgesResource::collection($properties);
    }

    /**
     * @return JsonResponse
     */
    public function getTemplateVariables(){
        $variables = [
            config('db_const.template_variables_naming.'.BookingInfo::class),
            config('db_const.template_variables_naming.'.PropertyInfo::class),
            config('db_const.template_variables_naming.'.CreditCardInfo::class),
            config('db_const.template_variables_naming.'.UserAccount::class),
            //config('db_const.template_variables_naming.'.User::class),
            config('db_const.template_variables_naming.'.GuestData::class),
            config('db_const.template_variables_naming.'.RoomInfo::class),
            config('db_const.template_variables_naming.'.BookingSourceForm::class)
        ];
        $data = array();
        foreach ($variables as $model =>$variable){
            foreach ($variable['variables'] as $key=>$var ){
                if(!in_array($var, $data)){
                    $data[]=$var;
                }
            }
        }
        return $this->apiSuccessResponse('200',$data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function  getUpsellFormData(Request $request)
    {
        $this->validate($request, ['serve_id' => 'required|numeric|gt:0']);
        $upsell = $this->upsell->getUserUpsells(auth()->user()->user_account_id, $request->serve_id);


        return $this->apiSuccessResponse(200, $upsell, 'success');
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function  getUpsellList(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);
            $filters = $request->filters;

            array_push($filters['constraints'], ['user_account_id', auth()->user()->user_account_id]);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public  function upsellStatusChange(Request $request)
    {
        try {
            $valid_ids = auth()->user()->user_account->upsells->pluck('id')->toArray();
            $this->validate($request, ['id' => ['required', Rule::in($valid_ids)], 'status' => 'required|bool']);


            if ($request->status && empty($this->upsell->countAttachedPropertiesWithUpsell($request->id)))
                return $this->apiErrorResponse('No any Rental attached. Please attach at least one rental 
                to activate this upsell.',422);

            if ($this->upsell->changeUpsellStatus($request->id, $request->status))
                return $this->apiSuccessResponse(200, [$request->id => $request->status], 'success');
            else
                return $this->apiErrorResponse('Request not Valid', 501);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to update Upsell', 501);
        }
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

            array_push($filters['constraints'], ['user_account_id', auth()->user()->user_account_id]);
            array_push($filters['relations'], 'bookingInfo', 'upsellOrderDetails'); //'upsellListing', 'upsellListing.upsellType',

            $orders = get_collection_by_applying_filters($filters, UpsellOrder::class);

            return UpsellOrderListingResource::collection($orders);

        } catch (\Exception $exception) {
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to load upsell orders', 501);
        }
    }

    public function getUpsellConfig()
    {
        return $this->apiSuccessResponse(200, config('db_const.upsell_listing'), 200);
    }

    /** Upsell Types Methods */

    /**
     *  Redirect To Types List Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function viewUpsellTypesList()
    {
        return view(self::UP_SELL_ROUTE.'.types.upsell-types-list');
    }

    /**
     * Redirect To Create Type Form Page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createType(){
        return view(self::UP_SELL_ROUTE.'.types.upsell-types-add');
    }

    /**
     * Get A Single Type Data Against given Type_Id.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTypeOldData(Request $request){
        $type=$this->upsell->getOneType($request->all()['serve_id']);
        return $this->apiSuccessResponse('200',$type);
    }

    /**
     * Update Record in Database
     * @param UpsellTypeRequest $request
     * @return JsonResponse
     */
    public function  updateType(UpsellTypeRequest $request){
        try {
            $validated=$request->validated();
            $this->upsell->updateType($validated);
            $response=$this->apiSuccessResponse('200',"","Updated Successfully");
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;

    }

    /**
     * Add Type Record To Database
     * @param UpsellTypeRequest $request
     * @return JsonResponse
     */
    public function saveType(UpsellTypeRequest $request){
        try {
            $validated=$request->validated();
            $new=$this->upsell->createType($validated);
            $response=$this->apiSuccessResponse('200',"","Added Successfully");
        }catch (\Exception $e){
            dump($e->getMessage());
            log_exception_by_exception_object($e, null, 'error');
            $response=$this->apiErrorResponse('Request Failed.',500);
        }
        return $response;

    }

    /**
     * Get All User Defined Types By Applying Filters from Types List Page
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAllTypes(Request $request){
        try {
            $this->validate($request, ['filters' => 'required|array']);


            /** Filters Formation */
            $filters = $request->filters;
            array_push($filters['constraints'], ['user_account_id',auth()->user()->user_account_id]);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public  function updateTypeStatus(Request $request)
    {
        $valid_ids = auth()->user()->user_account->upsell_types->pluck('id')->toArray();
        $this->validate($request, [
            'id' => ['required', Rule::in($valid_ids)],
            'updateWhat' => ['required'],
            'updateWith' => 'required|bool',
        ]);
        try {
            if ($this->upsell->updateTypeStatus($request->id, [$request->updateWhat=>$request->updateWith]))
                return $this->apiSuccessResponse(200,"", 'Updated Successfully');
            else
                return $this->apiErrorResponse('Request not Valid', 501);
        } catch (\Exception $exception) {
            dump($exception->getMessage());
            log_exception_by_exception_object($exception);

            return $this->apiErrorResponse('Oops something wrong, Fail to update', 501);
        }
    }
}
