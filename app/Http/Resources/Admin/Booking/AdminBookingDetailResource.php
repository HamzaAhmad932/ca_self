<?php

namespace App\Http\Resources\Admin\Booking;

use App\CaCapability;
use App\Http\Resources\Admin\Transaction\BookingTransactionDetailResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

;

class AdminBookingDetailResource extends JsonResource
{
    protected $symbol;
    public static $bookingsChannelsCapabilities = [];
    public static $paymentTypeMetaAuto = [];

    use AdminBooking;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!empty($this->resource)) {
            return $this->successCase($this);
        } else {
            return $this->failureCase();
        }
    }

    public function successCase($bookingInfo)
    {
        $this->property_timezone = $bookingInfo->property_info->time_zone;
        $this->symbol = get_currency_symbol($bookingInfo->property_info->currency_code);
        $capabilities = $this->getCapabilities();
        $check_in = Carbon::parse($bookingInfo->check_in_date)->timezone($bookingInfo->property_info->time_zone);
        $check_out = Carbon::parse($bookingInfo->check_out_date)->timezone($bookingInfo->property_info->time_zone);
        $security_deposit = $bookingInfo->credit_card_authorization->whereIn('type', [19, 20])->first();
        $deposit = empty($security_deposit) ? 0 : $security_deposit;
        $deposit_status = $deposit === 0 ? '' : $this->getDepositStatus($deposit);

        $payment_status = $bookingInfo->is_process_able
            ? ((!$capabilities[CaCapability::AUTO_PAYMENTS]
                && !$capabilities[CaCapability::SECURITY_DEPOSIT])
                ? $this->getOthersTypeBookingFlag()
                : $this->checkPriorityFlagForTransactions()
            ) : $this->getOthersTypeBookingFlag();

        $booking_status = $this->getBookingStatus($bookingInfo->pms_booking_status);
        $payment_type = $this->getPaymentType();
        $guest_data = $this->guestData($bookingInfo);

        return [
            'check_in' => [
                'month' => strtoupper($check_in->shortEnglishMonth),
                'day' => $check_in->day,
                'year' => $check_in->year
            ],
            'check_out' => [
                'month' => strtoupper($check_out->shortEnglishMonth),
                'day' => $check_out->day,
                'year' => $check_out->year
            ],
            'room' => [
                'id' => $bookingInfo->room_info->id,
                'pms_room_id' => $bookingInfo->room_info->pms_room_id,
                'name' => $bookingInfo->room_info->name
            ],
            'deposit' => $this->symbol . (empty($deposit->hold_amount) ? '0.00' : number_format($deposit->hold_amount, 2)),
            'deposit_status' => $deposit_status,
            'amount' => $this->symbol . number_format($bookingInfo->total_amount, 2),
            'payment_status' => $payment_status,
            'booking_status' => $booking_status,
            'arrival_time' => $guest_data->arrival_time,
            'guests' => $guest_data->guest_count,
            'last_seen_of_guest' => $bookingInfo->last_seen_of_guest != null ? Carbon::parse($bookingInfo->last_seen_of_guest)->timezone($bookingInfo->property_timezone)->format('d M Y H:i') : 'Not Visited',
            'payment_type' => $payment_type,
            'id' => $bookingInfo->id,
            'pms_booking_id' => $bookingInfo->pms_booking_id,
            'user_account_id' => $bookingInfo->user_account_id,
            'property_id' => $bookingInfo->property_id,
            'booking_time' => $bookingInfo->booking_time,
            'guest_name' => $bookingInfo->guest_name . ' ' . $bookingInfo->guest_last_name,
            'guest_phone' => $bookingInfo->guest_phone != '' ? $bookingInfo->guest_phone : '--',
            'guest_email' => $bookingInfo->guest_email,
            'user_account' => $bookingInfo->user_account,
            'cc_infos' => $this->getCreditCardInfoDetails(),
            'credit_card_authorization' => $this->getCreditCardAuthorizationDetails(),
            'transaction_init' => BookingTransactionDetailResource::collection($bookingInfo->transaction_init),
            'property_info' => $this->getPropertyDetails(),
            'payment_gateway' => $bookingInfo->payment_gateway
        ];
    }

    public function failureCase()
    {
        return [
            'status' => false,
            'message' => "Data not found"
        ];
    }
}
