<?php

namespace App\Http\Controllers\v2\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GeneralPreferencesForm;
use App\UserGeneralPreference;
use App\UserAccount;
use App\BookingSourceForm;
use Validator;
use App\Repositories\Settings\ClientNotifySettings;
use App\Repositories\BookingSources\BookingSources;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notification;
use App\Notifications\DatabaseNotification;

class GeneralSettingsController extends Controller
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
        $this->isPermissioned('guestExperience');
        return view('v2.client.settings.generalSettings');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData()
    {
        $this->isPermissioned('guestExperience');
        $user_account_id = auth()->user()->user_account_id;
        $user_account = UserAccount::find($user_account_id);
        $this->isSuperClient($user_account);

        //get all available preference by sequence with default status
        $allGeneralPreferences = GeneralPreferencesForm::
                                        select('id as form_id', 'name', 'description', 'status')
                                        ->orderBy('priority', 'asc') //sorting for sequence on frontend
                                        ->get();

        //get user custom settings
        $preferencesSettings1 = UserGeneralPreference::whereIn('form_id', $allGeneralPreferences->pluck('form_id')
            ->all())->where('user_account_id', $user_account_id)->get()->groupBy('form_id');

        //get all the available channel
        $user_pms = $user_account->pms;
        $all_booking_sources = app()->make('BookingSources');

        if (!is_null($user_pms))
            $booking_sources = $all_booking_sources->custom_settings_booking_sources()->where('status',1)->where('pms_form_id',$user_pms->pms_form_id);
        else
            $booking_sources = $all_booking_sources->custom_settings_booking_sources()->where('status',1);

        //Merged collection with keyBy
        foreach ($preferencesSettings1 as $key => $value) {
            $preferencesSettings1[$key] = $value->keyBy('booking_source_form_id')->sortBy('booking_source_form_id');
        }

        $data =  ['available_general_preferences' => $allGeneralPreferences, 'user_preferences_settings' => $preferencesSettings1, 'booking_sources' => $booking_sources];

        return $this->apiSuccessResponse(200, $data);
    }

    /**
     * @param Request $request
     * @return false|string
     */

    public function enableDisableSetting(Request $request)
    {
        $this->isPermissioned('guestExperience');
        $this->isSuperClient(auth()->user()->user_account);
        $user_account_id = auth()->user()->user_account_id;

        $name = $request->get('name');
        $status = $request->get('status');
        $setting_form_id = $request->get('form_id');
        $selected_booking_source_form = $request->get('selected_booking_source_form');
        $selected_channel_code = $request->get('selected_channel_code');
        
        $all_booking_sources = app()->make('BookingSources');
        $selected_booking_source_form = $all_booking_sources->custom_settings_booking_sources()->where('id', $selected_booking_source_form)->where('channel_code', $selected_channel_code)->first();

        if ($selected_booking_source_form)
        {
            $booking_source_form = $selected_booking_source_form->id;
        }
        else
        {
            //this will run for Others type of settings
            $booking_source_form = 0; 
        }        

        $default_general_preferences_setting = GeneralPreferencesForm::where('id', $setting_form_id)->first();
        
        /*
         * For most of the case foreach will run only for once
         * Only for Airbnb this will run 2 times as we have Airbnb Ical and Airbnb XML
        */
        $previous_user_custom_setting = UserGeneralPreference::where('user_account_id', $user_account_id)
                                        ->where('booking_source_form_id', $booking_source_form)
                                        ->where('form_id', $setting_form_id)
                                        ->first();

        if ($previous_user_custom_setting != null) {

            $previous_user_custom_setting->form_data = json_encode(['status' => $status]);
            $updated = ($previous_user_custom_setting->save() ? true : false);

        } else {

            $updated =  UserGeneralPreference::create([
                'name' => $default_general_preferences_setting->name,
                'description' => $default_general_preferences_setting->description,
                'user_account_id' => $user_account_id,
                'form_id' => $setting_form_id,
                'booking_source_form_id' => $booking_source_form,
                'form_data' => json_encode(['status' => $status])
            ]);

        }

        $email_to_guest_check = true;
        if($setting_form_id == GeneralPreferencesForm::EMAIL_TO_GUEST_OPTION && $status!=GeneralPreferencesForm::ENABLE_STATUS)
        {
            //check if email to guest option is disabled
            //Disable all other options as well for that booking channel
            $email_to_guest_check = UserGeneralPreference::where('user_account_id', $user_account_id)
                ->where('booking_source_form_id', $booking_source_form)
                ->update(['form_data'=>json_encode(['status' => $status])]);
        }
        elseif ($setting_form_id != GeneralPreferencesForm::EMAIL_TO_GUEST_OPTION && $status==GeneralPreferencesForm::ENABLE_STATUS)
        {
            //check if email to guest option is ON for same channel if not then enable it
            $email_to_guest_status = UserGeneralPreference::where('user_account_id', $user_account_id)
                ->where('booking_source_form_id', $booking_source_form)
                ->where('form_id', GeneralPreferencesForm::EMAIL_TO_GUEST_OPTION)
                ->first();
            if(!empty($email_to_guest_status)){
                $email_to_guest_form_data = json_decode($email_to_guest_status->form_data);
                if($email_to_guest_form_data->status != GeneralPreferencesForm::ENABLE_STATUS) {
                    $email_to_guest_status->form_data = json_encode(['status' => GeneralPreferencesForm::ENABLE_STATUS]);
                    $email_to_guest_check = ($email_to_guest_status->save() ? true : false);
                }
            }
        }

        return (($updated && $email_to_guest_check) ?
            json_encode(['status' => true, 'msg' => 'Settings Updated Successfully!' , 'value' => $status ]) :
            json_encode(['status' => false, 'msg' => 'Failed to Save Settings!' , 'value' => ($status == 1 ? 0 : 1)])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
