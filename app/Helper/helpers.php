<?php

use App\BookingInfo;
use App\Repositories\DynamicVariableInContent;
use App\System\Pages;
use App\TermsAndCondition;
use App\Upsell;
use App\User;
use App\UserPms;
use App\UserAccount;
use App\PropertyInfo;
use App\Mail\GenericEmail;
use App\GuestCommunication;
use App\System\PMS\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\Settings\SecurityDamageDeposit;
use App\System\StripeCommissionBilling\StripeCommissionBilling;

if (!function_exists('set_sql_mode')) {
    /**
     * @param string $mode
     * @return bool
     */
    function set_sql_mode($mode = '')
    {
        if($mode == 'ONLY_FULL_GROUP_BY')
            return \DB::statement("SET SQL_MODE=''");
        else
            return null;
    }
}
if (!function_exists('validateLatLong')) {

    /**
     * Validates a given coordinate
     *
     * @param float|int|string $lat Latitude
     * @param float|int|string $long Longitude
     * @return bool `true` if the coordinate is valid, `false` if not
     */
    function validateLatLong($lat, $long) {
        return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $lat.','.$long);
    }
}

if (!function_exists('get_currency_symbol')){
    function get_currency_symbol($currency_code){
        try{
            $locale= config('app.locale');
            $currency= $currency_code;
            $fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
            return $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        } catch(\Exception $e) {
            Log::error($e->getMessage(),['File'=>'currency symbol helper method']);
        }


        return $currency_code;
    }
}


if (!function_exists('validateAndUpdateUserPMSStatusByPMSResponse')) {

    /**
     * PMS Response is Valid for this User Account or un authorize,
     * and update userAccount status value in DB.
     * @param UserAccount $userAccount
     * @param $response
     * @return array
     */
   function validateAndUpdateUserPMSStatusByPMSResponse(UserAccount $userAccount, $response) {
        if (count($response) > 0) {
            foreach ($response as $key => $value) {
                if (isset($value->id) && $value->id != null && $value->id > 0) {
                    /*
                     * This condition is for case when client try to update username and api-key both
                     * If while update user-account-id-at-pms doesn't match with already save value
                     * Then this means client trying to shift different PMS account
                     * SO restrict client to do that and make his/her current PMS active again
                     * Show to client a proper error message that why hr/she can't update tp these credentials
                     */
                    if(isset($userAccount->user_account_id_at_pms) && $userAccount->user_account_id_at_pms != null && $userAccount->user_account_id_at_pms != $value->id)
                    {
                        return [ 'status' => false, 'message' => "Switching PMS account is not permitted."];
                    }
                    else
                    {
                        if($userAccount->pms->is_verified != 1)
                            $userAccount->pms->update(['is_verified' => 1]);
                            
                        $userAccount->update(['user_account_id_at_pms' => $value->id]);
                        return [ 'status' => true];
                    }
                }
            }
            return [ 'status' => true];
        } else {
            $userAccount->pms->update(['is_verified' => 0]);
            return [ 'status' => false, 'message' => "These credentials do not match with the credentials at $userAccount->pms->name"];
        }
   }
}

if (!function_exists('get_related_records')) {

    /**
     * This Function Will Return Collection Of Records From
     * (Up-Sells,Terms&Conditions OR Guid-Book) Tables Against Relation
     * With Property_infos and Room_Infos.
     *
     * @param array $select
     * @param string $model_key
     * @param array $where
     * @param int $property_info_id
     * @param int $room_info_id
     * @return mixed
     */
    function get_related_records(array $select, string  $model_key, array $where, int $property_info_id, int $room_info_id=0){
        return resolve(PropertyInfo::BRIDGED_MAIN_MODELS[$model_key])->whereHas(PropertyInfo::BRIDGED_MODEL_META[$model_key]['relation'],
            function ($query)use($property_info_id,$room_info_id){
            $query->where('property_info_id',$property_info_id);
            if($room_info_id!=0){
                $query->whereRaw("(room_info_ids is null OR room_info_ids LIKE '%{$room_info_id}%')");
            }
        })->where($where)->select($select)->get();
    }
}
if(!function_exists('convertTemplateVariablesToActualData')) {


    /**
     * This Function will Convert TemplateVariables To Actual Data
     * @param string $fromModel
     * @param int $primaryKey
     * @param array $data
     * @param bool $removeExtraVar
     * @return array
     */

    function convertTemplateVariablesToActualData (string  $fromModel, int $primaryKey, array $data, $removeExtraVar = true){
        /**
         * @var $instance DynamicVariableInContent
         */
        $instance = resolve(DynamicVariableInContent::class);
        return $instance->replaceWithActualData($fromModel,$primaryKey,$data, $removeExtraVar);
    }

}
if(!function_exists('getModelTemplateVars')) {


    /**
     * @param $model
     * @param bool $get_related_vars
     * @param array $all_vars
     * @return array
     */
    function getModelTemplateVars ($model, $get_related_vars = true, $all_vars=[]){
        if(is_array($model)){
            foreach ($model as $templateModel){
                $all_vars = getModelTemplateVars($templateModel,$get_related_vars,$all_vars);
            }
        }else{
            $vars  = config('db_const.template_variables_naming');
            if(array_key_exists($model,$vars)){
                $vars = $vars[$model];
                $all_vars = array_merge($all_vars,array_flatten($vars['variables']));
                if($get_related_vars &&  !empty($vars['relationships'])){
                    $relatedModels = array_keys($vars['relationships']);
                    $all_vars = getModelTemplateVars($relatedModels,false,$all_vars);
                }
            }
        }
        return $all_vars;
    }

}
if(!function_exists('getEmailTypeTempVars')) {

    /**
     * @param $email_type
     * @return array
     */
    function getEmailTypeTempVars ($email_type){
        $email  = config("db_const.emails.heads.".$email_type);
        $all_vars = getModelTemplateVars($email['model']);
        if(!empty($email['extra_model_vars'])){
            $all_vars = getModelTemplateVars($email['extra_model_vars'],false,$all_vars);
        }
        if(!empty($email['skip_variables'])){
            $all_vars = array_diff($all_vars,$email['skip_variables']);
        }

        sort($all_vars);
        $all_vars = array_values(array_unique($all_vars));

        return $all_vars;
    }

}
if (!function_exists('get_collection_by_applying_filters')) {

    /**
     * Use this function to get your Collection records by custom sorting , filtering and you can also apply strict constraint record fetching
     * Basic Usage  Custom Build Datatable or pagination.
     * Sample data as function parameter available config file => "config('db_const.custom_datatable_filters_sample_json')"
     * @param array $filtersArray
     * @param string $modelName
     * @return mixed
     * @throws Exception
     */

    function get_collection_by_applying_filters(array $filtersArray, string $modelName)
    {
        if (empty($modelName))
            throw new \Exception('App Model Name Not Valid');

        if (!empty($filtersArray)) {

            $searchInColumn   = [];
            $searchStr = '';

            $sortColumn = !empty($filtersArray['sort']['sortColumn']) ? $filtersArray['sort']['sortColumn'] : '';
            $queryConstraints = !empty($filtersArray['constraints']) ? $filtersArray['constraints'] : [];
            $recordsPerPage = !empty($filtersArray['recordsPerPage']) ? $filtersArray['recordsPerPage'] : 10;
            $columnsToSelect = !empty($filtersArray['columns']) ? $filtersArray['columns'] : [];
            $relations = !empty($filtersArray['relations']) ? $filtersArray['relations'] : [];
            $whereHas = !empty($filtersArray['whereHas']) ? $filtersArray['whereHas'] : [];
            $page = !empty($filtersArray['page']) ? filter_var($filtersArray['page']) : 1;

            $sortOrder = (!empty($filtersArray['sort']['sortOrder']) && !empty($sortColumn)
                && in_array(strtolower($filtersArray['sort']['sortOrder']), ['asc', 'desc']))
                ? $filtersArray['sort']['sortOrder'] : 'Asc';

            if (!empty($filtersArray['search']['searchStr'])) {
                $searchStr = "%{$filtersArray['search']['searchStr']}%";
                $searchInColumn = ! empty($filtersArray['search']['searchInColumn']) ? $filtersArray['search']['searchInColumn'] : [];
            }

            //this if will calculate where to redirect user, guess page number and redirect there
            if(isset($filtersArray['redirect_to_record']) && isset($filtersArray['redirect_which_record']) && $filtersArray['redirect_to_record'] && $filtersArray['redirect_which_record'])
            {
                $all_records = resolve($modelName)::with($relations)->where($queryConstraints);
                
                // Check and Apply if Date Filter Applied eg Today or Custom Date
                if (!empty($filtersArray['is_custom_date']) && $filtersArray['is_custom_date']) {
                    $constrain = "DATE(CONVERT_TZ(check_in_date,'GMT',property_time_zone))";
                    $checks = [$filtersArray['dateOne'],$filtersArray['dateTwo']];
                    $all_records = $all_records->whereBetween(DB::raw($constrain),$checks);
                }

                // Apply Other Generic Constrains
                $all_records = $all_records->where(
                        function ($query) use ($searchStr, $searchInColumn) {
                            foreach ($searchInColumn as $column) {
                                $query->orWhere("{$column}", 'LIKE', $searchStr);
                            }
                        }
                    )
                    ->where('id', '>=', $filtersArray['redirect_which_record'])
                    ->count();
                
                if($all_records%$recordsPerPage == 0)
                {
                    $page = $all_records/$recordsPerPage;
                }
                else
                {
                    $page = intval($all_records/$recordsPerPage)+1;
                }
            }

            $data = resolve($modelName)::with($relations)->where($queryConstraints);
            //Where In Constrains
            if(!empty($whereHas)){
                foreach ($whereHas as $in){
                  $data->whereIn($in['col'],$in['values']);
                }
            }
            // Check and Apply if Date Filter Applied eg Today or Custom Date
            if (!empty($filtersArray['is_custom_date']) && $filtersArray['is_custom_date']) {
                $constrain = "DATE(CONVERT_TZ(check_in_date,'GMT',property_time_zone))";
                $checks = [$filtersArray['dateOne'],$filtersArray['dateTwo']];
                $data->whereBetween(DB::raw($constrain),$checks);
            }

            // Apply Other Generic Constrains
            $data->where(
                function ($query) use ($searchStr, $searchInColumn) {
                    foreach ($searchInColumn as $column) {
                        $query->orWhere(DB::raw("{$column}"), 'LIKE', $searchStr);
                    }
                }
            )->select($columnsToSelect);

            if (!empty($sortColumn)) // If Sort changed then apply
                $data->orderBy($sortColumn, $sortOrder);

            return $data->paginate($recordsPerPage, $columns = ['*'], 'page', $page); // Paginate
        } else {
            throw new \Exception('Filters not Valid.');
        }
    }
}


if (!function_exists('generateUniqueUseAblePropertyAPiKeyForPMS')) {
    /**
     * @param $userAccountId
     * @return mixed
     */
    function generateUniqueUseAblePropertyAPiKeyForPMS($userAccountId) {
        $previousApiKeysArray = PropertyInfo::where('user_account_id', $userAccountId)->pluck('property_key')->toArray();
        $keyValid = false;
        while(!$keyValid){
            $newApiKey = \Uuid::generate()->string;
            if(!in_array($newApiKey, $previousApiKeysArray))
                $keyValid = true;
        }
        return $newApiKey;
    }
}


if (!function_exists('getUserPMSStepsCompletedStatus')) {
    /**
     * Return Users's status of PMS Steps Completed
     * @param $userAccountId
     * @return array
     * @throws Exception
     */
    function getUserPMSStepsCompletedStatus($userAccountId)
    {
        $userAccount = resolve(UserAccount::class):: with(['pms' => function ($query) {$query->where('is_verified',1)
            ->select('user_account_id');}])
        ->with(['properties_info'       => function ($query) {$query->select('user_account_id')->first();}])
        ->with(['userGeneralPreferences'=> function ($query) {$query->select('user_account_id')->first();}])
        ->with(['user_bookings_source'  => function ($query) {$query->where('property_info_id', 0)->select('user_account_id')->first();}])
        ->with(['user_payment_gateways' => function ($query) {$query->where([['is_verified', 1], ['property_info_id', 0]])
            ->select('user_account_id')->first();}])->select('id', 'stripe_customer_id', 'integration_completed_on')
            ->where('id', $userAccountId)->first();

        if (is_null($userAccount))
            throw new \Exception('User Account Id not Valid!');

        $stepsCompleted = [
            'step1' => ! empty($userAccount->pms),
            'step2' => ! empty($userAccount->user_bookings_source->count()),
            'step3' => ! empty($userAccount->user_payment_gateways->count()),
            'step4' => ! empty($userAccount->userGeneralPreferences->count()),
            'step5' => ! empty($userAccount->properties_info->count())
        ];

        if ((empty($userAccount->integration_completed_on) || empty($userAccount->stripe_customer_id))
            && $stepsCompleted['step1'] && $stepsCompleted['step5']) {

            if (empty($userAccount->integration_completed_on)) {
                $userAccount->update(['integration_completed_on' => now()->toDateTimeString(),
                'status' => config('db_const.user_account.status.active.value')]); // Update Integration Completed Status
            }

            createDefaultBillingCustomer($userAccount);
        }


        return $stepsCompleted;
    }
}

if (!function_exists('pageTitle')) {

    function pageTitle($routeName) {
        $pageTitlesArray= array (
            'login' => 'ChargeAutomation - Login',
            'register' => 'ChargeAutomation - Sign Up',
            'password_reset' => 'ChargeAutomation - Password Reset',
            'activate-user' => 'ChargeAutomation - Confirmation',
            'ba_integration_instructions' => 'Integration Guide - BookingAutomation',
            'beds24_integration_instructions' => 'Integration Guide - Beds24',
            'dashboard' => 'Dashboard',
            'v2dashboard' => 'Dashboard',
            'properties' => 'Properties',
            'v2properties' => 'Properties',
            'property_details' => 'Property Detail',
            'v2property_details' => 'Property Detail',
            'bookings' => 'Bookings',
            'v2bookings' => 'Bookings',
            'booking_details' => 'Booking Detail',
            'v2booking_details' => 'Booking Detail',
            'bookingDetailPage' => 'Booking Detail',
            'manageteam' => 'Manage Team',
            'v2manageteam' => 'Manage Team',
            'generalSettings' => 'General Settings',
            'v2generalSettings' => 'Settings - Online Check-in',
            'settings' => 'Preferences',
            'v2settings' => 'Preferences',
            'pmsintegration' => 'Global Settings',
            'v2pmsintegration' => 'Account Setup',
            'viewPMS_SetupStep1' => 'Account Setup - PMS',
            'viewPMS_SetupStep2' => 'Account Setup - Payment Rules',
            'viewPMS_SetupStep3' => 'Account Setup - Payment Gateway',
            'viewPMS_SetupStep4' => 'Account Setup - Guest Experience',
            'viewPMS_SetupStep5' => 'Account Setup - Activate Properties',
            'memberprofile' => 'Member Profile',
            'v2memberprofile' => 'Member Profile',
            'profile' => 'Client Profile',
            'v2profile' => 'Client Profile',
            'notifications' => 'Notifications',
            'email_list' => 'Email List',
            'v2allNotifications' => 'Notifications',
            'upsells' => 'Upsells',
            'upsellAdd' => 'Add Upsell',
            'upsellTypes' => 'Upsell Types',
            'upsellAddType' => 'Add Upsell Types',
            'tac' => 'Terms & Condations',
            'tacAdd' => 'Add Terms & Condations',
            'upsellOrders' => 'Upsell Orders',
            'v2email_settings' => 'Manage Emails',
            'guideBooks' => 'Guide Books',
            'guideBooksAdd' => 'Add Guide Book',
            'getGuideBookTypes' => 'Guide Book Types',
            'guideBooksAddType' => 'Add Guide Book Types',
            'cancelBdcBookingDetailPage'=> 'ChargeAutomation | Invalid Credit Card Cancellation'
        );
        if(array_key_exists($routeName, $pageTitlesArray)){
            return $pageTitlesArray[$routeName];
        }
        else{
            return 'ChargeAutomation';
        }
    }
}



if (!function_exists('returnRoute')) {

    function returnRoute($routeName) {
        $routeNamesArray= array (
            'dashboard' => 'v2dashboard',
            'v2dashboard' => 'dashboard',
            'properties' => 'v2properties',
            'v2properties' => 'properties',
            'property_details' => 'v2properties',
            'bookings' => 'v2bookings',
            'v2bookings' => 'bookings',
            'booking_details' => 'v2bookings',
            'manageteam' => 'v2manageteam',
            'v2manageteam' => 'manageteam',
            'memberprofile' => 'v2manageteam',
            'generalSettings' => 'v2generalSettings',
            'v2generalSettings' => 'generalSettings',
            'settings' => 'v2settings',
            'v2settings' => 'settings',
            'pmsintegration' => 'v2pmsintegration',
            'v2pmsintegration' => 'pmsintegration',
            'viewPMS_SetupStep1' => 'pmsintegration',
            'viewPMS_SetupStep2' => 'pmsintegration',
            'viewPMS_SetupStep3' => 'pmsintegration',
            'viewPMS_SetupStep4' => 'pmsintegration',
            'profile' => 'v2dashboard',
            'notifications' => 'v2allNotifications',
            'v2allNotifications' => 'notifications',
            'email_list' => 'v2bookings',);
        if(array_key_exists($routeName, $routeNamesArray)){
            return $routeNamesArray[$routeName];
        }
        else{
            return 'v2dashboard';
        }
    }
}


if (!function_exists('notificationsPagination')) {

    function notificationsPagination() {

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        /*
         * where('user_id', $user_id)
            ->
         */

        $builder = GuestCommunication::where('user_account_id', $user_account_id)
            ->whereNotNull('pms_booking_id')
            ->where(function($query) {
                $query->where(function($q) {
                    $q->where('alert_type', config('db_const.notifications.alert_type.message'))
                        ->where('is_guest', '!=', config('db_const.notifications.alert_for.client'));
                })->orWhere('alert_type', '!=', config('db_const.notifications.alert_type.message'));
            });
        return $builder;
    }
}


if (!function_exists('pms_redirect_by_checking_steps_completed')) {

    /**
     *  Move PMS To Steps which are incomplete yet!
     * @param $step
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View|void
     */
     function pms_redirect_by_checking_steps_completed($step) {
        try{
            $stepsDetail = getUserPMSStepsCompletedStatus(auth()->user()->user_account_id);

            /*foreach ($stepsDetail as $key => $pmsStepCompleted) {
                if ((!$pmsStepCompleted) && ((int)str_replace('step', '', $key) < $step))
                    return redirect(route('viewPMS_Setup' . ucfirst($key)));
            }*/

            if (empty($stepsDetail['step1']))
                return redirect(route('viewPMS_SetupStep1' ));

            return view('v2.client.pms_integration.setup_step' . $step);
        } catch (\Exception $e) {
            abort(500,'Oops something Wrong. Fail to load page');
        }
     }
}

if (!function_exists('filteredChannelCode')) {
    /**
     *  To check if we need to use internal channel code or not!
     * -- From PMS while booking fetch
     * @param $original_channel_code
     * @return \Illuminate\Config\Repository|mixed
     */
     function filteredChannelCode($original_channel_code) {
        try {
            /* check if channel code is in duplicate list
             * If true then use CA internal channel code
            */ 
            if(in_array($original_channel_code, config('db_const.booking_source_forms.duplicate_channel_codes'))) {
                return config('db_const.booking_source_forms.internal_channel_codes.'.$original_channel_code);
            } else {
                return $original_channel_code;
            }
        } catch (\Exception $e) {
            return $original_channel_code;
        }
    }
}


if (!function_exists('CheckBookingSourceSettings')) {

    /**
     * @param PropertyInfo $propertyInfo
     * @return int|mixed
     */
    function GetPropertyInfoIdForBridgeSettings(PropertyInfo $propertyInfo)
    {
        return $propertyInfo->use_bs_settings != 0 ? $propertyInfo->id : 0;
    }

}


if (!function_exists('CheckAuthReAuthEnabledOrDisabled')) {
    /**
     * @param \App\UserBookingSource|null $userBookingSource
     * @param string $AuthType
     * @return CreditCardValidation|SecurityDamageDeposit
     */
    function CheckAuthReAuthEnabledOrDisabled(\App\UserBookingSource $userBookingSource = null, string $AuthType)
    {
        if ($AuthType == 'CC') {
            $settings = !empty($userBookingSource->credit_card_validation_setting[0]->settings)
                ? $userBookingSource->credit_card_validation_setting[0]->settings
                : \App\UserBookingSource::DEFAULT_CREDIT_CARD_SETTING;
        } else {
            $settings = !empty($userBookingSource->security_damage_deposit_setting[0]->settings)
                ? $userBookingSource->security_damage_deposit_setting[0]->settings
                : \App\UserBookingSource::DEFAULT_SECURITY_DEPOSIT_SETTING;

        }

        return $AuthType == 'CC' ? new CreditCardValidation($settings) : new SecurityDamageDeposit($settings);
    }
}


if (!function_exists('generatePropertyNotificationUrlForBookingAutomation')) {

    function generatePropertyNotificationUrlForBookingAutomation($userAccountId)
    {
        /**
         * @var $urlGen \App\System\PMS\BookingAutomation\NotificationUrlBookingAutomation
         */
        $urlGen = resolve(\App\System\PMS\BookingAutomation\NotificationUrlBookingAutomation::class);
        $urlGen->enableChannelCode(true);
        $urlGen->enablePropertyId(true);
        $urlGen->enableMetaUserAccountId(true, $userAccountId);
        $urlGen->enableGroupId(true);
        return $urlGen->generateURL();
    }
}

if (!function_exists('validateAttachedPermission')) {
    /**
     * @param \App\User $user
     * @param string $permissionName
     */
    function validateAttachedPermission(\App\User $user, string $permissionName)
    {
        try{
            $arrayToManageFewPermissions  = ['syncProperties' => 'editProperty', 'charges'=>'editBooking', 'authorize'=>'editBooking', 'capture' =>'editBooking','refund'=>'editBooking'];
            $arrayToManageFewPermissions2 = ['charges'=>'editTransaction', 'authorize'=>'editTransaction', 'capture' =>'editTransaction','refund'=>'editTransaction'];
            $permissionName = isset($arrayToManageFewPermissions[$permissionName]) ? $arrayToManageFewPermissions[$permissionName] : (isset($arrayToManageFewPermissions2[$permissionName]) ? $arrayToManageFewPermissions2[$permissionName] : $permissionName);
            $permissionNameExploded = preg_split('/(?<=\\w)(?=[A-Z])/', $permissionName);
            if ($permissionNameExploded[0] == 'edit' || $permissionNameExploded[0] == 'delete') {
                unset($permissionNameExploded[0]);
                $viewPermission = "view".implode('', $permissionNameExploded);
                if (!$user->hasPermissionTo($viewPermission))
                    $user->givePermissionTo($viewPermission);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage(), ['stackTrace' => $e->getTraceAsString()]);
        }
    }
}

if (!function_exists('validateDetachedPermission')) {
    /**
     * @param \App\User $user
     * @param string $permissionName
     */
    function validateDetachedPermission(\App\User $user, string $permissionName)
    {
        try{
            $arrayToManageFewPermissions = ['viewProperty' => ['syncProperties'], 'viewBooking' =>   ['charges','authorize', 'capture', 'refund' ]];
            $permissionToDetach = array();
            $permissionNameExploded = preg_split('/(?<=\\w)(?=[A-Z])/', $permissionName);
            if ($permissionNameExploded[0] == 'view'){
                unset($permissionNameExploded[0]);
                $permissionNameImploded = implode('', $permissionNameExploded);
                if (isset($arrayToManageFewPermissions[$permissionNameImploded]))
                    $permissionToDetach = $arrayToManageFewPermissions[$permissionNameImploded];
                $permissionToDetach[]  = "edit".$permissionNameImploded;
                $permissionToDetach[]  = "delete".$permissionNameImploded;
                foreach ($permissionToDetach as $permission) {
                    if ($user->hasPermissionTo($permission))
                        $user->revokePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage(), ['stackTrace' => $e->getTraceAsString()]);
        }
    }
}

if (!function_exists('sendMailToAppDevelopers')) {

    /**
     * Send Email to App Developers.
     * @param $emailSubject
     * @param $emailMessage
     * @param $anyJsonData
     */
    function sendMailToAppDevelopers($emailSubject, $emailMessage, $anyJsonData)
    {
        try {
            Mail::to(config('db_const.app_developers.emails.to'))->cc(
                config('db_const.app_developers.emails.cc'))->send(
                new GenericEmail(
                    array(
                        'subject' => $emailSubject,
                        'markdown' => 'emails.network_error_email_markdown',
                        'noReply' => true,
                        'message' => $emailMessage,
                        'any_json_object' => $anyJsonData
                    )
                )
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Stack' => $e->getTraceAsString()]);
        }
    }
}

if (!function_exists('GetMailSubject')){
  function GetMailSubject ($array, $attr, $val, $strict = FALSE) {
    if (!is_array($array)) return FALSE;
    foreach ($array as $key => $inner) {
      if (!is_array($inner)) return FALSE;
      if (!isset($inner[$attr])) continue;
      if ($strict) {
        //if ($inner[$attr] === $val) return $key;
        if ($inner[$attr] === $val) return $inner['title'];
      }
      else{
        //if ($inner[$attr] == $val) return $key['title'];
        if ($inner[$attr] == $val) return $inner['title'];
      }
    }
    return NULL;
  }

}

if (! function_exists('float_compare')) {
    function float_compare(float $a, float $b)
    {
        return abs($a - $b) < 0.00001;
    }
}

if (! function_exists('UnAuthorizedUserPMSRevertBackChanges')) {

    /**
     * @param $userPmsId
     * @param bool $isNew
     * @param bool $previousIsVerifiedStatus
     * @param string|null $previousFormData
     * @param string|null $previousUniqueKey
     */
    function UnAuthorizedUserPMSRevertBackChanges($userPmsId, bool $isNew, bool $previousIsVerifiedStatus, string $previousFormData = null, string $previousUniqueKey = null)
    {
        $userPms = resolve(UserPms::class)::findOrFail($userPmsId);
        if (!$isNew){
            $userPms->is_verified = $previousIsVerifiedStatus;
            $userPms->form_data   = $previousFormData;
            $userPms->unique_key   = $previousUniqueKey;
            $userPms->save();
            Log::info('Update');
        } else {
            $userPms->delete();
            Log::info('delete');
        }
    }
}

if (! function_exists('applyConstraintsToAvoidUpdatingUniqueKey')) {
    /**
     * @param array $requestCredentials
     * @param UserPms|null $userPMS
     * @return array
     */
    function applyConstraintsToAvoidUpdatingUniqueKey(array $requestCredentials, UserPms $userPMS = null)
    {
        if (!is_null($userPMS)) {
            $formData = json_decode($userPMS->form_data, true);
            foreach ($formData['credentials'] as $key => $value ) {
                if ($formData['credentials'][$key]['is_unique']) {
                    $name = $formData['credentials'][$key]['name'];
                    $requestCredentials[$name] = $formData['credentials'][$key]['value'];
                    break;
                }
            }
        }
        return $requestCredentials;
    }
}

if (! function_exists('append_notify_url')) {

    function append_notify_url(Property $prop, string $new_notify_url)
    {
        $notify_url_base = config('db_const.ba_pms_form.notify-url', 'https://app.chargeautomation.com/api/booking_automation?');

        $notify_url_array = explode("\n", $prop->caNotifyURL);
        $found_in_all = preg_match("~\b$notify_url_base\b~", $prop->caNotifyURL);
        foreach ($notify_url_array as $j => $url) {
            if (empty($url)) {
                unset($notify_url_array[$j]);
            }
        }
        if($found_in_all){
            foreach ($notify_url_array as $k => $url){
                $update = preg_match("~\b$notify_url_base\b~", $url);
                if($update){
                    $notify_url_array[$k] = $new_notify_url;
                }
                if(empty($url)){
                    unset($notify_url_array[$k]);
                }
            }
        }else{
            array_push($notify_url_array, $new_notify_url);
        }

        return implode("\n", $notify_url_array);
    }
}

if (! function_exists('matchNotifyUrl')) {

    function matchNotifyUrl(Property $prop)
    {
        if (!empty($prop)) {
            $notify_url_base = config('db_const.ba_pms_form.notify-url', 'https://app.chargeautomation.com/api/booking_automation?');
            $found_in_all = preg_match("~\b$notify_url_base\b~", $prop->caNotifyURL, $existing_url);

            if ($found_in_all == 1 && !empty($existing_url)) {
                return "Notify URL is verified.";
            } else {
                return "Notify URL is not verified.";
            }

        } else {
            return "Notify URL is not verified.";
        }
    }
}

if (! function_exists('setCardExceptionReadable')) {
    /**
     * @param string $message
     * @return string
     */
    function setCardExceptionReadable(string $message)
    {
        if (strpos(strtolower($message), 'no such payment_method') !== false) {
            return \App\BookingInfo::CREDIT_CARD_NOT_VALID_MESSAGE;
        }
        return $message;
    }
}

if (! function_exists('getDefaultEmailAddressOfUserAccount')) {
    /**
     * @param UserAccount $userAccount
     * @return mixed
     */
    function getDefaultEmailAddressOfUserAccount(UserAccount $userAccount)
    {
        return $userAccount->email != null ?  $userAccount->email : $userAccount->users->first()->email;
    }
}

if (! function_exists('userSecurityDepositSettingsUpdateForBillingReminder')) {

    /**
     * @param UserAccount $userAccount
     * @param bool $securityDepositSettingStatus
     */
    function userSecurityDepositSettingsUpdateForBillingReminder(UserAccount $userAccount, $securityDepositSettingStatus)
    {
        if ((($securityDepositSettingStatus == true) || ($securityDepositSettingStatus == 'true'))
            && ($userAccount->sd_activated_on == null))
            $userAccount->update(['sd_activated_on' => now()->toDateTimeString()]);
    }
}


if(! function_exists('getInitialsFromString')){

    /*
     * get Initials for user name, company name and property name form (name) string
     */
    function getInitialsFromString($name, $length=3 ){

        $initials='';
        $name = preg_replace("/[^A-Za-z\s]/",'', $name);

        if(!empty($name)) {

            $exploded_name = explode(" ", $name);

            if (!empty($exploded_name) && is_array($exploded_name) && count($exploded_name) > 0) {

                foreach ($exploded_name as $w) {
                    if($w != '') {
                        $initials .= substr($w, 0, 1);
                    }
                }

            } else {
                $initials = substr($name, 0, 1);
            }
        }

        return substr($initials,0, $length);
    }
}

if (! function_exists('checkImageExists')) {
    function checkImageExists($img, $name, $image_flag)
    {
        $initials='';
        $directory = 'db_const.logos_directory.'.$image_flag;
        if( empty($img) || $img == config($directory.'.default_image_name')
            || $img == config($directory.'.old_default_image_name')
            || !file_exists(public_path(config($directory.'.img_path').$img))) {

            if ( empty(config($directory.'.default_image_name')) || !file_exists(public_path(config($directory.'.img_path').config($directory.'.default_image_name')))) {
                $initials = getInitialsFromString($name,2);
            }
            else {
                $img = config($directory.'.default_image_name');
            }
        }
        return [
            $image_flag.'_initial'=>$initials,
            $image_flag.'_image'=>$img,
            'is_initial'=> !empty($initials)
        ];
    }
}

if (! function_exists('getImageNameOrInitials')) {
    function getImageNameOrInitials($data,$image_flag)
    {
        $image_details = null;
        switch ($image_flag) {
            case config('db_const.logos_directory.user.value'):
                $image_details = checkImageExists($data->user_image, $data->name, $image_flag);
                break;
            case config('db_const.logos_directory.company.value'):
                $company = $data->user_account;
                $image_details = checkImageExists($company->company_logo, $company->name, $image_flag);
                break;
            case config('db_const.logos_directory.booking_source.value'):
            case config('db_const.logos_directory.property.value'):
                $image_details = checkImageExists($data->logo, $data->name, $image_flag);
                break;
        }
        return $image_details;
    }
}

if (! function_exists('log_exception_by_exception_object')) {

    /**
     * @param $exception_object
     * @param string|array|null $any_json_content
     * @param null | $level //default error
     */
    function log_exception_by_exception_object($exception_object, $any_json_content = null, $level = null)
    {
        /**
         * @var $exception_object Exception
         */
        $level = $level?:'error';
        Log::$level($exception_object->getMessage(),
            [
                'File'=> $exception_object->getFile(),
                'Line'=> $exception_object->getLine(),
                'content' => $any_json_content,
                'StackTrace' => $exception_object->getTraceAsString()
            ]
        );
    }
}

if (! function_exists('get_property_time_zone')) {
    function get_property_time_zone(PropertyInfo $property_info)
    {
        return (!empty($property_info->time_zone) ? $property_info->time_zone : $property_info->user_account->time_zone);
    }
}

if (! function_exists('get_property_info_email')) {
    function get_property_info_email(PropertyInfo $property_info)
    {
        return (!empty($property_info->property_email) ? $property_info->property_email
            : (!empty($property_info->user_account->email) ? $property_info->user_account->email
                : $property_info->user_account->users->first()->email));
    }
}


if (! function_exists('get_billing_config_file_name')) {
    function get_billing_config_file_name()
    {
          return ((config('app.env') === 'local' || config('app.debug') == true))  ?
              'db_const.stripe_commission_billing_test' : 'db_const.stripe_commission_billing';

    }
}

if (! function_exists('handleSpecialCharacters')) {
    /**
     * Return String by handling Special characters encoded to decoded string
     * @param string|null $str
     * @return string|string[]|null
     */
    function handleSpecialCharacters(string $str = null)
    {
        return preg_replace_callback("/(&#[0-9]+;)/", function($m)
           {return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");}, $str);
    }
}


if (! function_exists('createDefaultBillingCustomer')) {
    /**
     * Create Default Billing Customer on stripe and attach default Plan
     * @param UserAccount $userAccount
     */
    function createDefaultBillingCustomer(UserAccount $userAccount)
    {
        // Create Billing Customer on stripe and attach default Plans
        if (empty($userAccount->stripe_customer_id)
            && ((config('app.env') === 'local' || config('app.debug') == true))) {  // TODO Remove For Releases
            resolve(StripeCommissionBilling::class)->createBillingCustomerWithNoCardAndAddDefaultBillingPlan($userAccount);
        }
    }
}

if (! function_exists('room_infos_to_string')) {
    /**
     * @param $value
     * @return string|null
     */
    function room_infos_to_string($value)
    {
        if (!empty($value)) {
            $value = array_map( function($value) { return (int)$value; }, $value);
            $value = implode('","', $value);
            return !empty($value) ? '"'.$value.'"' : null;
        }
        return null;
    }
}

if (! function_exists('get_config_column_values')) {
    /**
     * @param $file_name
     * @param $column_name
     * @param $column_value
     * @return \Illuminate\Config\Repository|mixed
     */
    function get_config_column_values($file_name, $column_name, $column_value)
    {
        $key = config('db_const.'.$file_name.'.' . $column_name.'.get_key.'.$column_value);
        return config('db_const.'.$file_name.'.' . "$column_name.$key");
    }
}

if (! function_exists('get_two_letter_country_code')) {

    function get_two_letter_country_code ($countryNameFull) {

        if(empty($countryNameFull))
            return '';

        $countries = json_decode(file_get_contents(__DIR__ . '/../Json/country_names_from_abbr_code.json'), true);

        if(in_array($countryNameFull, $countries))
            if($key = array_search($countryNameFull, $countries))
                return $key;

        foreach($countries as $key => $value)
            $countries[$key] = strtolower($value);

        if(in_array($countryNameFull, $countries))
            if($key = array_search($countryNameFull, $countries))
                return $key;

        return substr($countryNameFull, 0, 2);
    }
}

if (! function_exists('get_country_name_full_by_code')) {

    function get_country_name_full_by_code ($two_letter_code) {

        if(empty($two_letter_code))
            return '';

        $countries = json_decode(file_get_contents(__DIR__ . '/../Json/country_names_from_abbr_code.json'), true);

        if(key_exists($two_letter_code, $countries))
            return $countries[$two_letter_code];

        $two_letter_code = strtoupper($two_letter_code);

        if(key_exists($two_letter_code, $countries))
            return $countries[$two_letter_code];

        return $two_letter_code;
    }
}

if (!function_exists('company_email_address')) {
    function company_email_address(BookingInfo $booking_info)
    {
        $user_account = $booking_info->user_account;
        $user = User::where('user_account_id', $user_account->id)->first();
        $property_info = $booking_info->property_info;
        $email = '';
        if (!empty($user_account->email)) {
            $email = $user_account->email;
        } elseif (!empty($user->email)) {
            $email = $user->email;
        } elseif (!empty($property_info->property_email)) {
            $email = $property_info->property_email;
        }
        return $email;
    }
}

if (!function_exists('upsell_price_for_booking')) {

    function upsell_price_for_booking(BookingInfo $booking_info, $upsell_value_type, $upsell_value)
    {
        switch ($upsell_value_type) {
            case config('db_const.upsell_listing.value_type,percentage'):
                return round(($booking_info->total_amount / 100) * $upsell_value);
                break;
            default:
                return $upsell_value;
                break;
        }
    }
}
if (!function_exists('datatable_query_filter')) {

    function datatable_query_filter()
    {
        return [
            "recordsPerPage" => 10,
            "page" => 1,
            'columns' =>   ['*'], // Sample ['id', 'name']
            'relations' => [], // Sample ['booking_info', 'property_info']
            "sort" => [
                "sortOrder" => "ASC",
                "sortColumn" => "id",
            ],
            "constraints" => [ ], // Sample [['id', '>', 0], ['name', 'Example']]
            "search" => [
                "searchInColumn" => [
                    0 => "id",
                    //any other columns
                ],
                "searchStr" => "", // any string to search
            ],
        ];
    }
}
