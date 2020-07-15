<?php

namespace App\Repositories\BookingSources;


use App\BookingSourceCapability;
use App\CaCapability;
use App\CancellationSetting;
use App\CreditCardValidationSetting;
use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use App\FetchBookingSetting;
use App\PaymentScheduleSettings;
use App\Repositories\Settings\CancellationAmountType;
use App\Repositories\Settings\CreditCardValidation;
use App\Repositories\Settings\PaymentSchedule;
use App\Repositories\Settings\SecurityDamageDeposit;
use App\SecurityDamageDepositSetting;
use App\BookingSourceForm;
use App\UserAccount;
use App\UserBookingSource;
use App\PropertyInfo;
use App\UserSettingsBridge;
use Exception;
use Illuminate\Support\Facades\Log;

class BookingSources
{
  private static $supportedBookingSources = null;
  private static $customSettingBookingSources = null;

  const NO_PAYMENT_SUPPORTED_TYPE = 6;

  public function all_booking_sources()
  {
    $booking_sources = BookingSourceForm::where('type', '!=', self::NO_PAYMENT_SUPPORTED_TYPE)->get();
    return $booking_sources;
  }

    /**
     * these bookings sources will be used to set Guest Experience settings
     * @return mixed
     */
  public function custom_settings_booking_sources()
  {
    return BookingSourceForm::whereIn('id', $this->getAllGuestExperienceSupportedBookingSourceIds())
        ->where('use_custom_settings' , 1)->orderBy('priority', 'asc')->get();
  }

    /**
     * @param PropertyInfo $property_info
     * @param $booking_source_form_id
     * @return bool
     */
  public function isBookingSourceActive(PropertyInfo $property_info, $booking_source_form_id)
  {
      return $this->getPropertyAllBookingSources($property_info)
              ->where('booking_source_form_id', $booking_source_form_id)
              ->where('status', 1)
              ->count() > 0;
  }

  /**
   * @param PropertyInfo $property_info
   * @return mixed
   */
  public function getPropertyAllBookingSources(PropertyInfo $property_info)
  {
      try {
          $propertyId = $property_info->use_bs_settings == 1 ? $property_info->id : 0;
          return UserBookingSource::where('user_account_id', $property_info->user_account_id)
              ->where('property_info_id', $propertyId)->get();

      } catch (Exception $e) {
          Log::error($e->getMessage(), [
              'Name' => 'Ammar',
              'Property' => json_decode(json_encode($property_info), true),
              'Stack' => $e->getTraceAsString()
          ]);
      }
      return [];
  }

    /**
     * Validate Booking source Cancellation Policies is valid
     * to save Cancellation Settings
     *
     * @param array $cancellationSetting
     * @param bool $bookingSourceStatus
     * @return bool
     */
    public static function ValidateBookingSourceCancellationPolicies(array $cancellationSetting, $bookingSourceStatus = true)
    {
        $isPolicyValid = true;
        $cancellationStatusActive = filter_var($cancellationSetting['status'], FILTER_VALIDATE_BOOLEAN);
        if ($bookingSourceStatus && $cancellationStatusActive) {
            $ruleOneActive = filter_var($cancellationSetting['beforeCheckInStatus'], FILTER_VALIDATE_BOOLEAN);
            $ruleTwoActive = filter_var($cancellationSetting['afterBookingStatus'],  FILTER_VALIDATE_BOOLEAN);
            if (!$ruleOneActive  && !$ruleTwoActive) {
                $isPolicyValid = false;
                foreach ($cancellationSetting['rules'] as $key => $rule) {
                    if ((($rule['canFee'] === 'first_night') || ($rule['canFee'] > 0))  && ($rule['is_cancelled'] >= 0)
                        && (!is_null($rule['canFee'] ))  && !is_null($rule['is_cancelled'])) {
                        $isPolicyValid = true;
                        break;
                    }
                }
            }
        }
        return $isPolicyValid;
    }

    public static function isBookingSourceSupported($bookingSource) {

        if(self::$supportedBookingSources == null) {
            self::$supportedBookingSources = BookingSourceForm::where('type', '!=', self::NO_PAYMENT_SUPPORTED_TYPE)->get()->pluck('channel_code')->toArray();
        }

        return in_array($bookingSource, self::$supportedBookingSources);

    }

    /**
     * @param $channel_code
     * @param $pms_form_id
     * @return int
     */
    public static function getBookingSourceFormIdForGuestExperience($channel_code, $pms_form_id)
    {
        $booking_source_form = BookingSourceForm::where('channel_code', $channel_code)
            ->where('pms_form_id', $pms_form_id)->where('use_custom_settings', 1)->first;
        return !is_null($booking_source_form) ? $booking_source_form->id : 0;
    }

    /**
     * @param UserBookingSource $userBookingSource
     * @param array $settings
     * @param string $modelName
     * @param bool $bookingSourceCapable
     * @throws Exception
     */
    static function saveOrUpdateBookingSourceSetting(UserBookingSource $userBookingSource, array $settings, string $modelName, bool $bookingSourceCapable = true)
    {

        $bridgeDetails = self::getSettingConversionClassObjectFormSettingModelName($settings, $modelName);

        $setting = $bookingSourceCapable
            ? $bridgeDetails['settingClassInstance']->toJSON($settings) : $bridgeDetails['defaultSetting'];

        if ($userBookingSource->{$bridgeDetails['bridgeRelation']}->count()) {
            $userBookingSource->{$bridgeDetails['bridgeRelation']}[0]->settings = $setting;
            $userBookingSource->{$bridgeDetails['bridgeRelation']}[0]->save();
        } else {
            $settingsModel = resolve($modelName);
            $newSettingInstance = $settingsModel::create(['settings' => $setting]);
            UserSettingsBridge::create([
            'user_account_id'        =>  $userBookingSource->user_account_id,
            'booking_source_form_id' =>  $userBookingSource->booking_source_form_id,
            'property_info_id'       =>  $userBookingSource->property_info_id,
            'user_booking_source_id' =>  $userBookingSource->id,
            'model_name'             =>  $modelName,
            'model_id'               =>  $newSettingInstance->id]);
        }

        if (($modelName == SecurityDamageDepositSetting::class)  && ($settings['status']))
            userSecurityDepositSettingsUpdateForBillingReminder(auth()->user()->user_account, $settings['status']);
    }

    /**
     * @param array $settings
     * @param $modelName
     * @return array
     * @throws \Exception
     */
    static function getSettingConversionClassObjectFormSettingModelName(array &$settings, $modelName)
    {

        switch ($modelName){
            case CreditCardValidationSetting::class:
               return  [
                   'settingClassInstance' => resolve(CreditCardValidation::class),
                   'bridgeRelation' => 'credit_card_validation_setting',
                    'defaultSetting' => UserBookingSource::DEFAULT_CREDIT_CARD_SETTING
               ];
               break;
            case PaymentScheduleSettings::class:
                return  [
                    'settingClassInstance' => resolve(PaymentSchedule::class),
                    'bridgeRelation' => 'payment_schedule_setting',
                    'defaultSetting' => UserBookingSource::DEFAULT_PAYMENT_SETTING
                ];
                break;
            case SecurityDamageDepositSetting::class:
                return  [
                    'settingClassInstance' => resolve(SecurityDamageDeposit::class),
                    'bridgeRelation' => 'security_damage_deposit_setting',
                    'defaultSetting' => UserBookingSource::DEFAULT_SECURITY_DEPOSIT_SETTING
                ];
                break;
            case CancellationSetting::class:
                foreach ($settings['rules'] as $key => $rule) {
                    if ((!isset($rule['canFee']) || !isset($rule['is_cancelled'])) || (is_null($rule['canFee'])
                            || is_null($rule['is_cancelled'])))
                        unset($settings['rules'][$key]);
                }
                $settings['rules'] = array_values($settings['rules']);
                if (count($settings['rules']) == 0)
                    $settings['rules'] =  array(0 => array("canFee" => null , "is_cancelled" => null,
                        "is_cancelled_value" => null));

                return  [
                    'settingClassInstance' => resolve(CancellationAmountType::class),
                    'bridgeRelation' => 'cancellation_setting',
                    'defaultSetting' => UserBookingSource::DEFAULT_CANCELLATION_SETTING];
                break;
            default:
                throw new \Exception("No Case Defined for this Model $modelName");
                break;
        }
    }

    /**
     * @param array $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveClientBookingSourceSettings(array $request){

        try {
            /* Validate Cancellation Policies */
            foreach ($request['bookingSourcesSettings'] as $bookingSourcesSetting) {
                if(!BookingSources::ValidateBookingSourceCancellationPolicies($bookingSourcesSetting['return_rules']))
                    return response()->json(['status'=> false, 'status_code'=> 422, 'data' => [],
                        'message'=> 'Kindly specify cancellation policy for '. $bookingSourcesSetting['name']]);
            }

            $userBookingSources = UserBookingSource::with('User_account', 'credit_card_validation_setting',
                'payment_schedule_setting', 'security_damage_deposit_setting', 'cancellation_setting')
                ->where([['user_account_id', auth()->user()->user_account_id],
                    ['property_info_id', $request['propertyInfoId']]])->get();

            foreach ($request['bookingSourcesSettings'] as $bookingSourcesSetting) {
                $notifyFlag = false;
                $userBookingSource = $userBookingSources->where('booking_source_form_id',
                    $bookingSourcesSetting['id'])->first();

                if (!$bookingSourcesSetting['status'] && empty($userBookingSource))
                    continue;

                if (!is_null($userBookingSource)) {
                    if ($userBookingSource->status != $bookingSourcesSetting['status']) {
                        $userBookingSource->status =  $bookingSourcesSetting['status'];
                        $userBookingSource->save();
                        $notifyFlag = true;
                    }
                } else {
                    $userBookingSource = UserBookingSource::create([
                    'user_id'                => auth()->user()->id,
                    'user_account_id'        => auth()->user()->user_account_id,
                    'property_info_id'       => $request['propertyInfoId'],
                    'booking_source_form_id' => $bookingSourcesSetting['id'],
                    'status'                 => $bookingSourcesSetting['status']]);
                    $notifyFlag = true;
                }
                $this->saveOrUpdateBookingSourceSettings($userBookingSource, $bookingSourcesSetting, $notifyFlag);
            }
            return response()->json(['status' => true, 'status_code' => 200, 'message'=> 'Booking Source Settings Updated.', 'data' => [],]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File'=> $e->getFile(),'Line'=> $e->getLine(), 'stackTrace' => $e->getTraceAsString()]);
            return response()->json(['status'=> false, 'data' => [], 'status_code'=> 500, 'message'=> 'Failed to save Booking Source Settings']);
        }
    }

    /**
     * @param UserBookingSource $userBookingSource
     * @param array $bSSettings
     * @param bool $notifyFlag
     * @throws Exception
     */
    private  function saveOrUpdateBookingSourceSettings(UserBookingSource $userBookingSource, array $bSSettings, bool $notifyFlag = false) {
        $bSCapabilities = self::getBookingSourceAllCapabilitiesById($userBookingSource->booking_source_form_id);
        if ($bSCapabilities[CaCapability::AUTO_PAYMENTS] || $bSCapabilities[CaCapability::SECURITY_DEPOSIT]) {

            /* CC Auth  Settings*/
            if (!empty($bSSettings['booking_deposit']))
                self::saveOrUpdateBookingSourceSetting($userBookingSource, $bSSettings['booking_deposit'],
                CreditCardValidationSetting::class, $bSCapabilities[CaCapability::AUTO_PAYMENTS]);

            /* Payment Schedule Settings | booking_payment Settings*/
            if (!empty($bSSettings['booking_payment']))
                self::saveOrUpdateBookingSourceSetting($userBookingSource, $bSSettings['booking_payment'],
                PaymentScheduleSettings::class, $bSCapabilities[CaCapability::AUTO_PAYMENTS]);

            /* Security Damage Deposit Settings  */
            if (!empty($bSSettings['security_deposit']))
                self::saveOrUpdateBookingSourceSetting($userBookingSource, $bSSettings['security_deposit'],
                SecurityDamageDepositSetting::class, $bSCapabilities[CaCapability::SECURITY_DEPOSIT]);

            /* Cancellation Policies | Return Rules */
            if (!empty($bSSettings['return_rules']))
                self::saveOrUpdateBookingSourceSetting($userBookingSource, $bSSettings['return_rules'],
                CancellationSetting::class, $bSCapabilities[CaCapability::AUTO_PAYMENTS]);
        }

        if ($notifyFlag && $userBookingSource->status == 1) {
            event(new EmailEvent(config('db_const.emails.heads.booking_source_activated.type'), $userBookingSource->id));
        } elseif($notifyFlag && $userBookingSource->status != 1) {
            event(new EmailEvent(config('db_const.emails.heads.booking_source_deactivated.type'), $userBookingSource->id));
        }
    }


    /**
     * Return all Capabilities status array by name as key, regarding to a specific BookingSourceFormId
     * @param $booking_source_form_id
     * @return array
     */
    public  static function getBookingSourceAllCapabilitiesById($booking_source_form_id)
    {
        $capabilities = self::getCapabilitiesInstanceByBookingSourceFormId($booking_source_form_id)
            ->select('name', 'id')->get();
        $capabilities_status = [];
        foreach ($capabilities as $capability) {
            if ($capability->bookingSourceCapabilities->count()) {
                $capability_available_flag = true;
            } else {
                Log::emergency("$capability->name Capability Not Defined for Booking Source Form ID 
                $booking_source_form_id");
                $capability_available_flag = false;
            }
            $capabilities_status[$capability->name] = $capability_available_flag
                ? filter_var($capability->bookingSourceCapabilities->first()->status, FILTER_VALIDATE_BOOLEAN)
                : false;
        }
        return $capabilities_status;
    }

    /**
     * Validate Booking Source Specific Capability Supported by CA.
     * @param $capability_name
     * @param $booking_source_form_id
     * @return bool
     */

    public static function isCapabilitySupportedByBookingSource($capability_name, $booking_source_form_id)
    {
        $capability = self::getCapabilitiesInstanceByBookingSourceFormId($booking_source_form_id)
            ->where('name', $capability_name)->first();

        if (!is_null($capability)) {
            $bs_capable = $capability->bookingSourceCapabilities->first();
            return  !empty($bs_capable) ? filter_var($bs_capable->status, FILTER_VALIDATE_BOOLEAN) : false;
        } else {
            Log::emergency("$capability_name Capability Not Defined in DB for Booking Source 
            Form ID $booking_source_form_id");
            return false;
        }
    }


    /**
     * Return instance of Capability records with bookingSourceCapabilities related to specific BookingSourceFormId
     * @param $bookingSourceFormId
     * @return CaCapability|\Illuminate\Database\Eloquent\Builder
     */
    public static function getCapabilitiesInstanceByBookingSourceFormId($bookingSourceFormId)
    {
        return CaCapability::with(['bookingSourceCapabilities' =>
            function ($query) use ($bookingSourceFormId)
            {$query->where('booking_source_form_id', $bookingSourceFormId )->select('status', 'ca_capability_id');}]);
    }


    /**
     * @return array
     */
    public  function getAllGuestExperienceSupportedBookingSourceIds()
    {
        return BookingSourceCapability::where('ca_capability_id',
            CaCapability::where('name', CaCapability::GUEST_EXPERIENCE)->first()->id)->where('status', 1)
            ->pluck('booking_source_form_id')->toArray();
    }



    /**
     * @param $pms_form_id
     * @param $channel_code
     * @return mixed
     * @throws Exception
     */
    public static function getBookingSourceFormIdByChannelCode($pms_form_id, $channel_code)
    {
        try {
            $others = config('db_const.booking_source_forms.channelCode.others');
            $booking_source_forms = BookingSourceForm::where('pms_form_id', $pms_form_id)->where(
                function ($query) use ($channel_code, $others) {
                    $query->where('channel_code', $channel_code)->orWhere('channel_code', $others);
                })->get();

            $booking_source_form = $booking_source_forms->where('channel_code', $channel_code)->first();

            if (!empty($booking_source_form))
                return $booking_source_form->id;
            else
                return $booking_source_forms->where('channel_code', $others)->first()->id;
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File'=> __FILE__, 'Function'=>__FUNCTION__, 'stackTrace' => $e->getTraceAsString()]);
        }

        throw new \Exception("Channel Code or PMS not Valid Channel_Code => $channel_code, PMS Form ID => $pms_form_id");
    }

    /**
     * @param UserAccount $user_account
     * @param $channel_code
     * @return mixed
     */
    /*public static  function getUserBookingSourceByChannelCode(UserAccount $user_account, $channel_code)
    {
        return $user_account->user_bookings_source->whereIn('booking_source_form_id')
          ->where('pms_id', $user_account->pms->pms_form_id)->where('channel_code', $channel_code)->first();
    }*/

    /**
     * @return array
     */
    public function getAllBookingSourcesCapabilities() {
        $booking_sources_capabilities = [];
        $capabilities = BookingSourceCapability::with(['cACapability' => function ($query)
        {$query->select('name', 'id', 'status');}])->get();
         foreach($capabilities as $capability) {
             $booking_sources_capabilities[$capability->booking_source_form_id][$capability->cACapability->name] =
                 $capability->cACapability->status
                     ? filter_var($capability->status, FILTER_VALIDATE_BOOLEAN) : false;
         }
         return $booking_sources_capabilities;
    }


    /**
     * Set Default Payments Bookings Active for New Users, function not used.
     * @param UserAccount $userAccount
     */
/*
    public static function userDefaultBookingSourceSettingsCheck(UserAccount $userAccount)
    {
        $old_user = $userAccount->user_bookings_source->where('property_info_id', 0)->count();

        if ($old_user)
            return;

        $bS_ids = BookingSourceForm::where('pms_form_id',$userAccount->pms->pms_form_id)->where('status', 1)->pluck('id')->toArray();
        $create_many = [];

        $default_fetch_booking = [
            'user_id' => auth()->user()->id,
            'user_account_id' => $userAccount->id,
            'property_info_id' => 0,
            'booking_source_form_id' => 0,
            'status' => 0,  //Payments default in-active
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        foreach ($bS_ids as $bS_id) {
            $default_fetch_booking['booking_source_form_id'] = $bS_id; // Create Fill able to Create_Many...
            array_push($create_many, $default_fetch_booking);
        }

        if (!empty($create_many))
            UserBookingSource::insert($create_many);
    }
*/




    /**
     * Booking Channel Settings Fetch Boooking Settings
     * @param UserAccount $user_account
     * @return array
     */

    public function getUserFetchBookingSettings(UserAccount $user_account)
    {
        $settings = [];
        $user_account_id = $user_account->id;

        $bookingSources = BookingSourceForm::with(['fetchBookingSettings' => function ($query) use ($user_account_id){
            $query->select('status', 'booking_source_form_id', 'user_account_id')
                ->where('user_account_id', $user_account_id);}])
            ->where([['status' , 1], ['pms_form_id', $user_account->pms->pms_form_id]])
        ->select('id', 'channel_code','name', 'logo', 'status')->get();

        $others = config('db_const.booking_source_forms.channelCode.others');
        $others_desc = 'Fetch bookings for all other booking sources at BA/Beds24 except above in the list';

        foreach ($bookingSources as $bookingSource) {
            $setting = $bookingSource->fetchBookingSettings->first();
            array_push($settings,
                [
                    'id' => $bookingSource->id,
                    'name' => $bookingSource->name,

                    'desc' => $bookingSource->channel_code != $others
                        ? 'Fetch bookings for '. $bookingSource->name
                        : $others_desc,

                    'logo' => strlen($bookingSource->logo) > 2
                        ? asset('storage/uploads/booking_souce_logo/').'/'.$bookingSource->logo
                        : $bookingSource->logo,

                    'fetch_booking' => empty($setting) ?:
                        filter_var($setting->status, FILTER_VALIDATE_BOOLEAN)
                ]
            );
        }


        return $settings;
    }

    /**
     * @param UserAccount $user_account
     * @param $booking_source_form_id
     * @return bool
     */
    public static function isActiveFetchBookingSetting(UserAccount $user_account, $booking_source_form_id)
    {
        $setting = $user_account->fetchBookingSettings
            ->where('booking_source_form_id', $booking_source_form_id)->first();
        return empty($setting) ?: filter_var($setting->status, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * @param UserBookingSource|null $userBookingSource
     * @return array
     */
    public static function MapSettingsByJsonStringFromBridgeTableWithModelRelations(UserBookingSource $userBookingSource = null)
    {
        if (!is_null($userBookingSource)) {
            return [
                'booking_payment'  => isset($userBookingSource->payment_schedule_setting[0])
                    ? new PaymentSchedule($userBookingSource->payment_schedule_setting[0]->settings)
                    : self::getDefaultPaymentSettings(),

                'booking_deposit'  => isset($userBookingSource->credit_card_validation_setting[0])
                    ? new CreditCardValidation($userBookingSource->credit_card_validation_setting[0]->settings)
                    : self::getDefaultCreditCardSettings(),

                'security_deposit' => isset($userBookingSource->security_damage_deposit_setting[0])
                    ? new SecurityDamageDeposit($userBookingSource->security_damage_deposit_setting[0]->settings)
                    : self::getDefaultSecurityDepositSettings(),

                'return_rules'     => isset($userBookingSource->cancellation_setting[0])
                    ? new CancellationAmountType($userBookingSource->cancellation_setting[0]->settings)
                    : self::getDefaultCancellationSettings(),
            ];
        } else {
            return self::getDefaultUserBookingSourceSettings();
        }
    }

    /**
     * @return array
     */
    public static function getDefaultUserBookingSourceSettings() {
        return [
            'booking_payment'  => self::getDefaultPaymentSettings(),
            'booking_deposit'  => self::getDefaultCreditCardSettings(),
            'security_deposit' => self::getDefaultSecurityDepositSettings(),
            'return_rules'     => self::getDefaultCancellationSettings(),
        ];
    }

    public static function getDefaultPaymentSettings()
    {
        return json_decode(UserBookingSource::DEFAULT_PAYMENT_SETTING);
    }
    public static function getDefaultSecurityDepositSettings()
    {
        return json_decode(UserBookingSource::DEFAULT_SECURITY_DEPOSIT_SETTING);
    }
    public static function getDefaultCreditCardSettings()
    {
        return json_decode(UserBookingSource::DEFAULT_CREDIT_CARD_SETTING);
    }
    public static function getDefaultCancellationSettings()
    {
        return json_decode(UserBookingSource::DEFAULT_CANCELLATION_SETTING);
    }
}