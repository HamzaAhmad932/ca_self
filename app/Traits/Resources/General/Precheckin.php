<?php


namespace App\Traits\Resources\General;


use App\GuestImage;
use App\Upsell;
use Carbon\Carbon;
use App\BookingInfo;
use App\CaCapability;
use App\Services\CapabilityService;
use App\CreditCardAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\System\PMS\BookingSources\BS_Generic;
use App\Repositories\Upsells\UpsellRepository;
use App\Traits\Resources\General\GuestPortal;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\PaymentGateways\PaymentGateways;

trait Precheckin
{
    use GuestPortal;

    public $timezone;
    public $symbol;

    protected function getNextPageData($current_step, $booking_id){

        $guest_experience_setting = $this->checkRequiredStatusOfMetaInformation($booking_id);

        $next = [];
        $count = 0;

        $step_0 = [
            'title'=> '',
            'icon'=> '',
            'count'=> $count,
            'default_step_name'=> 'step_0',
            'default_step_num'=> 0,
            'show_step'=> false,
        ];
        array_push($next, $step_0);
        $count++;

        $response_body = [
            'current_step'=> $current_step,
            'routes'=> $next,
            'is_credit_card_scan_required'=> $guest_experience_setting['required_credit_card_scan'],
            'is_passport_scan_required'=> $guest_experience_setting['required_passport_scan'],
            'read_only_mode'=> !empty($mode) ? $mode['status'] : 0,
            'booking_id'=> $booking_id,
            'is_guest'=> Auth::guest()
        ];

        if(empty($guest_experience_setting['booking_info'])){
            return $response_body;
        }

        $room_info = $guest_experience_setting['booking_info']->room_info;
        $capablities = CapabilityService::allCapabilities($guest_experience_setting['booking_info']);
        $attached_upsell = !empty($room_info) ? $room_info->upsells()->where('status', Upsell::STATUS_ACTIVE) : null;
        $is_upsell_available = !empty($attached_upsell) && $attached_upsell->count() > 0;
        $upg = new PaymentGateways();
        $user_payment_gateway = $upg->getPropertyPaymentGatewayFromBooking($guest_experience_setting['booking_info']);
        $transaction_count = $guest_experience_setting['booking_info']->transaction_init->count();
        $booking_auth_count = $guest_experience_setting['booking_info']->credit_card_authorization->count();
        $payment_entries_exist = !($transaction_count == 0 && $booking_auth_count == 0);
        $credit_card_step_title = 'Credit Card';
        $is_cart = $guest_experience_setting['booking_info']->upsellCarts->count() > 0 ;

        $card_required = $this->getCardInfo($guest_experience_setting['booking_info']);

        if($payment_entries_exist) {
            if (!empty($user_payment_gateway)
                && !$is_cart
                && !empty($card_required['need_to_update_card'])) {

                $credit_card_step = [
                    'title'=> $credit_card_step_title,
                    'icon'=> 'fas fa-credit-card',
                    'count'=> $count,
                    'default_step_name'=> 'step_5',
                    'default_step_num'=> 5,
                    'show_step'=> true,
                ];
                array_push($next, $credit_card_step);
                $count++;
            }
        }

        if($guest_experience_setting['required_basic_info']){
            $basic_info_step = [
                'title'=> 'Basic Info',
                'icon'=> 'fa fa-info',
                'count'=> $count,
                'default_step_name'=> 'step_1',
                'default_step_num'=> 1,
                'show_step'=> true,
            ];
            array_push($next, $basic_info_step);
            $count++;

        }
        if ($guest_experience_setting['required_arrival_info']){

            $arrival_step = [
                'title'=> 'Arrival',
                'icon'=> 'fas fa-plane-arrival',//fas fa-door-open
                'count'=> $count,
                'default_step_name'=> 'step_2',
                'default_step_num'=> 2,
                'show_step'=> true,
            ];
            array_push($next, $arrival_step);
            $count++;

        }

        if($capablities[CaCapability::AUTO_PAYMENTS] || $capablities[CaCapability::SECURITY_DEPOSIT]){

            if (
                $guest_experience_setting['required_passport_scan'] ||
                $guest_experience_setting['required_credit_card_scan']
            ){

                $verification_step = [
                    'title'=> 'Verification',
                    'icon'=> 'fas fa-file-signature',//far fa-edit
                    'count'=> $count,
                    'default_step_name'=> 'step_3',
                    'default_step_num'=> 3,
                    'show_step'=> true,
                ];
                array_push($next, $verification_step);
                $count++;
            }
        }

        if(!empty($user_payment_gateway) && $guest_experience_setting['add_on_service'] && $is_upsell_available){

            $add_on_services = [
                'title'=> 'Add-on Services',
                'icon'=> 'far fa-star',// fas fa-star
                'count'=> $count,
                'default_step_name'=> 'step_4',
                'default_step_num'=> 4,
                'show_step'=> true,
            ];
            array_push($next, $add_on_services);
            $count++;
        }


        //dd($current_step);
        if($guest_experience_setting['add_on_service']
            && $is_upsell_available ) {

            if(!empty($user_payment_gateway)) {


                $credit_card_step = [
                    'title'=> 'Credit Card',
                    'icon'=> 'fas fa-credit-card',
                    'count'=> $count,
                    'default_step_name'=> 'step_5',
                    'default_step_num'=> 5,
                    'show_step'=> $is_cart,//true,
                ];
                array_push($next, $credit_card_step);
                $count++;

                // IF Credit Card Step Shown after add-ons then hide Credit card step on 1st step.
                if (isset($next[1]['title']) && ($next[1]['title'] == $credit_card_step_title)
                    && $is_cart) {
                    $next[1]['show_step'] = false;
                }
            }
        }

        if ($guest_experience_setting['guest_selfie']) {

            $selfie_Step = [
                'title'=> 'Self Portrait',
                'icon'=> 'fas fa-id-badge',//far fa-image
                'count'=> $count,
                'default_step_name'=> 'step_6',
                'default_step_num'=> 6,
                'show_step'=> true];

            array_push($next, $selfie_Step);
            $count++;
        }

        $summary_step = [
            'title'=> 'Your Summary',
            'count'=> $count,
            'default_step_name'=> 'step_7',
            'default_step_num'=> 7,
            'show_step'=> false,
        ];
        array_push($next, $summary_step);
        $count++;

        $precheckin_complete = [
            'title'=> 'Precheck In Complete',
            'count'=> $count,
            'default_step_name'=> 'step_8',
            'default_step_num'=> 8,
            'show_step'=> false,
        ];
        array_push($next, $precheckin_complete);
        $count++;

        $mode = null;
        if(!Auth::guest()) {
            $mode = $this->setPrecheckinSessionForClient($guest_experience_setting['booking_info']->id);
        }
        $property = $guest_experience_setting['booking_info']->property_info;

        return [
            'booking_id'=> $guest_experience_setting['booking_info']->id,
            'current_step'=>$current_step,
            'is_credit_card_scan_required'=> $guest_experience_setting['required_credit_card_scan'],
            'is_guest'=> Auth::guest(),
            'is_passport_scan_required'=> $guest_experience_setting['required_passport_scan'],
            'property_name' => $property->name,//.' - '.$property->pms_property_id,
            'read_only_mode'=> !empty($mode) ? $mode['status'] : 0,
            'routes'=> $next,
        ];
    }

    public function setPrecheckinSessionForClient(int $booking_id){

        $modes = Session::get('precheckin');
        if(!empty($modes)){
            $mode = array_key_exists($booking_id, $modes) ? $modes[$booking_id] : False;
            if(!empty($mode)){
                $time_difference = now()->diffInMinutes($mode['created_at']);
                if($time_difference > 14){
                    $modes[$booking_id]['created_at'] = now();
                    $modes[$booking_id]['status'] = 1;
                    Session::put(['precheckin'=> $modes]);
                }
            }else{

                $modes[$booking_id]['created_at'] = now();
                $modes[$booking_id]['status'] = 1;
                Session::put(['precheckin'=> $modes]);
            }
        }else{

            Session::put(['precheckin'=> [ $booking_id => ['status'=> 1, 'created_at'=> now()]]]);
        }

        Session::save();
        return Session::get('precheckin')[$booking_id];
    }

    public  function getCardInfo(BookingInfo $booking){

        $meta = $this->checkMetaInformation($booking);
        $card = [
            'card_available'=> false,
            'cc_last_digit'=>'',
            'card_type'=>'',
            'expiry'=> '',
            'sd_auth_present'=> false,
            'sd_msg'=> ''
        ];

        if(!$booking->cc_Infos->isEmpty()){
            $latest_card = $booking->cc_infos->last();
            $card['cc_last_digit'] = $latest_card->cc_last_4_digit;
            $card['expiry'] = $latest_card->cc_exp_month.'/'.$latest_card->cc_exp_year;
            $card['card_available'] = true;
            if($latest_card->is_vc == '1'){
                $card['card_type'] = 'VC';
            }else{
                $card['card_type'] = 'CC';
            }
        }

        $upsellRepository = new UpsellRepository();
        $upsell_order = $upsellRepository->getUpsellOrdersAndCart($booking->id);

        $auth_scheduled = $booking->credit_card_authorization->whereIn('status', [
            CreditCardAuthorization::STATUS_PENDING,
            CreditCardAuthorization::STATUS_MANUAL_PENDING
        ])->whereIn('type', [19, 20])->first();

        $auth_failed = $booking->credit_card_authorization->whereIn('status', [
            CreditCardAuthorization::STATUS_FAILED,
            CreditCardAuthorization::STATUS_REATTEMPT
        ])->whereIn('type', [19, 20])->first();

        if(!empty($auth_scheduled)){
            $card['sd_auth_present'] = true;
            $card['sd_msg'] .= nl2br("To confirm your reservation, refundable security deposit of ".$this->symbol.$auth_scheduled->hold_amount." is required.");
            $card['sd_msg'] .= nl2br(" A temporary hold of ".$this->symbol.$auth_scheduled->hold_amount." will be placed on your card on ".Carbon::parse($auth_scheduled->due_date)->timezone($this->timezone)->format('F d, Y').". This is not charge and should disappear from your bank statement after checkout.");
            if(!$card['card_available']){
                $meta['need_to_update_card'] = true;
            }else{
                if($card['card_type'] == 'VC'){
                    $meta['need_to_update_card'] = true;
                }
            }
        }

        if(!empty($auth_failed)){
            $card['sd_auth_present'] = true;
            $card['sd_msg'] .= nl2br("Authorization of ".$this->symbol.$auth_failed->hold_amount." Refundable Security Deposit Failed. Please update below with a valid credit card.");
            $meta['need_to_update_card'] = true;
        }

        if(!empty($upsell_order)) {
            //need to update card in VC card case for upsell charge
            if($card['card_available']){
                $meta['need_to_update_card'] = ($card['card_type'] == 'VC' && $upsell_order['amount_due'] > 0) ? true : $meta['need_to_update_card'];


            }else{
                $meta['need_to_update_card'] = ($upsell_order['amount_due'] > 0) ? true : $meta['need_to_update_card'];
            }
        }

        unset($meta['booking_info']);

        return array_merge($card, $meta);
    }

    public function cardInfo(BookingInfo $booking_info)
    {
        $new_card = [
            'name' => $booking_info->guest_name . ' ' . $booking_info->guest_last_name,
            'number' => '',
            'expiry' => '',
            'cvv' => ''
        ];

        
        $upg = new PaymentGateways();
        $userPaymentGateway = $upg->getPropertyPaymentGatewayFromBooking($booking_info);
                
        $paymentGateway = new PaymentGateway();
        $terminalData = $paymentGateway->getTerminal($userPaymentGateway);

        $new_card['pgTerminal'] = $terminalData;
        $new_card['pgTerminal']['first_name'] = $booking_info->guest_name;
        $new_card['pgTerminal']['last_name'] = $booking_info->guest_last_name;
        $new_card['pgTerminal']['booking_id'] = $booking_info->id;
        $new_card['pgTerminal']['with3DsAuthentication'] = true;
        $new_card['pgTerminal']['show_authentication_button'] = false;

        return $new_card;
    }

    public function guestImagesStatus($guest_images)
    {
        $guest_images_status = [];

        foreach ($guest_images as $image) {
            $guest_images_status[$image->type] = $image->status;
        }

        return $guest_images_status;
    }

    public function guestDocumentTransform($images){

        $img_status_info = Config::get('db_const.guest_images.status_with_badge');
        $hide_status_for_types = ['selfie', 'signature'];

        $images->transform(function ($img) use($img_status_info, $hide_status_for_types){

            $status = $img_status_info[$img->status];
            if(in_array($img->type, $hide_status_for_types)){
                $status['display'] = false;
            }

            return [
                'id'=> $img->id,
                'type'=> $img->type,
                'status'=> $status,
                'image'=> '/'.GuestImage::PATH_IMAGES.$img->image,
                'description'=> $img->description
            ];
        });
    }

}
