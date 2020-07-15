<?php
namespace App\Http\Controllers\v2\client;

use App\BookingSourceForm;
use App\CaCapability;
use App\Http\Controllers\Controller;
use App\Http\Resources\BA\Properties\PropertyListCollection;
use App\Http\Resources\General\BookingSource\ClientActiveBookingSourcesWithDetailResource;
use App\Http\Resources\General\Properties\PropertiesListToExportResource;
use App\Jobs\SyncProperties\BASyncPropertyJob;
use App\PaymentGatewayForm;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\Repositories\BookingSources\BookingSources;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Repositories\Properties\Properties;
use App\RoomInfo;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\PaymentGateway;
use App\UserAccount;
use App\UserBookingSource;
use App\UserPaymentGateway;
use App\UserSettingsBridge;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Image;
use Validator;


class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(){
        $this->isPermissioned('properties'); //Having Permission to perform this act
        return view('v2.client.properties.properties_list'); //,['user_account' => $user_account]
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

    /**
     * @return mixed
     */
    public function getPropertiesNames()
    {
        return PropertyInfo::where('user_account_id', auth()->user()->user_account_id)
            ->where('available_on_pms', 1)->select('pms_property_id','id', 'name')->get();
    }

    /**
     * This Will Return Room_infos against Selected Property
     * @param Request $request
     * @return array
     */
    public function getRoomInfo(Request $request){
        if(!empty($request->property_info_id) && $request->property_info_id!="all"){
            $data=RoomInfo::where('property_info_id',$request->property_info_id)->select('id','name')->get();
        }else{
            $data=[];
        }
        return $data;
    }
    /**
     * @param Request $request
     * @param $propertyInfoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUsePropertyBookingSourceSettingLocalOrGlobal(Request $request, $propertyInfoId){
        try{
            $this->isPermissioned('properties'); //Having Permission to perform this act

            $useSetting = ($request->useSettings === 'local' ? 1 : 0);
            if(auth()->user()->user_account->properties_info->where('id', $propertyInfoId)->first()->update(['use_bs_settings' => $useSetting]))
                $response = $this->successResponse('Booking Source Settings Updated', 200);
            else
                $response = $this->errorResponse('Failed to Update Booking Source Settings', 501);
        } catch (\Exception $e){
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            $response = $this->errorResponse('Failed to Update Booking Source Settings', 500);
        }
        return  $response;
    }

    /**
     * @param Request $request
     * @param $propertyInfoId
     * @return JsonResponse
     */
    public function changeUsePropertyPaymentGatewaySettingLocalOrGlobal(Request $request, $propertyInfoId)
    {
        try{
            $this->isPermissioned('properties'); //Having Permission to perform this act

            $userAccount = auth()->user()->user_account;
            $useSetting = ($request->useSettings === 'local' ? 1 : 0);
            $userPaymentGateway = $userAccount->user_payment_gateways->where('property_info_id', ($useSetting != 0 ? $propertyInfoId : 0))->first();

            if ((!is_null($userPaymentGateway) && ($userPaymentGateway->is_verified == 1)) || ($useSetting == 0)) {
                if ($userAccount->properties_info->where('id', $propertyInfoId)->first()->update(['use_pg_settings' => $useSetting]))
                    $response = $this->successResponse('Payment Gateway Settings Updated', 200);
                else
                    $response = $this->errorResponse('Failed to Update Payment Gateway Settings', 501);
            } else {
                $response = $this->successResponse('"Please Verify Your Locale PaymentGateway Otherwise System Will Revert Setting to Global!"', 200);
            }
        } catch (\Exception $e){
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            $response = $this->errorResponse('Failed to Update Payment Gateway Settings', 500);
        }
        return  $response;
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public  function getPropertyBookingSourcesWithDetail(Request $request)
    {
        $this->isPermissioned('properties'); //Having Permission to perform this act
        try{
            $userAccount = auth()->user()->user_account;
            $BS = UserBookingSource::with(['booking_source_form' =>  function ($query)
            {$query->select('id','name','logo');}])->where('user_account_id', $userAccount->id)
                ->with('credit_card_validation_setting', 'payment_schedule_setting', 'security_damage_deposit_setting',
                    'cancellation_setting')->where('status', 1)
                ->where('property_info_id', $request->propertyInfoId)->get();
            return  ClientActiveBookingSourcesWithDetailResource::collection($BS);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            return $this->errorResponse('Failed to Get Booking Source Settings', 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientBookingSourcePreviousSettings(Request $request)
    {
        /**
         * @var $booking_source_repo BookingSources
         * @var $bS BookingSourceForm
         */
        $this->isPermissionedBulk(['properties', 'accountSetup']);

        try {
            $booking_source_repo = resolve(BookingSources::class);
            $userAccount = auth()->user()->user_account;
            $data = [];
            $allBS = BookingSourceForm::select('id','name','logo', 'channel_code', 'type')
                ->where('pms_form_id', $userAccount->pms->pms_form_id)->where('status', 1)->get();

            // CA Capabilities
            $booking_sources_capabilities = $booking_source_repo->getAllBookingSourcesCapabilities();

            $user_booking_sources = UserBookingSource::with(['booking_source_form' =>
                function ($query) {$query->select('id','name');}])
                ->with('credit_card_validation_setting', 'payment_schedule_setting',
                'security_damage_deposit_setting', 'cancellation_setting')
                ->where('user_account_id', $userAccount->id)
                ->where('property_info_id', $request->propertyInfoId)
            ->get();

            if(isset($request->bookingSourceFormId) && ($request->bookingSourceFormId != 0))
                $allBS = $allBS->where('id', $request->bookingSourceFormId);

            foreach ($allBS as $bsKey => $bS) {

                // Fetch Booking not Supported or not any SD or Payments Supported
                if (( empty($booking_sources_capabilities[$bS->id][CaCapability::FETCH_BOOKING]))
                    || (empty($booking_sources_capabilities[$bS->id][CaCapability::AUTO_PAYMENTS])
                        && empty($booking_sources_capabilities[$bS->id][CaCapability::SECURITY_DEPOSIT])))
                    continue;

                $user_booking_source = $user_booking_sources->where('booking_source_form_id', $bS->id)->first();

                $data['bookingSourceSettings'][$bS->id] = BookingSources::MapSettingsByJsonStringFromBridgeTableWithModelRelations($user_booking_source);
                $data['bookingSourceSettings'][$bS->id]['id'] = $bS->id;
                $data['bookingSourceSettings'][$bS->id]['name'] = $bS->name;
                $data['bookingSourceSettings'][$bS->id]['channel_code'] = $bS->channel_code;
                // BS Logo
                $data['bookingSourceSettings'][$bS->id]['logo'] = strlen($bS->logo) > 2
                    ? asset('storage/uploads/booking_souce_logo/').'/'.$bS->logo : $bS->logo;

                $data['bookingSourceSettings'][$bS->id]['status'] = !is_null($user_booking_source) ?  $user_booking_source->status : false; /* Default False | De-active */

                // Capabilities
                $data['bookingSourceSettings'][$bS->id]['payment_capability']  = $booking_sources_capabilities[$bS->id][CaCapability::AUTO_PAYMENTS];
                $data['bookingSourceSettings'][$bS->id]['security_capability'] = $booking_sources_capabilities[$bS->id][CaCapability::SECURITY_DEPOSIT];


                //Booking Types supported by channels on PMS`
                $data['bookingSourceSettings'][$bS->id]['support_cc'] = $bS->isCCBookingsSupported();
                $data['bookingSourceSettings'][$bS->id]['support_vc'] = $bS->isVCBookingsSupported();
                $data['bookingSourceSettings'][$bS->id]['support_bt'] = $bS->isBTBookingsSupported();
            }
            return $this->successResponse('success', 200, $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            return $this->errorResponse('Failed to Get Booking Source Settings', 500);
        }
    }


    /**
     * Save or update  user Booking Source settings
     * @param Request $request
     * @return JsonResponse
     */
    public function saveClientBookingSourceSettings(Request $request)
    {
        $this->isPermissionedBulk(['properties', 'accountSetup']);
        return resolve(BookingSources::class)->saveClientBookingSourceSettings($request->toArray());
    }

    /**
     * get user pre saved values Payment gateway credentials to draw or show keys form
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertyLocalPaymentGatewayWithKeys(Request $request)
    {
        $this->isPermissioned('properties'); //having Permission to do this act
        $userPaymentGateway = PaymentGateways::getLocalPropertyPaymentGatewayWithKeys(auth()->user()->user_account, $request->propertyInfoId);
        if ($userPaymentGateway != null)
            return $this->successResponse('success', 200, $userPaymentGateway);
        else
            return $this->errorResponse('Failed to get Payment Gateway Settings', 1000); // 1000 is custom code, added because if ts received no, success or fail msg is shown to user.
    }

    /**
     * get Payment gateway credentials to draw form along with user pre saved values if exists
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaymentGatewayWithKeys(Request $request)
    {
        $this->isPermissionedBulk(['properties', 'accountSetup']);
        $paymentGateway = PaymentGateways::getPaymentGatewayWithKeys(auth()->user()->user_account, $request->paymentGatewayFormId, $request->propertyInfoId);
        if ($paymentGateway != null)
            return $this->successResponse('success', 200, $paymentGateway);
        else
            return $this->errorResponse('Failed to get Payment Gateway Settings', 500);
    }


    /**
     * Save Client PaymentGateways Credentials and validate
     * @param Request $request
     * @return JsonResponse
     */
    public function savePaymentGatewayKeys(Request $request)
    {
        $this->isPermissionedBulk(['properties', 'accountSetup']);
        try{
            $data = $request->data;
            $propertyInfoId = $request->data['propertyInfoId'];
            $isAlreadyAddedGatewayOnParentServer = false;
            $userAccount = auth()->user()->user_account;
            $paymentGatewayModel = PaymentGatewayForm::with('payment_gateway_parent')->where('id', $data['paymentGatewayFormId'])->where('status',1)->first();
            $gatewayModelObject = new GateWay($paymentGatewayModel->gateway_form); //Pass form string to gateway class to entertain
            $haveParentGateway = ($paymentGatewayModel->payment_gateway_parent_id > 0); /*For Spreedly child gateways*/
            $systemPaymentGateway =  resolve(PaymentGateway::class);
            foreach ($gatewayModelObject->credentials as $dBCredential) {
                foreach ($data['credentials'] as $userCredential) {
                    if($dBCredential->name == $userCredential['name']) {
                        $dBCredential->value = $userCredential['value'];
                        unset($userCredential);
                        break;
                    }
                }
            }

            $bridgeModel = UserSettingsBridge::where('user_account_id',$userAccount->id)->where('model_name',UserPaymentGateway::class)->where('property_info_id', $propertyInfoId)->first();
            if(is_null($bridgeModel)){
                $userPaymentGateway = UserPaymentGateway::create([
                'payment_gateway_form_id' =>  $data['paymentGatewayFormId'],
                'property_info_id'        =>  $propertyInfoId,
                'user_id'                 =>  auth()->user()->id,
                'user_account_id'         =>  $userAccount->id,
                'gateway'                 =>  json_encode($gatewayModelObject),
                'is_verified'             =>  0 ]);

                UserSettingsBridge::create([
                'user_account_id'        =>  $userAccount->id,
                'property_info_id'       =>  $propertyInfoId,
                'model_name'             =>  UserPaymentGateway::class,
                'model_id'               =>  $userPaymentGateway->id,
                'booking_source_form_id' =>  0 ]);
            } else {
                $userPaymentGateway = $bridgeModel->user_payment_gateway;
                /*For Spreedly child gateways If Same Value as pre Created Object No need to again create Customer object*/
                if (($haveParentGateway) && ($userPaymentGateway->payment_gateway_form_id == $data['paymentGatewayFormId'])) {
                    $preAddedGateway = json_decode($userPaymentGateway->user_payment_gateway->gateway);
                    if ((json_encode($gatewayModelObject->credentials)) == (json_encode($preAddedGateway->auth_modes[0]->credentials)))
                        $isAlreadyAddedGatewayOnParentServer = true;
                } elseif (!$haveParentGateway) {    /*For those gateways whose parent Zero*/
                    
                    $gatewayObjectBackup = new GateWay($userPaymentGateway->gateway);
                    
                    $gatewayModelObject->statement_descriptor = $gatewayObjectBackup->statement_descriptor;
                    $gatewayModelObject->companyName = $gatewayObjectBackup->companyName;
                    $gatewayModelObject->displayName = $gatewayObjectBackup->displayName;
                    
                    $userPaymentGateway->update([
                        'payment_gateway_form_id' => $data['paymentGatewayFormId'],
                        'gateway' => json_encode($gatewayModelObject),
                        //'is_verified' => 0
                    ]);
                }
            }
            /*For Spreedly (IF Have Parent And not Pre ParentGateway Object Created) Create Once*/
            if (($haveParentGateway) && (!$isAlreadyAddedGatewayOnParentServer)) {
                $parentGatewayResponse = $systemPaymentGateway->addGatewayOnParentServer($paymentGatewayModel->payment_gateway_parent->backend_name, json_decode($paymentGatewayModel->payment_gateway_parent->credentials , true), $gatewayModelObject);
                $previousUserPaymentGatewayId = $userPaymentGateway->payment_gateway_form_id;
                $parentGatewayResponse->credentials = $gatewayModelObject->credentials; //Assign credentials to Gateway Object and save in DB
                $userPaymentGateway->update(['payment_gateway_form_id' => $data['paymentGatewayFormId'], 'gateway' => json_encode($parentGatewayResponse), 'is_verified' => 0]);
                if ($previousUserPaymentGatewayId != $data['paymentGatewayFormId']) {
                   Bookings::booking_effects($propertyInfoId, $userPaymentGateway);
                }
            }

            $paymentGatewayRepo = resolve(PaymentGateways::class);
            $card = $paymentGatewayRepo->getTestCard($userPaymentGateway->id, $data['paymentGatewayFormId'], $userAccount->id);

            
            if ($paymentGatewayModel->name == 'Stripe') {
                return  ($userPaymentGateway->is_verified == 0 ? $this->errorResponse('Please Complete Gateway Integration! ',422) : $this->successResponse('success', 200));
            }
            
            
            $response = $systemPaymentGateway->authorizeWithCard($card, $userPaymentGateway);
            
            if($response != null) {
                $userPaymentGateway->update(['is_verified' => 1]);
                if ($propertyInfoId != 0) {
                    $userAccount->properties_info->where('id', $propertyInfoId)->first()->update(['use_pg_settings' => 1]);
                } elseif ($userAccount->integration_completed_on == null) {
                    $userAccount->update(['integration_completed_on' => now()->toDateTimeString()]);
                }
                $systemPaymentGateway->cancelAuthorization($response, $userPaymentGateway);
                //\Session::put(self::MasterSettingMsg, "PMS Integration Process Completed Successfully!"); //TODO
                return $this->successResponse('success', 200);
            } else {
                $userPaymentGateway->update(['is_verified' => 0]);
                return $this->errorResponse('Something Wrong or Credentials not Valid!', 401);
            }
        } catch (Exception $e ){
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @param $propertyInfoId
     * @return JsonResponse
     */
    public function paymentGatewayStoreWithoutAuthTest(Request $request, $propertyInfoId = 0) {

        $this->isPermissionedBulk(['properties', 'accountSetup']);

        session()->forget([PaymentGateways::RedirectURL, PaymentGateways::CustomPropertyID, PaymentGateways::PaymentGatewayID]);
        if($propertyInfoId != 0)
            \Session::put(PaymentGateways::RedirectURL, 'client/v2/properties');
        else
            \Session::put(PaymentGateways::RedirectURL, 'client/v2/pms-setup-step-3');

        $csrf = sha1(time() . '1');
        $url = $request->post('url', '');
        $url .= '&state=' . $csrf;

        session()->put(PaymentGateways::CsrfSent, $csrf);
        \Session::put(PaymentGateways::CsrfSent, $csrf);

        \Session::put(PaymentGateways::CustomPropertyID, $propertyInfoId);
        \Session::put(PaymentGateways::PaymentGatewayID, $request->data['paymentGatewayFormId']);

        session()->save();
        \Session::save();

        return $this->successResponse('success', 200, ['url' => $url]);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getResponseFromStripeConnectAndRedirect(Request $request) {
        try{
            $userAccount = auth()->user()->user_account;
            $stripeConnect = array('proceed' => 0, 'state' => '', 'code' => '', 'scope' => 'read_write', 'error' => 0, 'error_description' => 0);
            $stripeConnect['state'] = request()->get('state') != null ? request()->get('state') : '';
            $stripeConnect['code']  = request()->get('code') != null ? request()->get('code') : '';
            $stripeConnect['scope'] = request()->get('scope') != null ? request()->get('scope') : '';
            $stripeConnect['error'] = request()->get('error') != null ? request()->get('error') : 0;
            $stripeConnect['error_description'] = request()->get('error_description') != null ? request()->get('error_description') : 0;

//            Log::notice("CSRF",
//                [
//                    'Global Session: ' . \Session::get(PaymentGateways::CsrfSent),
//                    'Helper Session: ' . session(PaymentGateways::CsrfSent)
//                ]);

            if($stripeConnect['code'] != '' && $stripeConnect['scope'] != '') {
                if(session(PaymentGateways::CsrfSent) == $stripeConnect['state']) {
                    $stripeConnect['proceed'] = 1;
                } else {
                    $stripeConnect['error'] = 'Forgery';
                    $stripeConnect['error_description'] = 'Request forgery detected.';
                }
            }

            if(session()->has(PaymentGateways::CustomPropertyID) && session()->has(PaymentGateways::RedirectURL)) {
                if(!is_null(session()->get(PaymentGateways::CustomPropertyID)) && !is_null(session()->get(PaymentGateways::RedirectURL))) {

                    if($stripeConnect['proceed'] == 1) {
                        $repoPaymentGateways = new PaymentGateways();
                        $repoPaymentGateways->savePropertySpecificStripeConnect($stripeConnect, $userAccount, \Session::get(PaymentGateways::CustomPropertyID, 0));
                    } else {
                        session()->put(PaymentGateways::StripeConnectMsg, $stripeConnect['error_description']);
                    }
                    $redirectUrl = \Session::get(PaymentGateways::RedirectURL);

                    $this->clearSessionForStripeConnect();
                    return redirect($redirectUrl)->with('stripeConnect');
                }
            } else {
                $this->clearSessionForStripeConnect();
                die("Oops Something Wrong or missing Redirect Url or Property Info." );
            }
        } catch(\Exception $e) {
            $this->clearSessionForStripeConnect();
            die("Oops Something Wrong." . ucwords( $e->getMessage() ));
        }
        $this->clearSessionForStripeConnect();
        return null;
    }

    private function clearSessionForStripeConnect() {
        \Session::forget(PaymentGateways::CustomPropertyID);
        \Session::forget(PaymentGateways::RedirectURL);
        \Session::forget(PaymentGateways::CsrfSent);
        session()->save();
    }

    /**
     * PMS Wise
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
     * @return mixed
     */
    public function getAllPropertiesCities(){
        $cities = PropertyInfo::select('city')
            ->where('user_account_id', auth()->user()->user_account_id)
            ->where('available_on_pms', 1)
            ->where('city','!=','')
            ->distinct()->pluck('city');
        if( !empty($cities) ){
            $cities = $cities->toArray();
        }
        return $cities;
    }

    public function generateApiKey() {
        return $this->apiSuccessResponse(200,generateUniqueUseAblePropertyAPiKeyForPMS(auth()->user()->user_account_id));
    }


    public function exportAllProperties(Request $request) {

        $this->isPermissioned('properties'); //Having Permission to perform this act

        try{
            $userAccount = UserAccount::with('properties_info')->where('id', auth()->user()->user_account_id)->select('id')->first();
            if (isset($request->city) && ($request->city != 'all'))
                $userAccount->properties_info = $userAccount->properties_info->where('city', $request->city);

            if( !empty($request->propertyInfoIds) ){
                $userAccount->properties_info = $userAccount->properties_info->whereIn('id', $request->propertyInfoIds);
            }


            return PropertiesListToExportResource::collection($userAccount->properties_info);
        } catch (\Exception $e){
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            return $this->errorResponse('Failed to Get Properties', 500);
        }
    }
    /**
     * Upload Property Info logo
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updatePropertyLogo(Request $request, $id) {

        $this->isPermissioned('properties'); //Having Permission to perform this act

        try {
            $fileNameToStore = 'no_image.png';
            $path = '/storage/uploads/property_logos/';
            $validator = Validator::make($request->all(), ['propertyLogo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',]);
            if ($validator->fails())
                return $this->apiErrorResponse($validator->errors()->first(), 422, ['path' => $fileNameToStore]);

            if ($request->hasFile('propertyLogo')) {
                $image = $request->file('propertyLogo');
                $destinationPath = public_path('storage/uploads/property_logos');
                $fileNameToStore = time() . '.' . $image->getClientOriginalExtension();
                $img = Image::make($image->getRealPath());
                $img->resize(1080, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $fileNameToStore);
            }
            if (auth()->user()->user_account->properties_info->where('id', $id)->first()->update(['logo' => $fileNameToStore]))
                return $this->apiSuccessResponse(200, ['path' => $fileNameToStore], 'Property Logo Updated');
            else
                return $this->apiErrorResponse('Fail to Update Property Logo', 500, ['path' => $fileNameToStore]);
        } catch (\Exception $e) {
            return $this->apiErrorResponse('Fail to Update Property Logo, Property Id Not Valid', 500, ['path' => $fileNameToStore]);
        }
    }

    public function updatePropertyEmail(Request $request) {

        $this->isPermissioned('properties'); //Having Permission to perform this act

        $validator = Validator::make($request->all(), ['email' => 'nullable|email',]);
        if ($validator->fails())
            return $this->apiErrorResponse($validator->errors()->first(), 422);
        if (auth()->user()->user_account->properties_info->where('id', $request->id)->first()->update(['property_email' => $request->email]))
            return $this->apiSuccessResponse(200, [],'Property Email Updated');
        else
            return $this->apiErrorResponse('Fail to Update Property Email, Property Id Not Valid', 500);
    }

    /**
     * PMS Wise
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertyInfo(Request $request) {
        $this->isPermissioned('properties');
       return $this->getPropertyInfoData($request->propertyInfoId);
    }

    /**
     * PMS Wise
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
}
