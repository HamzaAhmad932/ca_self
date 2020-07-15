<?php 
/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 18-Feb-19
 * Time: 4:47 PM
 */

namespace App\Repositories\Settings;

use App\Activity;
use App\UserAccount;
use App\UserPreferencesNotificationSettings;
use App\UserNotificationSetting;
use App\User;
use App\Mail\GenericEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class ClientNotifySettings {

    private $userAccountId;
    private $activities;
    private $userSettings;
	private $to_company_emailArr = [];


	function __construct(int $userAccountId) {

	    $this->userAccountId = $userAccountId;
	    $this->activities = Activity::get();
	    $this->userSettings = UserPreferencesNotificationSettings::where('user_account_id' , $this->userAccountId)->get();

        $userAccount = UserAccount::find($this->userAccountId);
        $this->to_company_emailArr[] = $userAccount->users->first()->email;

        if ($userAccount->email != null )
            $this->to_company_emailArr[] = $userAccount->email;

    }

	private function isDefaultEmailEnabled(Activity $activity) {
        return $activity->email === 1;
    }

    private function isDefaultSMSEnabled(Activity $activity) {
        return $activity->sms === 1;
    }


    /**
     * @param Activity $activity
     * @return array|bool
     */

    private function defaultEmailSettings(Activity $activity){
        if ($this->isDefaultEmailEnabled($activity)) {
            return array('to_email' => implode(',', $this->to_company_emailArr), 'cc_email' => false, 'bcc_email' => false);
        } else {
            return false;
        }
    }

    public function isActiveMail($configVar){

        $activity = $this->havingActivity($configVar);
        if ($activity === false)
            return false;
        $setting = $this->userSettings->where('activity_id', $activity->id)->first();

        if ($setting === null) {

            return $this->defaultEmailSettings($activity);

        } else {

            $clientNotifySettingsJson = json_decode($setting->notify_settings,true);

            /* Guest email setting commented here because the are now part of General settings */
            // if ($configVar === config('db_const.user_notify_Settings.guestCorrespondence'))
            //     return ( isset($clientNotifySettingsJson['email_to_guest']) ? ( $clientNotifySettingsJson['email_to_guest'] === 1 ? true : false ) : $this->isDefaultEmailEnabled($activity));


            if ((isset($clientNotifySettingsJson['email_to_preferences'])) && ($clientNotifySettingsJson['email_to_preferences'] == 1)) {

                if ((isset($clientNotifySettingsJson['email_to_company'])) && ($clientNotifySettingsJson['email_to_company'] == 1)) {

                    $to_email = implode(',', $this->to_company_emailArr);
                    if ($setting->to_email != null) {
                        $to_email .=  ',' . $setting->to_email;
                    }

                } else {
                    $to_email = $setting->to_email;
                }

                return array('to_email' => $to_email, 'cc_email' => $setting->cc_email, 'bcc_email' => $setting->bcc_email);

            } else if ((isset($clientNotifySettingsJson['email_to_company'])) && ($clientNotifySettingsJson['email_to_company'] == 1)) {

                return $this->defaultEmailSettings($activity);
            }
        }
        return false;
    }




    /**
     * @param $configVar
     * @return bool
     */

    public function isActiveSms($configVar){

        $activity = $this->havingActivity($configVar);

        if($activity === false)
            return false;

        $setting = $this->userSettings->where('activity_id', $activity->id)->first();

        if($setting === null)
            return $this->isDefaultSMSEnabled($activity);

        $clientNotifySettingsJson = json_decode($activity->notify_settings,true);

        if (isset($clientNotifySettingsJson['sms_to_company'])){

            return $clientNotifySettingsJson['sms_to_company'] === 1;

        } else{

            return $this->isDefaultSMSEnabled($activity);
        }
    }

    /**
     * @param string|null $activityName
     * @return Activity | false
     */

    private function havingActivity($activityName) {

        if($activityName === null)
            return false;

        if (count($this->activities) == 0)
            return false;

        $activityObj = $this->activities->where('name', '==', $activityName)->first();

        if ($activityObj == null)
            return false;

        return $activityObj;
     }


    /**
     * @param array $notify
     * @return array
     */
    public function setMailParameters(array $notify) {

        $respond = array('to' =>  '' , 'cc' => false ,'bcc' => false);

        try {

            if (($notify['to_email'] == "")  || is_null($notify['to_email'])) {

                $userAccount = UserAccount::find($this->userAccountId);

                if (($userAccount->email == null) || (empty($userAccount->email)))
                     $notify['to_email'] = $userAccount->users->first()->email;
                else
                    $notify['to_email'] = $userAccount->email;

            }

            $respond['to'] = explode(',', $notify['to_email']);
            $respond['cc'] = ( ($notify['cc_email'] == "")  || is_null($notify['cc_email']) ? false : explode(',', $notify['cc_email']) );
            $respond['bcc'] = ( ($notify['bcc_email'] == "")  || is_null($notify['bcc_email']) ? false : explode(',', $notify['bcc_email']) );

        } catch (\Exception $e) {
            Log::error($e->getMessage() , array('File'=>'ClientNotifySettings', 'stack-trace'=> $e->getTraceAsString(), 'Exception-Line-No'=> 144));
        }
        return $respond;
    }

    public function sendMail($to, $cc, $bcc, $email) {

        try {

            if ($cc && $bcc) {
                Mail::to($to)->cc($cc)->bcc($bcc)->send(new GenericEmail($email));

            } else if ($cc) {
                Mail::to($to)->cc($cc)->send(new GenericEmail($email));

            } else if ($bcc) {
                Mail::to($to)->bcc($bcc)->send(new GenericEmail($email));

            } else {
                Mail::to($to)->send(new GenericEmail($email));
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage() , array('File'=>'ClientNotifySettings', 'stack-trace'=> $e->getTraceAsString(), 'Exception-Line-No'=> 167));
            Log::error($e->getTraceAsString() , array('File'=>'ClientNotifySettings', 'stack-trace'=> $e->getTraceAsString(), 'Exception-Line-No'=> 168));
        }
    }

}