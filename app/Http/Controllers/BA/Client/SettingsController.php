<?php

namespace App\Http\Controllers\BA\Client;

use App\PreferencesForm;
use App\UserPreference;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function saveCustomPreferences(Request $request){

        $this->isPermissioned('preferences');
        $this->isSuperClient(auth()->user()->user_account);

        $user_id = auth()->user()->id;
        $user_account_id = auth()->user()->user_account_id;

        $pre = UserPreference::where('user_account_id', $user_account_id)->where('preferences_form_id', $request->form_id)->first();

        if(is_null($pre)) {
            $userPreference = new UserPreference();
            $userPreference->user_id = $user_id;
            $userPreference->name = $request->name;
            $userPreference->user_account_id = $user_account_id;
            $userPreference->preferences_form_id = $request->form_id;
            $userPreference->form_data = json_encode($request->form_data);
            $userPreference->save();

        }else{
            $pre->form_data = json_encode($request->form_data);
            $pre->user_id = $user_id;
            $pre->save();
        }

        return response()->json([
            'status'=>'success',
            'status_code'=>200,
            'message'=>'Custom Preference Setting Saved Successfully.'
        ]);
    }

    /**
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

}
