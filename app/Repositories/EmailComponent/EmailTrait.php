<?php

namespace App\Repositories\EmailComponent;

use App\BookingInfo;
use App\Events\Emails\EmailEvent;
use App\Events\SendEmailEvent;
use App\Exceptions\EmailComponentException;
use App\GuestData;
use App\Jobs\GenericTwilioSmsJob;
use App\Repositories\BookingSources\BookingSources;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\Settings\ClientNotifySettings;
use App\Repositories\Upsells\UpsellRepository;
use App\SentEmail;
use App\Services\Emails\EmailContentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

trait EmailTrait
{
    use SystemEmailContents;

    public $email_type = null;
    public $to_whom = null;
    /**
     * How many times we need to retry job if fails
     */
    public $tries = 1;

    /**
     * Array of all the emails where we need to send the email
     */
    public $send_to_emails = [];

    /**
     * Array of all the emails where we need to send CC of the sending email
     */
    public $cc_to_emails = [];

    /**
     * Array of all the emails where we need to send BCC of the sending email
     */
    public $bcc_to_emails = [];

    /**
     * Subject of the email being sent
     */
    public $subject = '';

    /**
     * Final email content/body after all data logic
     */
    public $email_body = [];

    /**
     * Any exception or extra data required by email will be sent here as array
     */
    public $extra_data = array();

    public $email_type_name;
    public $model_id; //this will be send by event when triggered
    public $send_to_whom_name; // Client/Admin/Guest
    public $email_settings;
    public $sending_email_address_arr;

    /**
     * @var BookingInfo
     */
    public $booking_info = null;
    public $user = null;
    public $user_account = null;
    public $property_info = null;
    public $guest_communication = null;
    public $transaction_info = null;
    public $refund_info = null;
    public $authorization_info = null;
    public $credit_card_info = null;
    public $user_booking_source = null;
    public $user_payment_gateway = null;

    public function __construct()
    {
        //do something here if needed
    }

    /*
     * Check if this type of email enabled or not for sending
     * If not enabled then throw exception
    */
    private function isEmailTypeEnabledToSend($which_email)
    {
        try {
            $this->email_settings = new ClientNotifySettings($this->user_account->id);
            $notify = $this->email_settings->isActiveMail($which_email);

            if (!$notify) {
                return false;
            } else {
                if(isset($notify['to_email']) && !empty($notify['to_email'])) {
                    $this->sending_email_address_arr = $notify; //set global variable
                    return true;
                } else
                    return false;
            }
        } catch(EmailComponentException $e) {
            return false;
        }
    }

    /*
     * Check if email to guest option enabled
     * If not enabled then throw exception
    */
    private function isEmailToGuestOptionEnabled()
    {
        try
        {
            /*
             * Checking Client's Guest Email Sending Settings for current channel
             * If booking is not from supported channels then always mark it unprocessable
             */
            $generalPreferencesSettings = new ClientGeneralPreferencesSettings($this->user_account->id);
            $guestEmailStatus = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.emailToGuest'), $this->booking_info->bookingSourceForm);

            if(!$guestEmailStatus)
                return false;
            return true;
        }
        catch(EmailComponentException $e)
        {
            return false;
        }
    }

    //get all guest emails from booking and pre-checkin data
    public function getGuestEmails()
    {
        $guest_emails = [];
        $guest_data = GuestData::where('booking_id', $this->booking_info->id)->first();

        if (!is_null($guest_data) && ($guest_data->email != null))
            $guest_emails[] = $guest_data->email;

        if ($this->booking_info->guest_email != null)
            $guest_emails[] = $this->booking_info->guest_email;

        return $guest_emails;
    }

    public function setToEmail($emails = [])
    {
        $this->send_to_emails = $this->validateEmails($emails);
    }

    public function validateEmails($emails = [])
    {
//        if(!is_array($emails))
//            throw new EmailComponentException("Invalid Email List for validateEmails function");

        $valid_emails = [];

        if(!is_array($emails))
        {
            $emails = explode(",", $emails);
        }

        foreach($emails as $email) {
            $email = trim($email);
            $validator = Validator::make( ['email' => $email], ['email' => 'required|email'] );
            if (!$validator->fails()) {
                $valid_emails[] = $email;
            } else {
                Log::notice("REMOVING Email! Email address is not correct", [ 'emails'=> $emails, 'invalid_email'=>$email] );
            }
        }


        if(count($valid_emails) > 0) {
            return $valid_emails;
        } else {
            if ($this->email_type_name == 'new_booking') {
                //send email to client
                event(new EmailEvent(config('db_const.emails.heads.guest_email_missing.type'), $this->booking_info->id ));
                //send sms to guest
                if (!empty($this->booking_info->guest_phone)) {
                    GenericTwilioSmsJob::dispatch( $this->booking_info->guest_phone , 'Please update your email address for reservation '.$this->booking_info->pms_booking_id.'. '.\Shortener::shorten(\URL::to($this->getSignedGuestCheckinUrl())) );
                }
            }
            //stop execution of job
            throw new EmailComponentException("Valid Email Address not found to send email");
        }
    }

    public function setCcEmail($emails = [])
    {
        $this->cc_to_emails = $emails;
    }

    public function setBccEmail($emails = [])
    {
        $this->bcc_to_emails = $emails;
    }

    public function setEmailBody($email_content)
    {
        $this->email_body = $email_content;
    }

    /**
     * Send Email to Client | Guest | Admin by common function.
     * @throws \Exception
     */
    public function sendEmail()
    {
        // Set Default and Basic Required Data for email.
        $this->set_required_data();


        if ($this->validToSend()) {

            /** Check Guest Experience Settings */
            if (!empty($this->to_whom['check_guest_experience_settings']) && !empty($this->booking_info)) {

                $settings = new ClientGeneralPreferencesSettings($this->booking_info->user_account_id);
                $active = $settings->isActiveStatus(
                    config('db_const.general_preferences_form.' . $this->to_whom['check_guest_experience_settings']),
                    $this->booking_info->bookingSourceForm);

                if (!$active)
                    return;
            }


            /** Check if Email Types is set to validate user notify Settings
             * It True then it will check for User Notify Setting as set in Emails Config File.*/
            if (!empty($this->to_whom['check_notify_settings'])) {

                $setting_key = config('db_const.user_notify_Settings.' . $this->to_whom['check_notify_settings']);

                if (!$this->isEmailTypeEnabledToSend($setting_key))
                    return; /*This means client has turned off email notifications for this type*/

                /*explode comma separated emails and make array to send emails*/
                $this->sending_email_address_arr = $this->email_settings->setMailParameters($this->sending_email_address_arr);
            }

            // Email Layout from Config
            $layout = config('db_const.emails.send_to.' . $this->to_whom['id'] . ".template");

            // Set Email Content
            $this->setEmailBody(new $layout($this->getContent()));

            // to team member email's address
            if (in_array($this->email_type_name, ['password_reset', 'email_verification_new_user', 'team_member_invite'])) {

                $this->setToEmail($this->user->email);
            } else {
                $this->setToEmail($this->get_email_address()); // to user Account's emails as per settings
            }

            // Send Email and record sent email.
            if (Mail::to($this->send_to_emails)->send($this->email_body))
                $this->recordSentEmail();
        }
    }


    /**
     * @return bool
     */
    private function validToSend(){
        switch ($this->email_type_name) {
            case config('db_const.emails.heads.new_booking.type'):
                return ($this->booking_info->pre_checkin_status == 0); // Pre-Check.
                break;
            default:
                return true;
                break;
        }
    }

    /**
     * @param array|null $extra_data
     */
    public function recordSentEmail(array $extra_data = null)
    {
        //get what tables and which column's data we need to save according to email type
        $encoded_data = [];
        $data_to_save = $this->to_whom['data_to_save'];

        foreach ($data_to_save as $key => $value) {
            if(isset($this->$key))
            {
                foreach ($value as $data_column) {
                    if(isset($this->$key) && isset($this->$key->$data_column)) {
                        $encoded_data[$key][$data_column] = $this->$key->$data_column;
                    }
                }
            }
        }

        if (!empty($extra_data))
            $encoded_data['extra_data'] = $extra_data;

        $sent_email = new SentEmail([
                //if email is related to booking like payment, cc, booking email -- then save booking_info_id
                'booking_info_id' => isset($this->booking_info->id) ? $this->booking_info->id:null,
                'model_id' => $this->model_id,
                'model_class' => $this->email_type['model'],
                'email_subject' => $this->subject,
                'email_type' => $this->email_type_name,
                'sent_to' =>  $this->to_whom['id'], //Client/Guest/Admin
                'encoded_data' => json_encode($encoded_data)
        ]);

        $sent_email->save();
    }

    public function getGuestFullNameWithTitle()
    {
        $guest_full_name_with_title = isset($this->booking_info->guest_title) ? $this->booking_info->guest_title : '';
        $guest_full_name_with_title .= isset($this->booking_info->guest_name) ? ' ' . $this->booking_info->guest_name : '';
        $guest_full_name_with_title .= isset($this->booking_info->guest_last_name) ? ' ' . $this->booking_info->guest_last_name : '';
        return trim($guest_full_name_with_title);
    }

    public function getGuestFullNameWithoutTitle()
    {
        $guest_full_name_without_title = isset($this->booking_info->guest_name) ? $this->booking_info->guest_name : '';
        $guest_full_name_without_title .= isset($this->booking_info->guest_last_name) ? ' ' .$this->booking_info->guest_last_name : '';
        return trim($guest_full_name_without_title);
    }

    public function getCheckinFormatDMY()
    {
        if(isset($this->property_info) && isset($this->property_info->time_zone) && !is_null($this->property_info->time_zone))
            return Carbon::parse($this->booking_info->check_in_date)->timezone($this->property_info->time_zone)->format('d M, Y');
        else
            return Carbon::parse($this->booking_info->check_in_date)->format('d M, Y');
    }

    public function getCheckoutFormatDMY()
    {
        if(isset($this->property_info) && isset($this->property_info->time_zone) && !is_null($this->property_info->time_zone))
            return Carbon::parse($this->booking_info->check_out_date)->timezone($this->property_info->time_zone)->format('d M, Y');
        else
            return Carbon::parse($this->booking_info->check_out_date)->format('d M, Y');
    }

    public function getCheckinFormatDM()
    {
        if(isset($this->property_info) && isset($this->property_info->time_zone) && !is_null($this->property_info->time_zone))
            return Carbon::parse($this->booking_info->check_in_date)->timezone($this->property_info->time_zone)->format('d M');
        else
            return Carbon::parse($this->booking_info->check_in_date)->format('d M');
    }

    public function getClientBookingDetailUrl($upsell_purchased = null)
    {
        if (!empty($upsell_purchased)) {
            return route('bookingDetailPage', ['id' => $this->booking_info->id, 'upsell-purchased' => $upsell_purchased]); // TODO
        } elseif ($this->email_type_name == 'new_chat_message') {
            return route('bookingDetailPage', ['id' => $this->booking_info->id, 'chat'=> 'open']);
        } else {
            return route('bookingDetailPage', ['id' => $this->booking_info->id]);
        }
    }

    public function getSignedGuestCheckinUrl()
    {
        return URL::signedRoute('step_0', $this->booking_info->id);
    }

    public function getSignedGuestPortalUrl($upsell_purchased = null)
    {
        if (!empty($upsell_purchased)) {
            return URL::signedRoute('guest_portal', ['id' => $this->booking_info->id, 'upsell-purchased' => $upsell_purchased]);
        } else {
            return URL::signedRoute('guest_portal', ['id' => $this->booking_info->id]);
        }
    }

    public function getClientSettingUrl()
    {
        $property_emails = [
            config('db_const.emails.heads.properties_unavailable_on_pms.type'),
            config('db_const.emails.heads.properties_deactivated.type'),
            config('db_const.emails.heads.properties_activated.type'),
            config('db_const.emails.heads.empty_property_key_received.type')
        ];

        if (!empty($this->property_info) || in_array($this->email_type_name, $property_emails)) {
            return URL::to('/client/v2/properties');
        } else {
            return URL::to('/client/v2/pmsintegration');
        }
    }


    public function getDashboardUrl()
    {
        return URL::to('/client/v2/dashboard');
    }



    public function getCompanyLogo()
    {
        return asset('storage/uploads/companylogos/'.$this->user_account->company_logo);
    }

    public function getFromEmailOfProperty()
    {
        return [ (!is_null($this->property_info->property_email) ? $this->property_info->property_email : (!is_null($this->user_account->email) ? $this->user_account->email : $this->user_account->users->first()->email)),  $this->property_info->name ];
    }

    public function getReplyToEmailOfProperty()
    {
        return [ (!is_null($this->property_info->property_email) ? $this->property_info->property_email : (!is_null($this->user_account->email) ? $this->user_account->email : $this->user_account->users->first()->email)),  $this->property_info->name ];
    }

    public function getPropertyLogo()
    {
        return ($this->property_info->logo == 'no_image.png' || empty($this->property_info->logo) ) ? '' : asset('storage/uploads/property_logos/'.$this->property_info->logo);
    }

    public function getPropertyInitial()
    {
        //use common helper function to get initials from property name
        return getInitialsFromString($this->property_info->name, 3);
    }

    public function getSubject()
    {
        $email_type_text = config('db_const.sent_email.all_emails.'.$this->email_type_name.'.subject_text');
        return $email_type_text.' | '.$this->getCheckinFormatDM().' | '.$this->getGuestFullNameWithoutTitle().' | '.$this->booking_info->pms_booking_id;
    }

    public function getCommonGuestEmailDataForEveryEmail()
    {
        $data = [
            'subject' => $this->subject,
            'email_tags_for_tracking' => config('db_const.sent_email.all_emails.'.$this->email_type_name.'.tags'),
            'from' => $this->getFromEmailOfProperty(),
            'reply_to' => $this->getReplyToEmailOfProperty(),
            'guest_full_name' => $this->getGuestFullNameWithoutTitle(),
            'property_name' => $this->property_info->name,
            'property_logo' => $this->getPropertyLogo(),
            'property_initial' => $this->getPropertyInitial(),
            'show_secure_process_text' => true,
            'button_url'=> $this->getSignedGuestPortalUrl(
                in_array($this->email_type_name, ['upsell_marketing', 'upsell_purchased']) ? 'upsell' : null
            ),
        ];

        $this->checkFor3DsLink($data);
        $this->setCustomUrlForGuestEmail($data);

        return $data;
    }

    /**
     * Update Link button Url if 3_DS required Type Email
     * @param $data
     */
    public function checkFor3DsLink(&$data) {
        $_3ds_types = [
            'auth_3ds_required' => self::MAIL_FOR_3DS_AUTH,
            'charge_3ds_required' => self::MAIL_FOR_3DS_CHARGE,
            'sd_3ds_required' => self::MAIL_FOR_3DS_AUTH_SSD
        ];

        if (array_key_exists($this->email_type_name, $_3ds_types)) {
            $id="";
            if(!empty($this->authorization_info)){
                $id = $this->authorization_info->id;
            }elseif(!empty($this->transaction_info)){
                $id = $this->transaction_info->id;
            }
            $data['button_url'] = URL::signedRoute('checkout',
                [
                    'id' => $id,
                    'type' => $_3ds_types["$this->email_type_name"]
                ]
            );
        }
    }

    public function setCustomUrlForGuestEmail(&$data){

        switch ($this->email_type_name){
            case 'guest_document_rejected':
                if($this->booking_info->pre_checkin_status == 0){
                    $data['button_url'] = Url::signedRoute('step_3', $this->booking_info->id);
                }else{
                    $data['button_url'] = URL::signedRoute('guest_portal', ['id' => $this->booking_info->id]).'#document_upload_tab';
                }
                break;
            case 'new_chat_message':
                if(strtolower($this->send_to_whom_name) == 'guest'){
                    //url hashing in order to open chat panel at Guest Pre Checkin & Guest Portal
                    if($this->booking_info->pre_checkin_status == 0){
                       $data['button_url'] = $this->getSignedGuestCheckinUrl().'#open_chat';
                    }else{
                        $data['button_url'] = URL::signedRoute('guest_portal', ['id' => $this->booking_info->id]).'#open_chat';
                    }
                }
        }
    }

    public function getCommonClientEmailDataForEveryEmail()
    {

        $data =[
            'subject' => $this->subject,
            'bottom_info_line' => 'Tips: You can view & manage reservation payments, collect additional payment, security deposit, Upsell orders & Guest documents.'
        ];

        $this->setClientEmailUrl($data);
        return $data;
    }


    public function setClientEmailUrl(&$data)
    {
        switch ($this->email_type_name) {

            case 'missing_billing_info':
                $data['button_url'] = URL::signedRoute('commission-billing-details', ['id' => $this->user_account->id, 'name' => '-']);
                break;

            case 'team_member_added_inform_client':
                $data['button_url'] = URL::to('/client/v2/manageteam');
                break;

            case 'password_reset':
                $data['button_url'] = route('password.reset', [$this->extra_data['token']]);
                break;

            case 'email_verification_new_user':
            case 'team_member_invite':
                $data['button_url'] = URL::signedRoute('verification', ['user' => $this->user->id, 'email' =>   $this->user->email]);
                break;

            case 'payment_passed_due_date':
                $data['button_url'] = URL::signedRoute(
                    'cancelBdcBookingDetailPage',
                    [
                        'user_account_id' => $this->user_account->id,
                        'booking_info_id' => $this->booking_info->id,
                    ]
                );
                break;

            default:
                if (!empty($this->booking_info)) {
                    $data['button_url'] = $this->getClientBookingDetailUrl(
                        in_array($this->email_type_name, ['upsell_marketing', 'upsell_purchased'])?'upsell':null
                    );
                } else {
                    $data['button_url'] = $this->getClientSettingUrl();
                }
                break;
        }
    }


    /**
     * @return array
     */
    public function getDefaultData() {

        $data = [];

        switch (strtolower($this->send_to_whom_name)) {
            // GUEST
            case 'guest':
                $data = $this->getCommonGuestEmailDataForEveryEmail();
                break;

            // Client
            case 'client':
                $data = $this->getCommonClientEmailDataForEveryEmail();
                break;

            default:
                $data['button_url'] = $this->getDashboardUrl();
                break;
        }

        return $data;
    }
    /** Following Function Will Get Email Address
     */
    public function get_email_address()
    {
        if (empty($this->sending_email_address_arr)) {
            if ($this->send_to_whom_name == 'client') {
                if(!empty($this->user)) {
                    $to = $this->user->email;
                } elseif (!empty($this->user_account)) {
                    $to = getDefaultEmailAddressOfUserAccount($this->user_account);
                }
            } elseif ($this->send_to_whom_name == 'guest') {
                $to=$this->getGuestEmails();
            }
        } else {
            $to = $this->sending_email_address_arr['to'];
        }

        return $to;
    }

    /**
     * Following Function Will Set Required Variables data form Booking info
     */
    public function set_required_data_from_booking_info($booking_info_id){
        $this->booking_info = BookingInfo::with('user_account')->where('id', $booking_info_id)->first();
        $this->property_info = $this->booking_info->property_info;
        $this->user_account = $this->booking_info->user_account;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getContent() {

        if (empty($this->email_type['system_default'])) {

            $content = EmailContentService::content(
                $this->email_type_name,
                empty($this->booking_info)
            )->{strtolower($this->send_to_whom_name) . "Content"}(
                $this->model_id,
                $this->user_account->id
            );
            $content = $this->parseRemainingVars($content);
            $this->subject = $content['subject'];

            $templateData = [
                'email_title' => ['text' => $content['subject'], 'text_color' => '#153643'],
                'button_text' => $content['button_text'],
                'top_paragraph' => $content['message'],
                'show_button' => filter_var($content['show_button'], FILTER_VALIDATE_BOOLEAN),
                'show_secure_process_text' => true,
            ];

        } else {

            $templateData = $this->{$this->email_type_name}();
        }

        //dd($this->setExtraContent($templateData));
        return $this->setExtraContent($templateData);
    }

    /**
     * @param array $templateData
     * @return array
     */
    public function setExtraContent(array $templateData) {

        switch ($this->email_type_name) {
            case 'upsell_purchased':
                $upsell_repository = new UpsellRepository();
                $all_upsells = collect($upsell_repository->upsellOrderList($this->booking_info->id));
                $purchased_upsells = $all_upsells->where('upsell_order_id', $this->model_id);
                $templateData['upsell_details_array'] = $purchased_upsells->transform(function($item){
                    return [
                        'type' => $item['type'] ?? 'N/A',
                        'description' => $item['description'],
                        'per' => $item['per']['label'],
                        'period' => $item['period']['label'],
                        'currency_symbol' => get_currency_symbol($this->property_info->currency_code),
                        'amount' => $item['value_type'] == config('db_const.upsell_listing.value_type.percentage')
                            ? round(($this->booking_info->total_amount / 100) * $item['original_value'])
                            : $item['original_value'],
                    ];
                });
                break;

            case 'upsell_marketing':
                $templateData['upsell_details_array'] = $this->upsells->transform(function($item) {
                    $desc = convertTemplateVariablesToActualData(BookingInfo::class, $this->booking_info->id, ['desc' => $item->meta->description]);
                    return [
                        'type' => $item->upsellType->title ?? 'N/A',
                        'description' => $desc['desc'],
                        'per' => get_config_column_values('upsell_listing' , 'per', $item->per)['label'],
                        'period' => get_config_column_values('upsell_listing', 'period', $item->period)['label'],
                        'currency_symbol' => get_currency_symbol($this->property_info->currency_code),
                        'amount' => $item->value_type == config('db_const.upsell_listing.value_type.percentage')
                            ? round(($this->booking_info->total_amount / 100) * $item->value)
                            : $item->value,
                    ];
                });
                break;

            case 'booking_source_deactivated':
                $templateData['booking_details_array'] = [
                    'property' => $this->labelForLocalGlobal('payment'),
                    'security_deposit' => ['label' => 'Security Deposit', 'value' => 'OFF', 'value_text_color' => '#dc3545'],
                    'payment_scheduling' => ['label' => 'Payment Scheduling', 'value' => 'OFF', 'value_text_color' => '#dc3545'],
                    'cancellation_policy' => ['label' => 'Cancellation Policy', 'value' => 'OFF', 'value_text_color' => '#dc3545'],
                    'card_authorization' => ['label' => 'Card Authorization', 'value' => 'OFF', 'value_text_color' => '#dc3545']
                ];
                break;

            case 'booking_source_activated':
                $settings = BookingSources::MapSettingsByJsonStringFromBridgeTableWithModelRelations($this->user_booking_source);
                $templateData['booking_details_array'] = [
                    'property' => $this->labelForLocalGlobal('payment'),
                    'security_deposit'    =>  ['label' => 'Security Deposit',     'value' => !empty($settings['security_deposit']->status) ? 'ON':'OFF', 'value_text_color' => !empty($settings['security_deposit']->status) ? '#1EAF24':'#dc3545' ],
                    'payment_scheduling'  =>  ['label' => 'Payment Scheduling',   'value' => !empty($settings['booking_payment']->status) ? 'ON':'OFF', 'value_text_color' => !empty($settings['booking_payment']->status) ? '#1EAF24':'#dc3545' ],
                    'cancellation_policy' =>  ['label' => 'Cancellation Policy',  'value' => !empty($settings['return_rules']->status) ? 'ON':'OFF', 'value_text_color' => !empty($settings['booking_deposit']->status) ? '#1EAF24':'#dc3545' ],
                    'card_authorization'  =>  ['label' => 'Card Authorization',   'value' => !empty($settings['booking_deposit']->status) ? 'ON':'OFF', 'value_text_color' => !empty($settings['return_rules']->status) ? '#1EAF24':'#dc3545' ],
                ];
                break;

            case 'properties_unavailable_on_pms':
            case 'empty_property_key_received':
            case 'properties_deactivated':
            case 'properties_activated':
                $properties = $this->user_account->all_properties->whereIn('id', $this->extra_data['properties_info_ids']);
                if($properties) {
                    foreach ($properties as $key=>$property) {
                        $templateData['booking_details_array']['property_'.$key] = [ 'value' => $property->name.' (#'.$property->pms_property_id.')' ];
                    }
                }
            break;

            case 'booking_fetch_failed':
                $templateData['booking_details_array'] = [
                    'failed_reason' => [ 'label' => 'Failure Reason', 'value' => $this->extra_data['exceptionMsg'] ?? 'N/A', 'value_text_color' => '#dc3545' ],
                    'error_code' => [ 'label' => 'Error Code', 'value' => $this->extra_data['errorCode'] ?? 'N/A', 'value_text_color' => '#dc3545' ],
                    'pms_booking_id' => [ 'label' => 'Booking ID', 'value' => $this->extra_data['pms_booking_id'] ?? 'N/A']
                ];
                break;

            case 'credit_card_not_added_payment_gateway_error':
                /*$templateData['booking_details_array'] = [
                        'reason' => [ 'label' => 'Reason', 'value' => $this->extra_data['exceptionMsg'] , 'value_text_color' => '#dc3545'],
                ];*/
                break;
        }

        return array_merge($templateData, $this->getDefaultData());
    }

    /**
     * @param $content
     * @return mixed
     */
    private function parseRemainingVars($content)
    {
        $vars = preg_grep('~' . preg_quote('{', '~') . '~', array_flatten($content));
        if(count($vars)){
            $data_from_extras = [
                '{Transaction_Response}' => 'reason',
                '{Transaction_Price}' => 'amount_to_refund',
                '{Authorization_Response}' => 'error_msg',
                '{Refund_Remarks}' => 'reason',
                '{Refund_Amount}' => 'amount_to_refund',
            ];
            $content_keys = array_keys($content);
            $content = implode(' <A/R> ', $content);
            foreach ($data_from_extras as $templateVar => $extra_data_key) {
                $extra_data = (!empty($this->extra_data[$extra_data_key]) ? $this->extra_data[$extra_data_key] : "N/A");
                if (strpos($content, $templateVar)) {
                    $content = str_replace($templateVar, $extra_data, $content);
                }
            }
            $content = array_combine($content_keys,explode(' <A/R> ',$content));
        }

        return $content;
    }


    private function labelForLocalGlobal($setting_name) {
        return [
            'label' => 'Property',
            'value' =>  empty($this->property_info)
                ? 'Properties with global '.$setting_name.' settings'
                : ($this->property_info->name.'(#'.$this->property_info->pms_property_id.')'),
        ];
    }

}
