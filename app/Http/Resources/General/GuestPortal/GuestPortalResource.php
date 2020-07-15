<?php


namespace App\Http\Resources\General\GuestPortal;


use App\BookingInfo;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Services\CapabilityService;
use App\Traits\Resources\General\Precheckin;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestPortalResource extends JsonResource
{
    use Precheckin;
    public $booking_id;

    public function __construct($booking_id, $resource)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
    }

    public function toArray($request)
    {
        //trait function calling
        $property = $this->property_info;
        $this->timezone = $property->time_zone;
        $this->symbol = get_currency_symbol($property->currency_code);
        $guest_data = $this->guest_data;
        $map = $this->getMapStatus($property);
        $payments = $this->getPayments($this->transaction_init);
        $deposits = $this->getDeposits($this->credit_card_authorization);
        $booking_status = $this->getBookingStatus($this->pms_booking_status);
        $card_info = $this->getCardInfo($this->resource);
        $meta = $this->checkMetaInformation($this->resource);
        $auth_info = $this->getAuthInformation($this->resource);
        $check_property_image = checkImageExists($property->logo, $property->name, config('db_const.logos_directory.property.value'));
        $property_logo = asset(config('db_const.logos_directory.property.img_path') . $check_property_image['property_image']);

        $check_booking_source_logo = checkImageExists($this->bookingSourceForm->logo, $this->bookingSourceForm->name, config('db_const.logos_directory.booking_source.value'));
        $booking_source_logo = asset(config('db_const.logos_directory.booking_source.img_path') . $check_booking_source_logo['booking_source_image']);

        $upg = new PaymentGateways();
        $user_payment_gateway = $upg->getPropertyPaymentGatewayFromBooking($this->resource);
        $non_transformed_docs = clone $this->guest_images;
        $this->guestDocumentTransform($this->guest_images);

        $guideBooks = array();
        if (!empty($this->guide_book_types)) {
            $data = $this->guide_book_types->toArray();
            $guideBooks = convertTemplateVariablesToActualData(BookingInfo::class, $this->booking_id, $data);
        }
        $arrivaltime = $arriving_by = '--';
        $flight_no = '';
        if (!empty($guest_data)) {
            if (!empty($guest_data->arrivaltime)) {
                $arrivaltime = $guest_data->arrivaltime;
            }

            if (!empty($guest_data->arriving_by)) {
                $arriving_by = $guest_data->arriving_by;
                if ($arriving_by == 'Plane' && !empty($guest_data->plane_number)) {
                    $flight_no = $guest_data->plane_number;
                }
            }
        }

        return [
            'header' => [
                'property_name' => $property->name . ' #' . $property->pms_property_id,
                'property_logo' => $property_logo,
                'property_initial' => $check_property_image['property_initial'],
                'booking_source' => 'Booked through ' . $this->bookingSourceForm->name,
                'booking_source_logo' => $booking_source_logo,
                'booking_source_initial' => $check_booking_source_logo['booking_source_initial'],
                'external_link' => ''
            ],
            'address_1' => $property->address,
            'address_2' => $property->city . ' ' . $property->country,
            'booking_status' => $booking_status,
            'pms_booking_id' => $this->pms_booking_id,
            'card_info' => $card_info,
            'check_in' => Carbon::parse($this->check_in_date)->timezone($property->time_zone)->format('M j, Y'),
            'check_out' => Carbon::parse($this->check_out_date)->timezone($property->time_zone)->format('M j, Y'),
            'email' => $this->guest_email,
            'phone' => $this->guest_phone,
            'arriving_by' => $arriving_by,
            'arrival_time' => $arrivaltime,
            'flight_no' => $flight_no,
            'guest_name' => $this->guest_name,
            'booking_dates' => Carbon::parse($this->check_in_date)->timezone($property->time_zone)->format('M j')
                . '-' .
                Carbon::parse($this->check_out_date)->timezone($property->time_zone)->format('M j, Y'),
            'show_map' => $map->status,
            'map_query' => $map->query,
            'payments' => $payments,
            'deposits' => $deposits,
            'images' => $this->guest_images,
            'is_auto_payment_or_security_supported' => CapabilityService::isAnyPaymentOrSecuritySupported($this->resource),
            'is_security_deposit_supported' => CapabilityService::isSecuritySupported($this->resource),
            'auth_info' => $auth_info,
            'meta' => $meta,
            'guide_book_types' => $guideBooks,
            'upsells' => $this->booking_upsells,
            'is_pg_active' => !empty($user_payment_gateway),
            'guest_images_status' => $this->guestImagesStatus($non_transformed_docs)
        ];
    }
}
