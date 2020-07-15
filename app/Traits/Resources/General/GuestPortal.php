<?php


namespace App\Traits\Resources\General;

use App\BookingInfo;
use App\CreditCardAuthorization;
use App\GuestImage;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\Upsells\UpsellRepository;
use App\Services\CapabilityService;
use App\System\PMS\BookingSources\BS_Generic;
use App\TransactionInit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

trait GuestPortal
{
    public $timezone;
    public $symbol;

    public function getMapStatus($property)
    {

        $map = new \stdClass();
        $map->status = true;
        $map->query = '';
        if (
            !empty($property->longitude)
            && !empty($property->latitude)
            && validateLatLong($property->longitude, $property->latitude)
        ) {
            $map->query = $property->latitude . ',' . $property->longitude;
        } elseif (!empty($property->address)) {

            $address = $property->address . ' ' . $property->city . ' ' . $property->country;
            $map->query = str_replace(' ', '+', $address);
        } else {
            $map->status = false;
        }

        return $map;
    }

    public function getPayments($transactions)
    {
        try {
            $payments = [];
            foreach ($transactions as $transaction) {

                $trans = new \stdClass();
                $trans->date = Carbon::parse($transaction->due_date)->timezone($this->timezone)->format('d F Y');
                $trans->amount = $this->symbol . number_format($transaction->price,2);
                $trans->status = config('db_const.transactions_init.payment_status_messages_for_guest.' . $transaction->payment_status);
                $trans->status_class = config('db_const.transactions_init.transaction_type_status_color.' . $transaction->payment_status);
                $trans->icon = config('db_const.transactions_init.status_icon.' . $transaction->payment_status);
                $trans->client_remarks = $transaction->client_remarks;
                array_push($payments, $trans);
            }

            return $payments;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getDeposits($deposits)
    {
        try {
            $sd_auth = $cc_auth = [];
            $sd_auth_found = $cc_auth_found = FALSE;
            foreach ($deposits as $deposit) {
                $ds = new \stdClass();
                $ds->date = Carbon::parse($deposit->due_date)->timezone($this->timezone)->format('d F Y');
                $ds->amount = $this->symbol . number_format($deposit->hold_amount, 2);
                $ds->status = config('db_const.credit_card_authorizations.authorization_status_messages_for_guest.' . $deposit->status);
                $ds->status_class = config('db_const.credit_card_authorizations.status_color_for_guest.' . $deposit->status);
                $ds->icon = config('db_const.credit_card_authorizations.status_icon.' . $deposit->status);
                if ($deposit->type == config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth') || $deposit->type == config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth')) {
                    array_push($sd_auth, $ds);
                    $sd_auth_found = TRUE;
                } elseif ($deposit->type == config('db_const.credit_card_authorizations.type.credit_card_auto_authorize') || $deposit->type == config('db_const.credit_card_authorizations.type.credit_card_manual_authorize')) {
                    array_push($cc_auth, $ds);
                    $cc_auth_found = TRUE;
                }
            }

            return array('sd_auth_found' => $sd_auth_found, 'sd_auth' => $sd_auth, 'cc_auth_found' => $cc_auth_found, 'cc_auth' => $cc_auth);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getBookingStatus($status)
    {

        $status_name = array_search($status, config('db_const.booking_info.pms_booking_status'));
        return $status_name === false ? '' : $status_name;
    }

    public function checkMetaInformation(BookingInfo $booking_info)
    {

        $status = [
            'need_to_update_card' => false,
            'is_invalid_card' => false,
            'card_missing' => false,
            'is_payment_failed' => false,
            'need_passport_scan' => false,
            'need_credit_card_scan' => false
        ];

        $upsellRepository = new UpsellRepository();
        $upsell_order = $upsellRepository->getUpsellOrdersAndCart($booking_info->id);

        if (!CapabilityService::isAutoPaymentSupported($booking_info) && $booking_info->credit_card_authorization->count() == 0) {
            return $status;
        }

        if (($booking_info->is_vc == BS_Generic::PS_BANK_TRANSFER) && ($booking_info->credit_card_authorization->count() == 0) && !($upsell_order['amount_due'] > 0)) {
            return $status;
        }

        $cc_info = $booking_info->cc_Infos()
            ->where('is_vc', 0)
            ->latest('id')
            ->limit(1)
            ->with('transaction_details')
            ->with('ccauth')
            ->first();

        if (!is_null($cc_info)) {

            if ($cc_info->cc_last_4_digit == '') {
                $status['card_missing'] = true;
            }
            if (empty($cc_info->customer_object->token)) {
                $status['need_to_update_card'] = true;
                $status['is_invalid_card'] = true;
            } else {
                $trans = $booking_info->transaction_init->where('updated_at', '>', $cc_info->updated_at)->pluck('payment_status')->toArray();
                $auth = $cc_info->ccauth->where('updated_at', '>', $cc_info->updated_at)->pluck('status')->toArray();

                if (!empty($trans) && (in_array(TransactionInit::PAYMENT_STATUS_FAIL, $trans) || in_array(TransactionInit::PAYMENT_STATUS_REATTEMPT, $trans))) {
                    $status['need_to_update_card'] = true;
                    $status['is_payment_failed'] = true;
                }

                if (!empty($auth) && (in_array(CreditCardAuthorization::STATUS_REATTEMPT, $auth) || in_array(CreditCardAuthorization::STATUS_FAILED, $auth))) {
                    $status['need_to_update_card'] = true;
                    $status['is_payment_failed'] = true;
                }
            }
        } else if ($booking_info->is_vc == BS_Generic::PS_CREDIT_CARD && empty($cc_info) && $booking_info->is_process_able == '1') {
            $status['need_to_update_card'] = true;
            $status['is_invalid_card'] = true;

        }

        $status['need_guest_verification'] = $this->checkImageUploadStatus($booking_info, false);
        $status['need_credit_card_scan'] = $this->checkImageUploadStatus($booking_info, true);
        $required_information = $this->checkRequiredStatusOfMetaInformation($booking_info->id);

        return array_merge($status, $required_information);
    }

    public function checkImageUploadStatus(BookingInfo $booking_info, $is_card_scan)
    {

        $flag = false;
        if ($is_card_scan) {
            $guest_images = $booking_info->guest_images->where('type', 'credit_card');
        } else {
            $guest_images = $booking_info->guest_images->where('type', '!=', 'credit_card');
        }

        if (!$guest_images->isEmpty()) {
            foreach ($guest_images as $img) {
                if ($img->status == '2') {
                    $flag = true;
                }
            }
        } else {
            $flag = true;
        }

        return $flag;
    }

    public function checkRequiredStatusOfMetaInformation($booking_info_id)
    {

        $required = [
            'enable_guest_chat' => false,
            'required_basic_info' => false,
            'required_arrival_info' => false,
            'required_passport_scan' => false,
            'required_credit_card_scan' => false,
            'guest_selfie' => false,
            'signature_pad' => false,
            'tac' => false, // tac stands for Terms and Conditions
        ];
        $booking_info = BookingInfo::find($booking_info_id);
        if (empty($booking_info)) {
            return $required;
        }
        $booking_source_form = $booking_info->bookingSourceForm;
        $generalPreferencesSettings = new ClientGeneralPreferencesSettings($booking_info->user_account_id);
        $required['enable_guest_chat'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.guestChatFeature'), $booking_source_form) == '1' ? true : false;
        $required['required_basic_info'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.basicInfo'), $booking_source_form) == '1' ? true : false;
        $required['required_arrival_info'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.arrival'), $booking_source_form) == '1' ? true : false;
        // $verification = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.verification'), $booking_source_form_id);
        $required['required_passport_scan'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.requiredPassportScan'), $booking_source_form) == '1' ? true : false;
        $required['required_credit_card_scan'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.requiredCreditCardScan'), $booking_source_form) == '1' ? true : false;
        $required['guest_selfie'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.guest-selfie'), $booking_source_form) == '1' ? true : false;
        $required['signature_pad'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.digitalSignaturePad'), $booking_source_form) == '1' ? true : false;
        $required['add_on_service'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.upsell'), $booking_source_form) == '1' ? true : false;
        $required['tac'] = $generalPreferencesSettings->isActiveStatus(config('db_const.general_preferences_form.termsAndCondition'), $booking_source_form) == '1' ? true : false;

        //just need to return the booking info modal object
        $required['booking_info'] = $booking_info;
        return $required;
    }

    public function getAuthInformation(BookingInfo $booking_info)
    {

        $auths = [];

        $payment_type_meta = new PaymentTypeMeta();

        $auths['cc_auth'] = $booking_info->credit_card_authorization->whereIn('type', [
            $payment_type_meta->getCreditCardAutoAuthorize(),
            $payment_type_meta->getCreditCardManualAuthorize()
        ])->first();

        $auths['security_auth'] = $booking_info->credit_card_authorization->whereIn('type', [
            $payment_type_meta->getAuthTypeSecurityDamageValidation(),
            $payment_type_meta->getSecurityDepositManualAuthorize()
        ])->first();

        $auths['security_auth_alert'] = 'Refundable Security Deposit amount pending.';
        $auths['cc_auth_alert'] = 'Credit card auth amount pending.';

        if (!empty($auths['cc_auth'])) {
            $auths['cc_auth_alert'] = $this->symbol . $auths['cc_auth']->hold_amount . ' Credit card auth due on ' . Carbon::parse($auths['cc_auth']->due_date)->timezone($this->timezone)->format('d F Y');
        }

        if (!empty($auths['security_auth'])) {
            $auths['security_auth_alert'] = $this->symbol . $auths['security_auth']->hold_amount . ' refundable security deposit due on ' . Carbon::parse($auths['security_auth']->due_date)->timezone($this->timezone)->format('d F Y');
        }

        return $auths;
    }

    public function getDigitalSignature(BookingInfo $booking_info)
    {
        $guest_images = $booking_info->guest_images;
        $signature = '';

        foreach ($guest_images as $guest_image) {
            if ($guest_image->type == 'signature') {
                $signature = $guest_image;
            }
        }

        return $signature;
    }

    public function guestDocumentTransform($images)
    {

        $img_status_info = Config::get('db_const.guest_images.status_with_badge');
        $hide_status_for_types = ['selfie', 'signature'];

        $images->transform(function ($img) use ($img_status_info, $hide_status_for_types) {

            $status = $img_status_info[$img->status];
            if (in_array($img->type, $hide_status_for_types)) {
                $status['display'] = false;
            }

            return [
                'id' => $img->id,
                'type' => $img->type,
                'status' => $status,
                'image' => '/' . GuestImage::PATH_IMAGES . $img->image,
                'description' => $img->description
            ];
        });
    }
}
