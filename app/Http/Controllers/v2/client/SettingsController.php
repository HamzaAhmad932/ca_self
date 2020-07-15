<?php

namespace App\Http\Controllers\v2\client;

use App\Activity;
use App\BookingSourceForm;
use App\FetchBookingSetting;
use App\Http\Controllers\Controller;
use App\PreferencesForm;
use App\ReattemptPolicy;
use App\Repositories\BookingSources\BookingSources;
use App\UserAccount;
use App\UserPreference;
use App\UserPreferencesNotificationSettings;
use Illuminate\Http\Request;
use Validator;

class SettingsController extends Controller
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

        $this->isPermissioned('preferences');
        return view('v2.client.settings.preference-settings');
    }

    /**
     * Fetch Booking Settings
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bookingChannelSetting()
    {
        $this->isPermissioned('accountSetup');
        return view('v2.client.settings.preference-settings');
        //return view('v2.client.settings.booking_channel_settings');
    }

    /**
     * @param null $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserFetchBookingSettings($message = null)
    {
        $this->isPermissioned('accountSetup');
        /**
         * @var $booking_source_repo BookingSources
         */
        $booking_source_repo = resolve(BookingSources::class);
        $settings = $booking_source_repo->getUserFetchBookingSettings(auth()->user()->user_account);
        $message = $message ?: 'Success';
        return $this->apiSuccessResponse(200, $settings, $message);
    }

    /**
     *  fetchBookingSettingOnOff per Booking Source
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBookingSettingOnOff(Request $request)
    {
        $this->isPermissioned('accountSetup');
        $validator = Validator::make($request->all(), [
            'booking_source_form_id' => 'required|integer',
            'status' => 'required|bool',]);

        if (!$validator->passes())
            return $this->errorResponse($validator->errors()->first(), 422);

        FetchBookingSetting::updateOrCreate(
            [
                'user_account_id' => auth()->user()->user_account_id,
                'booking_source_form_id' => $request->booking_source_form_id
            ],
            ['status' => $request->status, 'user_id' => auth()->user()->id]
        );

        $message = 'Successfully' . ($request->status ? ' Enabled' : ' Disabled');

        return $this->apiSuccessResponse(
            200,
            ['booking_source_form_id' => $request->booking_source_form_id,
                'status' => $request->status],
            $message
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBookingSettingOnOffAll(Request $request)
    {
        $this->isPermissioned('accountSetup');

        $validator = Validator::make($request->all(), ['status' => 'required|bool',]);

        if (empty($validator->passes()))
            return $this->errorResponse($validator->errors()->first(), 422);

        $insert = array();
        $user_account = auth()->user()->user_account;
        $message = 'Successfully ' . ($request->status ? ' Enabled' : ' Disabled');

        $booking_sources = BookingSourceForm::where(
            [
                ['pms_form_id', $user_account->pms->pms_form_id],
                ['status', 1]
            ]
        )->pluck('id')->toArray();

        $settings = FetchBookingSetting::where('user_account_id', $user_account->id)
            ->pluck('booking_source_form_id')->toArray();

        foreach ($booking_sources as $booking_source) {
            if (!in_array($booking_source, $settings)) {
                array_push($insert,
                    [
                        'user_account_id' => $user_account->id,
                        'booking_source_form_id' => $booking_source,
                        'status' => $request->status,
                        'user_id' => auth()->user()->id
                    ]
                );
            }
        }

        // Update Previous Records
        FetchBookingSetting::where('user_account_id', $user_account->id)->update(
            [
                'status' => $request->status,
                'user_id' => auth()->user()->id
            ]
        );

        // Insert New Records
        if (!empty($insert))
            FetchBookingSetting::insert($insert);

        return $this->getUserFetchBookingSettings($message);
    }

    public function fetchNotificationSettings()
    {

        $this->isPermissioned('preferences');
        $id = auth()->user()->user_account_id;
        $user_account = UserAccount::find($id);
        $this->isSuperClient($user_account);

        $allActivities = Activity::whereIn('preference_type', [1, 2, 3])->where('status', 1)->get();
        $clientSettings = UserPreferencesNotificationSettings::where('user_account_id', $id)->get();

        //find company email
        $company_email = $user_account->users->first()->email;

        /**Default Value to notify Client from Config */
        $defaultNotifyConfig = json_decode(config('db_const.user_preferences_notify_default_settings.notify_settings'));

        $parseClientSettingsForViewArr = [];

        if (!empty($allActivities)) {

            foreach ($allActivities as $activityKey => $activity) {
                $parseClientSettingsForViewArr[$activity->id]['id'] = $activity->id;
                $parseClientSettingsForViewArr[$activity->id]['name'] = $activity->name;
                $parseClientSettingsForViewArr[$activity->id]['desc'] = $activity->desc;
                $parseClientSettingsForViewArr[$activity->id]['type'] = $activity->preference_type;
                $parseClientSettingsForViewArr[$activity->id]['to_email'] = '';
                $parseClientSettingsForViewArr[$activity->id]['cc_email'] = '';
                $parseClientSettingsForViewArr[$activity->id]['bcc_email'] = '';
                $parseClientSettingsForViewArr[$activity->id]['sms_to_company'] = (isset($defaultNotifyConfig->sms_to_company) ? $defaultNotifyConfig->sms_to_company : 0);
                $parseClientSettingsForViewArr[$activity->id]['email_to_company'] = (isset($defaultNotifyConfig->email_to_company) ? $defaultNotifyConfig->email_to_company : 0);
                $parseClientSettingsForViewArr[$activity->id]['email_to_preferences'] = (isset($defaultNotifyConfig->email_to_preferences) ? $defaultNotifyConfig->email_to_preferences : 0);
                $parseClientSettingsForViewArr[$activity->id]['email_to_guest'] = (isset($defaultNotifyConfig->email_to_guest) ? $defaultNotifyConfig->email_to_guest : 0);

                $parseClientSettingsForViewArr[$activity->id]['to_email'] = $company_email;


                $currentActivitySettingCollection = $clientSettings->where('activity_id', $activity->id)->first();

                //add company email to the starting of to_email box

                if ($currentActivitySettingCollection != null) {

                    if ($currentActivitySettingCollection->notify_settings != null) {

                        $currentActivitySettings = json_decode($currentActivitySettingCollection->notify_settings);
                        // echo "<pre>";print_r($currentActivitySettings);exit;
                        // foreach ($currentActivitySettings as $keyEmailSettings => $clientEmailSettings) {
                        //     $parseClientSettingsForViewArr[$activity->id][$keyEmailSettings] = $clientEmailSettings;
                        // }
                        if (isset($currentActivitySettings->email_to_company) && $currentActivitySettings->email_to_company != 1)
                            $parseClientSettingsForViewArr[$activity->id]['to_email'] = '';

                    }

                    //We are going to show all email in one textarea by comma separated so thats why
                    if ($currentActivitySettingCollection->to_email && $parseClientSettingsForViewArr[$activity->id]['to_email'] != '') {
                        $parseClientSettingsForViewArr[$activity->id]['to_email'] .= ',' . $currentActivitySettingCollection->to_email;
                    } elseif ($currentActivitySettingCollection->to_email && $parseClientSettingsForViewArr[$activity->id]['to_email'] == '') {
                        $parseClientSettingsForViewArr[$activity->id]['to_email'] = $currentActivitySettingCollection->to_email;
                    }

                    $parseClientSettingsForViewArr[$activity->id]['cc_email'] = $currentActivitySettingCollection->cc_email;
                    $parseClientSettingsForViewArr[$activity->id]['bcc_email'] = $currentActivitySettingCollection->bcc_email;
                }
            }

        }


        $preference_type_array = array();

        foreach ($parseClientSettingsForViewArr as $key => $item) {
            $preference_type_array[$item['type']][] = $item;
        }

        ksort($preference_type_array, SORT_NUMERIC);

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'data' => [
                'account_notifications' => count($preference_type_array) > 0 ? $preference_type_array[config('db_const.user_notify_Settings.notification_type.account_notification.value')] : '',
                'guest_notification' => (count($preference_type_array) > 1 ? $preference_type_array[config('db_const.user_notify_Settings.notification_type.guest_notification.value')] : ''),
                'payment_activity_notification' => (count($preference_type_array) > 2 ? $preference_type_array[config('db_const.user_notify_Settings.notification_type.payment_activity_notification.value')] : ''),
            ]
        ]);
    }


    public function preferencesTemplateVar()
    {
        $this->isPermissioned('preferences');
        return view('v2.client.settings.preferencesTemplateVar');
    }


    /**
     * @param Request $request
     * @return false|string
     */

    public function preferenceOnOff(Request $request)
    {

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $pre = UserPreference::where('user_account_id', $user_account_id)->where('preferences_form_id', $request->preferences_form_id)->first();

        $default = PreferencesForm::find($request->preferences_form_id);

        $success = false;

        if (is_null($pre)) {
            $userPreference = new UserPreference();
            $userPreference->user_id = $user_id;
            $userPreference->name = $default->name;
            $userPreference->user_account_id = $user_account_id;
            $userPreference->preferences_form_id = $default->id;
            $userPreference->form_data = $default->form_data;
            $userPreference->status = $request->status;
            if ($userPreference->save())
                $success = true;

        } else {
            $pre->user_id = $user_id;
            $pre->status = $request->status;
            if ($pre->save())
                $success = true;
        }

        return response()->json([
            'status' => $success ? 'success' : 'failed',
            'status_code' => $success ? 200 : 422,
            'message' => $success ? 'Preference status changed successfully.' : 'Oops! Status not changed',
            'old_value' => ($request->status) == 1 ? 0 : 1 //use this to uncheck the button again on frontend
        ]);
    }

    /**
     * We dont need this function for new designs as we don't have email company, sms, preference email turn on/off
     * @param Request $request
     * @return false|string
     */

    // public function boxsettings(Request $request){

    //     $this->isPermissioned('editSetting');
    //     $this->isSuperClient(auth()->user()->user_account);
    //     $user_account_id = auth()->user()->user_account_id;

    //     $requestJsonSettingsKey = $request->get('name');
    //     $requestJsonSettingsKeyValue = $request->get('vl');
    //     $activityId = $request->get('activityId');

    //     /**Default Value to notify Client from Config */
    //     $defaultNotifyConfigArr = json_decode(config('db_const.user_preferences_notify_default_settings.notify_settings'),true);


    //     $clientPreviousPreferencesSettings = UserPreferencesNotificationSettings::where([['activity_id', '=', $activityId], ['user_account_id', '=', $user_account_id],])->first();

    //     if ($clientPreviousPreferencesSettings != null) {

    //         $clientNotifySettingsJson = json_decode($clientPreviousPreferencesSettings->notify_settings,true);

    //         if (($requestJsonSettingsKey == 'email_to_company') && ($requestJsonSettingsKeyValue == 0)) {

    //             if ((($clientPreviousPreferencesSettings->to_email == null) || ($clientPreviousPreferencesSettings->to_email == ''))
    //             && (($clientPreviousPreferencesSettings->cc_email != null)  ||  ($clientPreviousPreferencesSettings->bcc_email != null))
    //             && ($clientNotifySettingsJson['email_to_preferences'] == 1)) {

    //                 return json_encode(['status' => false, 'msg' => 'Kindly Provide To Email in Preferences email first to Deactivate Emails to Company', 'value' => 1]);
    //             }

    //         } elseif (($requestJsonSettingsKey == 'email_to_preferences') && ($requestJsonSettingsKeyValue == 1)) {

    //             if ((($clientPreviousPreferencesSettings->to_email == null) || ($clientPreviousPreferencesSettings->to_email == ''))
    //             && (($clientPreviousPreferencesSettings->cc_email != null)  ||  ($clientPreviousPreferencesSettings->bcc_email != null))
    //             && ($clientNotifySettingsJson['email_to_company'] == 0)) {

    //                 return json_encode(['status' => false, 'msg' => 'Kindly Provide To Email in Preferences email first to Deactivate Emails to Company', 'value' => 0]);
    //             }
    //         }

    //         $clientNotifySettingsJson[$requestJsonSettingsKey] = $requestJsonSettingsKeyValue;
    //         $clientNotifySettingsJson = json_encode($clientNotifySettingsJson);
    //         $clientPreviousPreferencesSettings->notify_settings =   $clientNotifySettingsJson;
    //         $updated = ($clientPreviousPreferencesSettings->save() ? true : false);

    //     } else {

    //         $defaultNotifyConfigArr[$requestJsonSettingsKey] = $requestJsonSettingsKeyValue;
    //         $updated =  UserPreferencesNotificationSettings::create([
    //             'user_account_id' => $user_account_id,
    //             'activity_id' => $activityId,
    //             'notify_settings' => json_encode($defaultNotifyConfigArr),
    //             'to_email' =>'',
    //             'cc_email' => '',
    //             'bcc_email' => ''
    //         ]);

    //     }

    //     return ($updated ?
    //         json_encode(['status' => true, 'msg' => 'Settings Updated Successfully!' , 'value' => $requestJsonSettingsKeyValue ]) :
    //         json_encode(['status' => false, 'msg' => 'Failed to Save Settings!' , 'value' => ($requestJsonSettingsKeyValue == 1 ? 0 : 1)])
    //     );
    // }


    public function mailsettings(Request $request)
    {

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_account_id = auth()->user()->user_account_id;

        //find company email
        $company_email = UserAccount::find($user_account_id)->users->first()->email;

        $company_email_default_on = 0;  //initialize it as it is disable

        $activity_id = $request->get('activityId');

        $tovl = str_replace(' ', '', $request->get('tovl'));
        //$ccvl = str_replace(' ', '', $request->get('ccvl') );
        //$bccvl = str_replace(' ', '', $request->get('bccvl') );
        $t = '';

        if (isset($tovl) && $tovl != "") {
            $arr = array();
            $tomail = explode(',', $tovl);

            foreach ($tomail as $key => $item) {
                if ($item != '') //sometimes tag plugin jquery send empty data so checking if not empty
                {
                    $tovalidate = filter_var($item, FILTER_VALIDATE_EMAIL);
                    if ($tovalidate == false) {
                        return response()->json(['status' => false, 'msg' => 'Please correct the email address:-  ' . $item], 422);

                    } else if ($tovalidate == $company_email) {
                        $company_email_default_on = 1; //if company email is included in to_email box then it should be enabled
                    } else {
                        $arr[] = $tovalidate;
                    }
                }
            }
            $t = implode(',', $arr);
        }

        /* We don not have settings for BCC and CC email from now on so this code is commented**/
        // $c = '';
        // if(isset($ccvl) && $ccvl != ""){
        //     $arr = array();
        //     $ccmail = explode(',', $ccvl);

        //     foreach($ccmail as $key => $item){
        //         $ccvalidate =  filter_var($item, FILTER_VALIDATE_EMAIL);
        //         if($ccvalidate == false){
        //             return json_encode(['status' => false, 'msg' => 'Please correct the email address in (Cc)']);

        //         }else {
        //             $arr[] = $ccvalidate;
        //         }
        //     }
        //     $c = implode(',',$arr );            
        // }
        // $b = '';
        // if(isset($bccvl) && $bccvl != "" ){
        //     $arr = array();
        //     $bccmail = explode(',', $bccvl);
        //     foreach($bccmail as $key => $item){
        //         $bccvalidate =  filter_var($item, FILTER_VALIDATE_EMAIL);
        //         if($bccvalidate == false){

        //             return json_encode(['status' => false, 'msg' => 'Please correct the email address in (Bcc)' ]);

        //         }else {
        //             $arr[] = $bccvalidate;
        //         }
        //     }
        //    $b = implode(',',$arr );
        // }

        $clientPreviousPreferencesSettings = UserPreferencesNotificationSettings::where([['activity_id', '=', $activity_id], ['user_account_id', '=', $user_account_id],])->first();

        if ($clientPreviousPreferencesSettings == null) {

            /**Default Value to notify Client from Config */
            $defaultNotifyConfigArr = json_decode(config('db_const.user_preferences_notify_default_settings.notify_settings'), true);
            $defaultNotifyConfigArr['email_to_company'] = $company_email_default_on;

            $updated = UserPreferencesNotificationSettings::create([
                'user_account_id' => $user_account_id,
                'activity_id' => $activity_id,
                'notify_settings' => json_encode($defaultNotifyConfigArr),
                'to_email' => $t,
                'cc_email' => '',
                'bcc_email' => '',
            ]);

        } else {

            $clientNotifySettingsJson = json_decode($clientPreviousPreferencesSettings->notify_settings, true);
            $clientNotifySettingsJson['email_to_company'] = $company_email_default_on;

            $clientPreviousPreferencesSettings->notify_settings = json_encode($clientNotifySettingsJson);
            $clientPreviousPreferencesSettings->to_email = $t;
            $clientPreviousPreferencesSettings->cc_email = '';
            $clientPreviousPreferencesSettings->bcc_email = '';
            $updated = ($clientPreviousPreferencesSettings->save() ? true : false);
        }

        return ($updated ?
            json_encode(['status' => true, 'msg' => 'Settings Updated Successfully!']) :
            json_encode(['status' => false, 'msg' => 'Failed to Save Settings!'])
        );
    }

    public function reattemptsettings(Request $request)
    {

        $this->isPermissioned('preferences');

        $id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $attempt = $request->get('attempt');
        $hours = $request->get('hours');
        $stop_hours = $request->get('stop_hours');


        $checkids = ReattemptPolicy::where([
            ['user_account_id', '=', $user_account_id],
            ['user_id', '=', $id]
        ])->count();

        if ($checkids == 1) {

            $SettingsUpdate = ReattemptPolicy::where([
                ['user_account_id', '=', $user_account_id],
            ])->update([
                'attempts' => $attempt,
                'hours' => $hours,
                'stop_after' => $stop_hours,

            ]);

        } else {

            $reattemptSettings = ReattemptPolicy::create([
                'user_id' => $id,
                'user_account_id' => $user_account_id,
                'attempts' => $attempt,
                'hours' => $hours,
                'stop_after' => $stop_hours,
            ]);
        }
        $res = array('done' => 1, 'error' => 0);
        return json_encode($res);
    }

    public function savePreferenceSettings(Request $request)
    {

        $id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;
    }

    /**
     * PMS Wise
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPreferences()
    {

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_id = auth()->user()->id;
        $user_account = auth()->user()->user_account;

        $customPreferences = UserPreference::where('user_account_id', $user_account->id)
            ->select('id', 'name', 'preferences_form_id as form_id', 'form_data', 'status')
            ->get();

        $default = PreferencesForm::whereNotIn('form_id', $customPreferences->pluck('form_id')
            ->all())->select('id', 'name', 'form_id', 'form_data', 'status')->get();

        // Merged collection with keyBy
        $default = $default->keyBy('form_id')->sortBy('form_id');

        $customPreferences = $customPreferences->keyBy('form_id')->sortBy('form_id');

        $final = $customPreferences->toBase()->merge($default)->keyBy('form_id')->sortBy('form_id');

        $final = $final->map(function ($instance) {
            $instance['form_data'] = json_decode($instance['form_data']);
            $instance['icon_class'] = config('db_const.user_preferences.icons.' . $instance['form_id']);
            return $instance;
        });

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'data' => $final,
            'account_status' => $user_account->status,
            'integration_status' => !empty($user_account->integration_completed_on)
        ]);

    }

    public function revertToDefaultSetting(Request $request)
    {

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $up = UserPreference::where('user_account_id', $user_account_id)
            ->where('preferences_form_id', $request->id)->get();

        $dp = PreferencesForm::findOrFail($request->id);
        $dp->form_data = json_decode($dp->form_data);

        if ($up->count() > 0) {
            UserPreference::where('user_account_id', $user_account_id)
                ->where('preferences_form_id', $request->id)
                ->delete();
            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Preference Setting Reverted To Default.',
                'data' => $dp
            ]);
        }

        return response()->json([
            'status' => 'Aleardy On Default Setting',
            'status_code' => 422,
            'message' => 'Preference Setting Aleardy On Default Mode.',
            'data' => $dp
        ]);
    }

    /**
     *  PMS Wise
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveCustomPreferences(Request $request)
    {

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $pre = UserPreference::where('user_account_id', $user_account_id)->where('preferences_form_id', $request->form_id)->first();

        if (is_null($pre)) {
            $userPreference = new UserPreference();
            $userPreference->user_id = $user_id;
            $userPreference->name = $request->name;
            $userPreference->user_account_id = $user_account_id;
            $userPreference->preferences_form_id = $request->form_id;
            $userPreference->form_data = json_encode($request->form_data);
            $userPreference->save();

        } else {
            $pre->form_data = json_encode($request->form_data);
            $pre->user_id = $user_id;
            $pre->save();
        }

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Custom Preference Setting Saved Successfully.'
        ]);
    }

}
