<?php

namespace App\Repositories\Admin\Bookings;

use App\Audit;
use App\BookingInfo;

use App\CreditCardInfo;
use App\PaymentGatewayForm;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\Services\CapabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function foo\func;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AdminBookingRepository implements AdminBookingRepositoryInterface
{

    public function __construct()
    {

    }

    public function get_bookings_list()
    {
        $bookings = BookingInfo::with([
                'credit_card_authorization'=>function($query){
                    $query->whereIn('type', [19, 20, 21, 22]);
                },
                'transaction_init'=>function($transaction_init){
                    $transaction_init->where('type','C');
                },
                'guest_images',
                'booking_source',
                'cc_Infos',
                //'room_info'
            ])->latest()->paginate(10);
        return $bookings;
    }

    public function get_booking_detail ($booking_info_id)
    {
        $booking = BookingInfo::with([
            'transaction_init',
            'transaction_init.transactions_detail',
            'transaction_init.transactions_detail.payment_gateway_form',
            'transaction_init.pmsForm',
            'user_account',
            'cc_infos',
            'cc_infos.creditCardInfoAudits',
            'credit_card_authorization',
            'credit_card_authorization.authorization_details'
        ])->where('id', $booking_info_id)
            ->first();

        if (empty($booking)) {
            return $booking;
        }

        $payment_gateways = new PaymentGateways();
        $booking_payment_gateway = $payment_gateways->getPropertyPaymentGatewayFromBooking($booking);
        $payment_gateway_form_name = PaymentGatewayForm::where('id', $booking_payment_gateway->payment_gateway_form_id)->select('name')->first();
        $booking_payment_gateway->payment_gateway_form_name = $payment_gateway_form_name->name;

        $booking_payment_gateway->payment_gateway_detail = Audit::where('auditable_type', 'App\UserPaymentGateway')
            ->where('auditable_id', $booking_payment_gateway->id)
            ->where('created_at', '>=', \Illuminate\Support\Carbon::parse($booking->created_at)->toDateTimeString())
            ->where('created_at', '<=', Carbon::parse($booking->check_out_date)->toDateTimeString())
            ->get();

        $booking->payment_gateway = $booking_payment_gateway;
        $booking->property_info;
        $booking->property_info->propertyInfoAudits;
        $booking->room_info;

        $booking->other_type_booking = CapabilityService::isAnyPaymentOrSecuritySupported($booking);

        return $booking;
    }

    public function get_admin_bookings_list_filtered($filter)
    {
        try{
            if ($filter['user_account_id'] != 0) {
                array_push($filter['constraints'], ["user_account_id", '=', $filter['user_account_id']]);
            }

            if(!empty($filter['date']) && $filter['date'] == 'today'){
                array_push($filter['constraints'], ['check_in_date', '>', Carbon::today()->addDay(-1)->startOfDay()]);
                array_push($filter['constraints'], ['check_in_date', '<=', Carbon::today()->addDay(1)->endOfDay()]);
            }
            if(!empty($filter['is_custom_date']) && $filter['is_custom_date'] == true){
                array_push($filter['constraints'], ['check_in_date', '>', Carbon::parse($filter['dateOne'])->addDay(-1)->startOfDay()]);
                array_push($filter['constraints'], ['check_in_date', '<', Carbon::parse($filter['dateTwo'])->addDay(1)->endOfDay()]);
            }

            $bookings = get_collection_by_applying_filters($filter, BookingInfo::class);
            return $bookings;
        } catch (\Exception $e){
            Log::debug('Booking List filter query exception', ['file'=> AdminBookingRepository::class, 'method'=> 'get_admin_bookings_list_filtered']);
            return [];
        }
    }


    public static function isCCInfoValidAndCustomerObjectCreated(CreditCardInfo $ccInfo = null) {
         return (!is_null($ccInfo) && ($ccInfo->customer_object != null) && ($ccInfo->customer_object->token != null )
             && !in_array($ccInfo->status, [3, 4, 5]));
    }
}
