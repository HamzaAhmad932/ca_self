<?php

namespace App\Http\Controllers\v2\client;

use App\Http\Controllers\Controller;
use App\Jobs\BASyncBookingJobNew2;
use App\Jobs\SyncBookingJob;
use App\Jobs\SyncProperties\BASyncPropertyJob;
use App\PaymentGatewayForm;
use App\PmsForm;
use App\PropertyInfo;
use App\Repositories\Properties\Properties;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\PMS;
use App\UserAccount;
use App\UserPaymentGateway;
use App\UserPms;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Image;
use Validator;

class PmsIntegrationController extends Controller
{


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewPMSSetupStep1()
    {
        $this->isPermissioned('accountSetup'); //having Permission to do this act
        return view('v2.client.pms_integration.setup_step1');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory
     * |\Illuminate\Http\RedirectResponse
     * |\Illuminate\Routing\Redirector|\Illuminate\View\View|void
     */
    public function viewPMSSetupStep2()
    {
        $this->isPermissioned('accountSetup'); //having Permission to do this act
        return pms_redirect_by_checking_steps_completed(2);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory
     * |\Illuminate\Http\RedirectResponse
     * |\Illuminate\Routing\Redirector
     * |\Illuminate\View\View|void
     */
    public function viewPMSSetupStep3()
    {
        $this->isPermissioned('accountSetup'); //Having Permission to perform this act
        return pms_redirect_by_checking_steps_completed(3);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory
     * |\Illuminate\Http\RedirectResponse
     * |\Illuminate\Routing\Redirector
     * |\Illuminate\View\View|void
     */
    public function viewPMSSetupStep4()
    {
        $this->isPermissioned('accountSetup'); //Having Permission to perform this act
        try {
            return pms_redirect_by_checking_steps_completed(4);
        } catch (Exception $e) {
            abort(501, 'Oop Something Wrong!');
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory
     * |\Illuminate\Http\RedirectResponse
     * |\Illuminate\Routing\Redirector
     * |\Illuminate\View\View|void
     */
    public function viewPMSSetupStep5()
    {
        $this->isPermissioned('accountSetup'); //Having Permission to perform this act
        try {
            return pms_redirect_by_checking_steps_completed(5);
        } catch (Exception $e) {
            abort(501, 'Oop Something Wrong!');
        }
    }

    /**
     * @return JsonResponse
     */
    public function getUserSupportedPmsList()
    {

        $this->isPermissioned('accountSetup'); //having Permission to do this act
        /**
         * @var $userAccount UserAccount
         */
        $userAccount = auth()->user()->user_account;

        if (!is_null($userAccount->pms))
            $pmsForms = array($userAccount->pms->pms_form->toArray()); // Previous User

        else {
            $pmsForms = PmsForm::where('status', 1)->orderBy('priority', 'ASC')->get();
            if (count($pmsForms) == 0)
                abort(500, 'Not any PMS available yet!');
            $pmsForms = $pmsForms->toArray(); // New User
        }

        return $this->apiSuccessResponse(200, $pmsForms, 'success');
    }


    /**
     * Get Pms Form  Credentials to Generate Form
     * @param Request $request
     * @return JsonResponse
     */
    public function getPMS_CredentialFormAlongWithUserSavedKeys(Request $request)
    {

        $this->isPermissioned('accountSetup');

        $validator = Validator::make($request->all(), ['pmsFormId' => 'required|numeric',]);

        if ($validator->passes()) {
            $userAccount = auth()->user()->user_account;

            return $this->apiSuccessResponse(200,
                $this->getPMSCredentialFormAlongWithUserSavedKeys($userAccount, $request->pmsFormId), 'success');

        } else {
            return $this->apiErrorResponse(ucwords($validator->errors()->first()), 422, $validator->errors());
        }
    }


    /**
     * Get Pms Form  Credentials to Generate Form
     * @param Request $request
     * @return JsonResponse
     */
    public function savePMS_Credentials(Request $request)
    {
        $this->isPermissioned('accountSetup'); //having Permission to do this act
        $validator = Validator::make($request->all(), ['pmsFormId' => 'required|numeric', 'credentials' => 'required|array|min:1']);
        if ($validator->passes()) {
            $userAccount = auth()->user()->user_account;
            $pmsForm = PmsForm::where('id', $request->pmsFormId)->where('status', 1)->first();
            if (is_null($pmsForm))
                return $this->apiErrorResponse('PMS Id not valid', 402);

            if (($userAccount->pms != null) && ($userAccount->pms->pms_form_id != $request->pmsFormId))
                return $this->apiErrorResponse('PMS Id not valid', 402);

            // $credentials   = applyConstraintsToAvoidUpdatingUniqueKey($request->credentials, $userAccount->pms);
            $credentials = $request->credentials;

            $populatedForm = $this->populateKeysWithConfigForm($pmsForm, $credentials);

            $messages = [
                'required' => 'The :attribute field is required.',
            ];
            $validator = Validator::make($request->credentials, $populatedForm['rules'], $messages);

            if ($validator->passes()) {
                $keyUniqueExist = ($populatedForm['isUnique'] != null
                    ? UserPms::where([['unique_key', $populatedForm['isUnique']], ['pms_form_id', $pmsForm->id],
                        ['is_verified', 1], ['user_account_id', '!=', $userAccount->id]])->count() : 0);

                if ($keyUniqueExist > 0)
                    return $this->apiErrorResponse('This ' . $populatedForm['isUniqueName'] .
                        ' already registered', 500);

                if (is_null($userAccount->pms)) {
                    $userPms = UserPms::create([
                        'pms_form_id' => $pmsForm->id,
                        'user_id' => auth()->user()->id,
                        'name' => $pmsForm->name,
                        'user_account_id' => $userAccount->id,
                        'form_data' => json_encode($populatedForm['formDataConfig']),
                        'unique_key' => $populatedForm['isUnique']]);
                    $UserMetaData = ['userPmsId' => $userPms->id, 'isNew' => true, 'previousIsVerifiedStatus' => 0,
                        'previousFormData' => null, 'previousUniqueKey' => null];

                } else {
                    $UserMetaData = ['userPmsId' => $userAccount->pms->id, 'isNew' => false,
                        'previousIsVerifiedStatus' => $userAccount->pms->is_verified,
                        'previousFormData' => $userAccount->pms->form_data,
                        'previousUniqueKey' => $userAccount->pms->unique_key];
                    $userAccount->pms->update(array('form_data' => json_encode($populatedForm['formDataConfig']),
                        'name' => $pmsForm->name, 'pms_form_id' => $pmsForm->id,
                        'unique_key' => $populatedForm['isUnique']));
                }

                try {
                    $pms = new PMS($userAccount);
                    $options = new PmsOptions();
                    $options->requestType = PmsOptions::REQUEST_TYPE_XML;
                    $PMSResponse = $pms->fetch_user_account($options);

                    $result = validateAndUpdateUserPMSStatusByPMSResponse($userAccount, $PMSResponse); //Helper Function
                    if (isset($result) && $result['status']) {
                        return $this->apiSuccessResponse(200,
                            ['nextStepUrl' => route('viewPMS_SetupStep2')], 'success');
                    } else {
                        UnAuthorizedUserPMSRevertBackChanges($UserMetaData['userPmsId'], $UserMetaData['isNew'],
                            filter_var($UserMetaData['previousIsVerifiedStatus'], FILTER_VALIDATE_BOOLEAN),
                            $UserMetaData['previousFormData'], $UserMetaData['previousUniqueKey']);
                        return $this->apiErrorResponse($result['message'], 422);
                    }
                } catch (PmsExceptions $e) {
                    UnAuthorizedUserPMSRevertBackChanges($UserMetaData['userPmsId'], $UserMetaData['isNew'],
                        filter_var($UserMetaData['previousIsVerifiedStatus'], FILTER_VALIDATE_BOOLEAN),
                        $UserMetaData['previousFormData'], $UserMetaData['previousUniqueKey']);
                    report($e);
                    $msg = ($e->getPMSCode() == PMS::ERROR_UNKNOWN_ERROR)
                        ? "These credentials do not match with the credentials at $pmsForm->name"
                        : $e->getCADefineMessage();
                    return $this->apiErrorResponse($msg, $e->getPMSCode());
                }
            } else {
                return $this->apiErrorResponse($validator->errors(), 422, $validator->errors());
            }
        } else {
            return $this->apiErrorResponse($validator->errors(), 422, $validator->errors());
        }
    }

    /**
     * @param UserAccount $userAccount
     * @param $pmsFormId
     * @return mixed
     */
    private function getPMSCredentialFormAlongWithUserSavedKeys(UserAccount $userAccount, $pmsFormId)
    {

        $this->isPermissioned('accountSetup');

        if (is_null($userAccount->pms)) {
            $pmsForm = PmsForm::where('id', $pmsFormId)->where('status', 1)->first();
            $instruction_page = $pmsForm->instruction_page;
            $configPmsForm = Config::get('db_const.' . $pmsForm->backend_name);
            foreach($configPmsForm['credentials'] as $key => $value) {
                $configPmsForm['credentials'][$key]['value'] = "";
                unset($configPmsForm['credentials'][$key]['status']);
            }
//            unset($configPmsForm['status']);
        } else {
            $valuesArr = [];
            $configPmsForm = Config::get('db_const.' . $userAccount->pms->pms_form->backend_name);
            $userSavedForm = json_decode($userAccount->pms->form_data, true);
            $instruction_page = $userAccount->pms->pms_form->instruction_page;

            foreach ($userSavedForm['credentials'] as $key => $value) {
                $valuesArr[$userSavedForm['credentials'][$key]['name']] = $userSavedForm['credentials'][$key]['value'];
            }
            foreach ($configPmsForm['credentials'] as $key => $value) {
                $configPmsForm['credentials'][$key]['value'] = (isset($valuesArr[$configPmsForm['credentials'][$key]['name']])
                    ? $valuesArr[$configPmsForm['credentials'][$key]['name']] : "");

            }
        }
        return [
            'pmsName' => $configPmsForm['name'],
            'credentials' => $configPmsForm['credentials'],
            'uniqueKeySaved' => ($userAccount->pms != null),
            'instruction_page' => $instruction_page
        ];
    }


    /**
     * @param PmsForm $pmsForm
     * @param array $credentials
     * @return array
     */
    private function populateKeysWithConfigForm(PmsForm $pmsForm, array $credentials)
    {
        $rules = [];
        $isUnique = '';
        $isUniqueName = '';
        $formDataConfig = Config::get('db_const.' . $pmsForm->backend_name);
        foreach ($formDataConfig['credentials'] as $key => $value) {
            $name = $formDataConfig['credentials'][$key]['name'];
            $credentials[$name] = !empty($credentials[$name]) ? $credentials[$name] : '';
            $formDataConfig['credentials'][$key]['value'] = $credentials[$name];
            $rules[$formDataConfig['credentials'][$key]['name']] = $formDataConfig['credentials'][$key]['rules'];
            if ($formDataConfig['credentials'][$key]['is_unique']) {
                $isUnique = $credentials[$name];
                $isUniqueName = $name;
            }
        }
        return ['formDataConfig' => $formDataConfig, 'rules' => $rules, 'isUnique' => $isUnique,
            'isUniqueName' => $isUniqueName];
    }


    /**
     * PMS Wise
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


    /**
     *  Return  Master Payment Gateway Form Selected Form Id | First PaymentGateway Form Id if not any record found
     * @param Request $request
     * @return JsonResponse
     */
    public function getMasterPaymentGatewayFormId(Request $request)
    {

        $this->isPermissioned('accountSetup');

        $userPaymentGateway = UserPaymentGateway::with('payment_gateway_form')
            ->where('property_info_id', 0)
            ->where('user_account_id', auth()->user()->user_account->id)
            ->select('payment_gateway_form_id', 'id')->first();

        if (is_null($userPaymentGateway)) {

            $firstForm = PaymentGatewayForm::where('status', 1)->first();

            if (is_null($firstForm))
                return $this->apiErrorResponse('No Gateway Form Available Yet!', 422);
            else
                return $this->apiSuccessResponse(200, ['paymentGatewayFormId' => $firstForm->id, 'name' => $firstForm->name], 'success');

        } else {
            return $this->apiSuccessResponse(200,
                ['paymentGatewayFormId' => $userPaymentGateway->payment_gateway_form->id,
                    'name' => $userPaymentGateway->payment_gateway_form->name], 'success');
        }
    }

    /**
     * Get Detail of User PMS Completed Steps
     * @return JsonResponse
     */
    public function getStepsCompletedStatus()
    {
        $this->isPermissioned('accountSetup');
        try {
            return $this->apiSuccessResponse(200,
                getUserPMSStepsCompletedStatus(auth()->user()->user_account_id), 'success');
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 422);
        }
    }


    /** Connect | Disconnect All Properties In One XML Request
     * PMS Wise
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


    public function setSyncBookingTime(Request $request)
    {

        $this->isPermissioned('accountSetup');

        $validator = Validator::make($request->all(), ['sync_date' => 'required|date|date_format:Y-m-d|before:tomorrow',]);

        if (empty($validator->passes()))
            return $this->apiErrorResponse($validator->errors()->first(), 422, $validator->errors());

        $user_account = auth()->user()->user_account;

        if (!empty($user_account->sync_booking_from))
            return $this->apiErrorResponse('Already used this feature.', 422);


        $properties = PropertyInfo::where('user_account_id', $user_account->id)
            ->where('available_on_pms', 1)->where('status', 1)
            ->update(['sync_booking_from' => $request->sync_date]);


        $user_account->sync_booking_from = $request->sync_date;
        $user_account->save();

        SyncBookingJob::dispatch(true, $user_account->id)->onQueue('ba_syc_bookings');

        $approx_time = now()->addMinutes(($properties > 0 ? $properties * 7 : 1))->diffForHumans();


        return $this->apiSuccessResponse(
            '200',
            [],
            'Sync process has been started. 
            All of your previous bookings from ' . $request->sync_date . ' will be synced 
            in ChargeAutomation within ' . str_replace('from now', '', $approx_time) . '  approximately.'
        );

    }

    /**
     * @return JsonResponse
     */
    public function canSyncBookings()
    {
        $this->isPermissioned('accountSetup');
        $user_account = auth()->user()->user_account;
        if (empty($user_account->sync_booking_from))
            return $this->apiSuccessResponse(200, [], 'success');
        else
            return $this->apiErrorResponse('Already used this feature.');
    }


}

