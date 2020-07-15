<?php


namespace App\Services;


use App\Events\Emails\EmailEvent;
use App\Repositories\NotificationAlerts;
use App\UpsellCart;
use App\UpsellOrder;
use App\Upsell;
use App\CreditCardInfo;
use App\UpsellOrderDetail;
use App\Events\SendEmailEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Repositories\Bookings\Bookings;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\Repositories\Upsells\UpsellRepository;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\Repositories\PaymentGateways\PaymentGateways as PaymentGatewayRepo;

trait UpsellService
{
    /**
     * @param int $booking_info_id
     * @param $cc_info
     * @return array
     * @throws \Exception
     */
    public function chargeUpsellPayment(int $booking_info_id, $cc_info){

        try{
            $upsellRepository = new UpsellRepository();
            $upsell_order = $upsellRepository->getUpsellOrdersAndCart($booking_info_id);

            if(!empty($upsell_order)){
                return $this->chargeUpsellsWithCustomer($cc_info, $upsell_order);
            }

        }catch (\Exception $e){

            log_exception_by_exception_object($e, null, 'error');
            throw $e;
        }
    }

    public function saveOrderEntries($payment_transaction, $booking_info, $upsells, $cc_info, $platform_fee){

        $order = UpsellOrder::create([
            'booking_info_id'=> $booking_info->id,
            'cc_info_id'=> $cc_info->id,
            'user_account_id'=> $booking_info->user_account_id,
            'user_id'=> Auth::check() ? $booking_info->user_id : 0,
            'final_amount'=> $payment_transaction->amount,
            'status'=> $payment_transaction->status,
            'commission_fee'=> $platform_fee,
            'charge_ref_no'=> $payment_transaction->token,
            'last_success_trans_obj'=> json_encode($payment_transaction)
        ]);

        $order_details = [];

        try{
            foreach($upsells as $upsell){

                $order_detail = [
                    'upsell_order_id'=> $order->id,
                    'upsell_id'=> $upsell->id,
                    'upsell_price_settings_copy'=> json_encode($upsell),
                    'amount'=> $upsell->total_price,
                    'persons'=> $upsell->guest_count,
                    'created_at'=> now()->toDateTimeString(),
                    'updated_at'=> now()->toDateTimeString()
                ];

                array_push($order_details, $order_detail);
            }

            UpsellOrderDetail::insert($order_details);

            //Empty cart

            UpsellCart::where('booking_info_id', $booking_info->id)->delete();

            return $order;
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
        }
    }

    /**
     * @param int $booking_id
     * @param array $upsell_ids
     * @return array
     * @throws GatewayException
     * @throws \Exception
     */
    public function purchaseUpsellByUpsellID(int $booking_id, array $upsell_ids){

        try {

            $upsellRepository = new UpsellRepository();
            $upsell_order = $upsellRepository->getUpsellByUpsellID($booking_id, $upsell_ids);

            $ccInfo = resolve(CreditCardInfo::class)
                ->where([['booking_info_id', $booking_id], ['is_vc', 0]])->latest('id')->limit(1)->first();

            if(!empty($upsell_order)){
                return $this->chargeUpsellsWithCustomer($ccInfo, $upsell_order);
            }
        }
        catch (GatewayException $e){
            throw $e;
        }
        catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            throw $e;
        }
    }

    /**
     * @param $cc_info
     * @param $upsell_order
     * @return array
     * @throws GatewayException
     */
    public function chargeUpsellsWithCustomer($cc_info, $upsell_order){


        $upg = new PaymentGatewayRepo();
        $pg = new PaymentGateway();

        $amount_due = abs($upsell_order['amount_due']);
        $booking_info = $upsell_order['booking_info'];

        $b = new Bookings($booking_info->user_account_id);
        $currency_code = $booking_info->property_info->currency_code;

        $commission = Config::get('db_const.upsell_listing.upsell_commission');
        $platform_fee = ($commission/100) * $amount_due;

        $card = new Card();
        $card->firstName = $cc_info->customer_object->first_name;
        $card->lastName = $cc_info->customer_object->last_name;
        $card->token = $cc_info->customer_object->token;
        $card->amount = $amount_due;
        $card->applicationFee = $platform_fee;
        $card->currency = $currency_code;
        $card->order_id = round(microtime(true) * 1000);

        $userPaymentGateway = $upg->getPropertyPaymentGatewayFromProperty($booking_info->property_info);

        PaymentGatewayRepo::addMetadataInformation($booking_info, $card, __CLASS__);
       
        
        try {
            
            $upsellDescription = '';
            
            foreach($upsell_order['upsell'] as $upSell) {

                $meta = $upSell->meta;
                $fromTime = $meta->from_time . ' ' . $meta->from_am_pm;
                $toTime = $meta->to_time . ' ' . $meta->to_am_pm;
                $name = $upSell->internal_name;
                $type = $upSell->upsellType->title;
                
                $upsellDescription .= "Your $type $fromTime - $toTime. ";
                
            }
            
            $message = !empty($booking_info->guest_title) ? $booking_info->guest_title . ' ' : '';
            $message .= !empty($booking_info->guest_name) ? $booking_info->guest_name . ' ' : '';
            $message .= !empty($booking_info->guest_last_name) ? $booking_info->guest_last_name . ' ' : '';
            $message .= 'for booking ' . $booking_info->pms_booking_id . ' purchased following upsell(s): ';
            $message .= $upsellDescription;
            
            $card->general_description = $message;
            
        } catch (\Exception $ex) {
            
            Log::error($ex->getMessage(), [
                'Function' => __FUNCTION__,
                'File' => __FILE__,
                'Stack' => $ex->getTraceAsString()
            ]);
            
            $card->general_description = 'Add on services charge of '.$currency_code.$amount_due.' on Booking ID: '.$booking_info->pms_booking_id;
        }

        $isCard3DS = false;
        $trans = new Transaction();

        try {
            
            $trans = $pg->chargeWithCustomer($cc_info->customer_object, $card, $userPaymentGateway);

            if($trans->status){
                $order = $this->saveOrderEntries($trans, $booking_info, $upsell_order['upsell'], $cc_info, $platform_fee);

                $notificationRepo = new NotificationAlerts($booking_info->user_id, $booking_info->user_account_id);
                $notificationRepo->create($booking_info->id, 1, config("db_const.notifications.alert_type.upsell_purchased"), $booking_info->pms_booking_id, 1);

                //send email to client
                event(new EmailEvent(config('db_const.emails.heads.upsell_purchased.type'), $order->id ));

            }


            return [
                'status'=> true,
                'status_code'=> 200
            ];
        }catch (GatewayException $e) {
            report($e);
            throw $e;
        }
    }
}