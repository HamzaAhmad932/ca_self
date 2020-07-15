<?php

namespace App\Http\Resources\BA\Booking;

use App\Traits\Resources\General\Booking;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class BookingListDetailResource extends JsonResource
{
    use Booking;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->symbol = get_currency_symbol($this->property_info->currency_code);
        $this->property_timezone = $this->property_info->time_zone;
        $payment_status = $this->getPaymentStatus($this);
        $processable = $this->is_process_able;
        $payment_summary = $this->getPaymentSummary($this->transaction_init);
        $guest_data = $this->guestData($this);

        $activity_log = $this->getPaymentActivityLog();

        return [
            'booking_detail' => [
                'id' => $this->id,
                'booking_date' => Carbon::parse($this->booking_time)->timezone($this->property_timezone)->format('d M h:i a'),
                'guest_phone' => empty($this->guest_phone) ? '--' : $this->guest_phone,
                'guest_email' => $this->guest_email,
                'channel_ref' => $this->pms_booking_id,
                'isvc' => config('db_const.booking_info.is_vc.' . $this->is_vc),
                'arrival_time' => $guest_data->arrival_time,
                'guests' => $guest_data->guest_count,
                'payment_status' => $payment_status,
                'processable' => $processable,
                'booking_status' => $this->pms_booking_status,
                'booking_exp' => now() > $this->check_out_date ? 0 : 1,
                'payment_summary' => $payment_summary,
                'activity_log' => $activity_log,
                'routes' => [
                    'pre_checkin' => URL::signedRoute('step_0', $this->id),
                    'guest_portal' => URL::signedRoute('guest_portal', $this->id),
                    //'pre_checkin_1'=> URL::signedRoute('guest_booking_details', ['id' => $this->id, 'visiting' => 'client']),
                    //'guest_portal_1'=> URL::signedRoute('guest_booking_details', ['id' => $this->id])
                ],
                'last_seen_of_guest' => $this->last_seen_of_guest != null ? Carbon::parse($this->last_seen_of_guest)->timezone($this->property_timezone)->format('d M Y H:i') : null,
                'total_charged' => $this->getAllChargedAmountSum($this),
                'total_refunded' => $this->getAllRefundedAmountSum($this),
                'total_marked_as_paid' => $this->getAllMarkedAsPaidAmountSum($this),
                'total_captured_amount' => $this->symbol . $this->getTotalCapturedAmount($this),
                'total_captured_refund_amount' => $this->symbol . $this->getTotalCapturedRefundAmount($this),
                'capabilities' => $this->capabilities,
                'pre_checkin_completed' => filter_var($this->pre_checkin_status, FILTER_VALIDATE_BOOLEAN),
                'guest_experience' => $this->guest_experience,
                'booking_list' => new BookingListCollection($this->booking_list),
                'is_payment_gateway_found' => $this->getPaymentGateway(),
                'is_credit_card_available' => !empty($this->cc_Infos->last())
            ],
        ];
    }
}
