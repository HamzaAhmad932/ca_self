<?php


namespace App\Http\Resources\General\Precheckin;


use App\BookingInfo;
use App\CaCapability;
use App\GuestImage;
use App\PropertyInfo;
use App\Services\CapabilityService;
use App\TermsAndCondition;
use App\Traits\Resources\General\Precheckin;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SummaryStepResource extends JsonResource
{
    use Precheckin;

    private $booking_id;

    public function __construct($booking_id, $resource)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
    }

    public function toArray($request)
    {
        $symbol = get_currency_symbol($this->property_info->currency_code);
        $guest_experience_setting = $this->checkRequiredStatusOfMetaInformation($this->id);
        $capablities = CapabilityService::allCapabilities($this->resource);

        $verification = false;
        if (CapabilityService::isAutoPaymentOrSecuritySupported($this->resource)) {
            $verification = ($guest_experience_setting['required_passport_scan'] || $guest_experience_setting['required_credit_card_scan']);
        }
        $card_info = ($capablities[CaCapability::AUTO_PAYMENTS] || $capablities[CaCapability::SECURITY_DEPOSIT]);

        $terms_and_conditions = array();
        $terms_and_conditions_found = false;
        $room_info_id = $this->room_info->id;
        if (!empty($room_info_id)) {
            $property_info_id = $this->property_info->id;
            $where = [
                ['user_account_id', $this->user_account_id],
                ['status', TermsAndCondition::STATUS_ACTIVE]
            ];
            if ($guest_experience_setting['tac']) {
                $terms_and_conditions = get_related_records(['text_content', 'internal_name', 'checkbox_text', 'required'], PropertyInfo::TAC, $where, $property_info_id, $room_info_id)->first();
                if (!empty($terms_and_conditions->text_content)) {
                    $terms_and_conditions_found = true;
                    $terms_and_conditions = convertTemplateVariablesToActualData(BookingInfo::class, $this->id, $terms_and_conditions->toArray());
                }
//                dd($terms_and_conditions);

            }

        }

        $arriving_by = $arrivaltime = '--';
        $flight_no = '';
        if (!empty($this->guest_data)) {
            if (!empty($this->guest_data->arriving_by)) {
                $arriving_by = $this->guest_data->arriving_by;
                if ($arriving_by == 'Plane' && !empty($this->guest_data->plane_number)) {
                    $flight_no = $this->guest_data->plane_number;
                }
                elseif ($arriving_by == 'Other' && !empty($this->guest_data->other_detail)){
                    $arriving_by = $this->guest_data->other_detail;
                }
            }
            if (!empty($this->guest_data->arrivaltime)) {
                $arrivaltime = $this->guest_data->arrivaltime;
            }
        }

        $cc_last_4_digit = '';
        $show_payment_method = true;
        if (!($this->cc_Infos->isEmpty())) {
            $cc_info = $this->cc_infos->where('is_vc', '0')->last();
            if (!empty($cc_info)) {
                $cc_last_4_digit = $cc_info->cc_last_4_digit;
                $show_payment_method = !empty($cc_last_4_digit);
            } else {
                $show_payment_method = false;
            }

        }

        return [
            'reference' => $this->pms_booking_id,
            'booking_type' => config('db_const.booking_info.is_vc.' . $this->is_vc),
            'pms_booking_Status' => array_search($this->pms_booking_status, config('db_const.booking_info.pms_booking_status')),
            'badge_color' => config('db_const.booking_info.pms_booking_status_badge_color.' . $this->pms_booking_status),
            'booking_time' => Carbon::parse($this->booking_time, $this->property_info->time_zone)->format('M j h:i a'),
            'check_in' => Carbon::parse($this->check_in_date, $this->property_info->time_zone)->format('M j, Y'),
            'check_out' => Carbon::parse($this->check_out_date, $this->property_info->time_zone)->format('M j, Y'),
            'amount' => $symbol . number_format($this->total_amount, 2),
            'manual_canceled' => $this->manual_canceled,
            'terms_and_conditions_accepted' => (!empty($this->terms_and_conditions_accepted) ? true : false),
            'cc_last_4_digit' => $cc_last_4_digit,
            'show_payment_method' => $show_payment_method,
            'arriving_by' => $arriving_by,
            'arrival_time' => $arrivaltime,
            'flight_no' => $flight_no,
            'guest_images' => $this->guest_images,
            'full_name' => $this->guest_name . ' ' . $this->guest_last_name,
            'email' => $this->guest_email,
            'phone' => $this->guest_phone,
            'adults' => !empty($this->guest_data) ? $this->guest_data->adults : '--',
            'childern' => !empty($this->guest_data) ? $this->guest_data->childern : '0',
            'links' => [
                'step_1' => URL::signedRoute('step_1', $this->booking_id),
                'step_2' => URL::signedRoute('step_2', $this->booking_id),
                'step_3' => URL::signedRoute('step_3', $this->booking_id),
                'step_4' => URL::signedRoute('step_4', $this->booking_id),
                'step_5' => URL::signedRoute('step_5', $this->booking_id),
                'cc_info' => URL::signedRoute('guest-cc-detail-fetch', $this->booking_id),
                'update_cc_info_on' => URL::signedRoute('guest-cc-detail-update'),
            ],
            'arrival_info' => $guest_experience_setting['required_arrival_info'],
            'verification' => $verification,
            'card_info' => $card_info,
            'contact_info' => $guest_experience_setting['required_basic_info'],
            'terms_and_conditions_found' => $terms_and_conditions_found,
            'terms_and_conditions' => $terms_and_conditions,
            'signature_pad' => $guest_experience_setting['signature_pad'],
            'signature_type' => GuestImage::TYPE_SIGNATURE,
            'booking_id' => $this->id,
            'digital_signature' => $this->getDigitalSignature($this->resource),
            'terms_link' => $terms_and_conditions_found ? Url::signedRoute('pre_checkin_terms_conditions', $this->booking_id) : '#',
        ];
    }

    public function with($request)
    {

        $property = $this->property_info;
        $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_7'), $this->id);

        $check_property_image = checkImageExists($property->logo, $property->name, config('db_const.logos_directory.property.value'));
        $property_logo = asset(config('db_const.logos_directory.property.img_path') . $check_property_image['property_image']);

        $check_booking_source_logo = checkImageExists($this->bookingSourceForm->logo, $this->bookingSourceForm->name, config('db_const.logos_directory.booking_source.value'));
        $booking_source_logo = asset(config('db_const.logos_directory.booking_source.img_path') . $check_booking_source_logo['booking_source_image']);

        return [
            'header' => [
                'property_name' => $property->name . ' #' . $property->pms_property_id,
                'property_logo' => $property_logo,
                'property_initial' => $check_property_image['property_initial'],
                'booking_source' => 'Booked with ' . $this->bookingSourceForm->name,
                'booking_source_logo' => $booking_source_logo,
                'booking_source_initial' => $check_booking_source_logo['booking_source_initial'],
                'external_link' => ''
            ],
            'meta' => $meta,
            'status' => true,
            'status_code' => 200,
        ];
    }
}
