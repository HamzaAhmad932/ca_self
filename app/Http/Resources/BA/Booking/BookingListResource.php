<?php

namespace App\Http\Resources\BA\Booking;

use App\CaCapability;
use App\CreditCardInfo;
use App\GuestImage;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Traits\Resources\General\Booking;
use Carbon\Carbon;
use App\TransactionInit;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Repositories\PaymentGateways\PaymentGateways;

class BookingListResource extends JsonResource
{
    protected $symbol;
    public static $bookingChannelsGuestEmailStatus = [];
    public static $bookingChannelsChatStatus = [];
    public static $bookingChannelsDocumentRequiredStatus = [];
    public static $bookingsChannelsCapabilities = [];
    public static $paymentTypeMetaAuto = [];
    public static $upsellTypes = [];

    use Booking;

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
            $current_day = Carbon::now($this->property_info->time_zone);
            $s_deposit = $this->credit_card_authorization->whereIn('type', [
                config('db_const.credit_card_authorizations.type.security_damage_deposit_auto_auth'),
                config('db_const.credit_card_authorizations.type.security_damage_deposit_manual_auth')
            ])->first();
            $deposit = empty($s_deposit) ? 0 : $s_deposit;
            $this->symbol = get_currency_symbol($this->property_info->currency_code);
            $deposit_status = $deposit === 0 ? '' : $this->getDepositStatus($deposit);
            $capabilities = $this->getCapabilities($this->resource);

            $total_amount = $this->transaction_init_charged
                ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->sum('price');

            $charged_amount = $this->transaction_init_charged
                ->whereIn('payment_status', [TransactionInit::PAYMENT_STATUS_SUCCESS, TransactionInit::PAYMENT_MARKED_AS_PAID])
                ->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->sum('price');

            $balance = $total_amount - $charged_amount;

            $amount_to_show = $this->transaction_init_charged->where('type', TransactionInit::TRANSACTION_TYPE_CHARGE)->sum('price');
            $payment_status = $this->is_process_able
                ? ((!$capabilities[CaCapability::AUTO_PAYMENTS]
                    //&& !$capabilities[CaCapability::MANUAL_PAYMENTS]
                    && !$capabilities[CaCapability::SECURITY_DEPOSIT])
                    ? $this->getOthersTypeBookingFlag($this->resource)
                    : $this->checkPriorityFlagForTransactions($this->resource)
                ) : $this->getOthersTypeBookingFlag($this->resource);

            $processable = $this->is_process_able;
            $stay_nights = self::calculateStayNights($this->full_response, $this->property_info->time_zone);
            //$guestIdStatus = $this->checkPriorityForGuestImagesStatus($this->guest_images); DEPRECATED
            $preCheckinStatus = $this->guestPreCheckinStatus($this);
            $booking_status = $this->getBookingStatus($this->pms_booking_status);
            $room_info = $this->getRoomInfo($this);

            $guest_experience_status = $capabilities[CaCapability::GUEST_EXPERIENCE] && $this->isGuestExperienceEmailActive();
            $chat_status = $guest_experience_status ? $this->isGuestExperienceChatActive() : false;
            $document_required_status = $guest_experience_status ? $this->isGuestExperienceDocumentsActive() : false;

            return [
                'id' => $this->id,
                'pms_booking_id' => $this->pms_booking_id,
                'booking_status_id' => $this->pms_booking_status,
                'booking_status' => $booking_status,
                'processable' => $processable,
                'guest_email' => $this->guest_email,
                'guest_name' => $this->guest_name . ' ' . $this->guest_last_name,
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
                'left_days' => $check_in->diffForHumans($current_day),
                'stay_days' => $stay_nights . ' nights',
                'amount' => $this->symbol . number_format($amount_to_show == 0 ? $this->total_amount : $amount_to_show, 2),
                'balance' => $this->symbol . number_format($balance, 2),
                'deposit' => $this->symbol . (empty($deposit->hold_amount) ? '0.00' : number_format($deposit->hold_amount, 2)),
                'deposit_status' => $deposit_status,
                'payment_status' => $payment_status,
                'guest_identity' => $preCheckinStatus,
                'logo' => $this->bookingSourceForm['logo'],
                'booking_source' => $this->bookingSourceForm['name'],
                'check_in_pdf' => $check_in->toDateString(),
                'check_out_pdf' => $check_out->toDateString(),
                'guest_experience' => $guest_experience_status,
                'chat_active' => $chat_status,
                'documents_required' => $document_required_status,
                'total_documents'=>GuestImage::where('booking_id',$this->id)->count(),
                'total_cc_added' =>$this->cc_Infos->where('status',1)->count(),
                'is_payment_gateway_found'=> $this->getPaymentGateway(),
                'capabilities' => $capabilities,
                'upsell_orders'=> $this->upsellOrders(),

                'general_settings_url' => route('v2generalSettings'),
                'upsell_general_setting_status' => !empty($this->clientGeneralPreferencesInstance->isActiveStatus(
                    config('db_const.general_preferences_form.upsell'), $this->bookingSourceForm
                )),


                 //'stay_days'=> $check_in->diffInDays($check_out). ' nights',
                 //number_format((float)$number, 2, '.', '')
                //'show_pre_paid'=> $this->transaction_init_charged->where('payment_status', 1)->sum('price') != 0 ? true : false,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
               'File' => __FILE__,
               'BookingInfo Id' => $this->id,
               'BookingSourceForm' => $this->bookingSourceForm,
               'Channel Code' => $this->channel_code,
               'Stack' => $e->getTraceAsString()
            ]);
            
        }
        return [];
    }
}
