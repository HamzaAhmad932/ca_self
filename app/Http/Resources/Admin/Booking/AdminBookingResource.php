<?php

namespace App\Http\Resources\Admin\Booking;

use App\CaCapability;
use App\TransactionInit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AdminBookingResource extends JsonResource
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
        try {
            //Day diff count && Stay days count
            $this->property_timezone = $this->property_info->time_zone;
            $check_in = Carbon::parse($this->check_in_date)->timezone($this->property_info->time_zone);
            $check_out = Carbon::parse($this->check_out_date)->timezone($this->property_info->time_zone);
            $s_deposit = $this->credit_card_authorization->whereIn('type', [19, 20])->first();
            $deposit = empty($s_deposit) ? 0 : $s_deposit;
            $this->symbol = get_currency_symbol($this->property_info->currency_code);
            $deposit_status = $deposit === 0 ? '' : $this->getDepositStatus($deposit);
            $capabilities = $this->getCapabilities();

            $amount_to_show = $this->transaction_init_charged->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->sum('price');
            $payment_status = $this->is_process_able
                ? ((!$capabilities[CaCapability::AUTO_PAYMENTS]
                    && !$capabilities[CaCapability::SECURITY_DEPOSIT])
                    ? $this->getOthersTypeBookingFlag()
                    : $this->checkPriorityFlagForTransactions()
                ) : $this->getOthersTypeBookingFlag();

            $booking_status = $this->getBookingStatus($this->pms_booking_status);
            $room_info = $this->getRoomInfo($this);
            $guest_data = $this->guestData($this);

            return [
                'id' => $this->id,
                'user' => [
                    //'user_id' => $this->user_id,
                    //'user_name' => $this->user->name,
                    'user_account_id' => $this->user_account_id,
                    'user_account_name' => $this->user_account->name
                ],
                'pms_booking_id' => $this->pms_booking_id,
                'booking_status' => $booking_status,
                'booking_date' => Carbon::parse($this->booking_time)->timezone($this->property_timezone)->format('d M @ h:i a'),
                'isvc' => $this->getBookingType($this->is_vc),
                'guest_email' => $this->guest_email,
                'guest_name' => $this->guest_name . ' ' . $this->guest_last_name,
                'guest_phone' => empty($this->guest_phone) ? '--' : $this->guest_phone,
                'arrival_time' => $guest_data->arrival_time,
                'guests' => $guest_data->guest_count,
                'last_seen_of_guest' => $this->last_seen_of_guest != null ? Carbon::parse($this->last_seen_of_guest)->timezone($this->property_timezone)->format('d M Y H:i') : null,
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
                'property' => [
                    'id' => $this->property_info->id,
                    'pms_property_id' => $this->property_info->pms_property_id,
                    'name' => $this->property_info->name,
                    'timezone' => $this->property_info->time_zone,
                ],
                'room' => $room_info,
                'amount' => $this->symbol . number_format($amount_to_show == 0 ? $this->total_amount : $amount_to_show, 2),
                'deposit' => $this->symbol . (empty($deposit->hold_amount) ? '0.00' : number_format($deposit->hold_amount, 2)),
                'deposit_status' => $deposit_status,
                'payment_status' => $payment_status,
            ];
        } catch (\Exception $e) {
            Log::error("BookingInfo Id => $this->id  --- BookingSourceForm => $this->bookingSourceForm -- Channel Code => $this->channel_code ");
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Stack' => $e->getTraceAsString()
            ]);
        }
        return [];
    }

}
