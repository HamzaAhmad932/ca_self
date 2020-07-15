<?php


namespace App\Services;


use App\CreditCardInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Repositories\Bookings\Bookings;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\CreditCardInfoDetail;

trait CreditCardInfoService
{

    public function createCustomerObject(int $is_vc, $card, $booking_info, $booking, $user_account, $user_payment_gateway) {

        if(empty($user_payment_gateway)) {

            return [
                'status' => false,
                'error_message' => 'User payment gateway not found',
                'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'),
                'reason' => config('db_const.credit_card_infos.status.Gateway-Missing')
            ];
        }

        $customer = null;
        $successMessage = 'Successfully Created Customer Object.';
        $payment_gateway = new PaymentGateWay();

        try {
            if(!empty($card->cardNumber)){
                $customer = $payment_gateway->addAsCustomer($card, $user_payment_gateway);
            }else{
                return array('status' => false,
                    'error_message' => 'Missing Card Number.',
                    'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'),
                    'reason' => 1); // 1 means credit card missing reason
            }

            if($customer != null) {
                if($customer->succeeded) {
                    $cc_info = CreditCardInfo::create(
                        [
                            'booking_info_id' => $booking_info->id,
                            'user_account_id' => $user_account->id,
                            'card_name' => $card->firstName . ' ' . $card->lastName,
                            'f_name' => $customer->first_name,
                            'l_name' => $customer->last_name,
                            'cc_last_4_digit' => $customer->last_four_digits,
                            'cc_exp_month' => $customer->month,
                            'cc_exp_year' => $customer->year,
                            //'cc_cvc_num' =>$card->cvvCode,
                            'customer_object' => json_encode($customer),
                            'auth_token' => $customer->token,
                            'system_usage' => '',
                            'attempts' => 1,
                            'is_vc' => $is_vc,
                            'status' => config('db_const.credit_card_infos.status.Created'),
                            'error_message' => $successMessage,
                            'type'=> config('db_const.credit_card_infos.type.customer_obj_created')
                        ]
                    );

                    if ($cc_info) {
                        $booking->cardNumber = null;
                        $booking->cardExpire = null;
                        $booking->cardCvv = null;
                        $booking_info->full_response = json_encode($booking);
                        $booking_info->save();

                        return array('status' => true, 'cc_info_id' => $cc_info->id, 'customer' => $customer, 'cc_info' => $cc_info, 'error_message' => $successMessage);

                    } else {
                        return array('status' => false,
                            'error_message' => 'Credit Card Record not created automatically. ' . " :: cc_info :: " . json_encode($cc_info) . " :: Customer :: " . json_encode($customer), 'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'));
                    }

                } else {
                    Bookings::BA_reportInvalidCardForBDCChannel($booking_info);
                    return array('status' => false, 'error_message' => 'Customer Object not created: ' . $customer->message, 'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'));
                }

            } else {
                Bookings::BA_reportInvalidCardForBDCChannel($booking_info);
                return array('status' => false, 'error_message' => 'Customer Object came NULL from PaymentGateway Class', 'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'));
            }

        } catch(GatewayException $e) {
            if ($e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE){
                Bookings::BA_reportInvalidCardForBDCChannel($booking_info);
            }
            return array('status' => false, 'error_message' => $e->getDescription(), 'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'), 'reason' => 2);  // 2 means payment gateway error

        } catch (\Exception $e) {

            $timeKey = "key_" . time();

            Log::error($e->getMessage(), array(
                'File'=> __FILE__,
                'Stack' => $e->getTraceAsString(),
                'Key' => $timeKey,
                'Customer' => json_encode($customer)
            ));

            return array('status' => false, 'error_message' => $e->getMessage() . " Key to find Stack on Slack: " . $timeKey, 'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'));
        }

    }


    public function addCcInfoEntry(int $is_vc, array $meta_data, string $errorMessage = '', $status = null, $type = null) {

        $fName = $meta_data['booking']->getCardFirstName();
        $lName = $meta_data['booking']->getCardLastName();
        $type_to_insert = $type == null ? $meta_data['card']->type : $type;
        $due_date = Carbon::now()->addMinute(2)->toDateTimeString();

        if($is_vc == 1){

            $fName = $meta_data['card']->firstName;
            $lName = $meta_data['card']->lastName;
            
            if($status != config('db_const.credit_card_infos.status.Gateway-Missing'))
                $status = config('db_const.credit_card_infos.status.Scheduled');
            
            $due_date = $this->getVcDueDate($meta_data);
        }

        return CreditCardInfo::create([
            'booking_info_id' => $meta_data['booking_info']->id,
            'user_account_id' => $meta_data['user_account']->id,
            'card_name' => $fName . ' ' . $lName,
            'f_name' => $fName,
            'l_name' => $lName,
            'cc_last_4_digit' => $meta_data['card']->getLastFourDigits(),
            'cc_exp_month' => !empty($meta_data['card']->expiryMonth) ? $meta_data['card']->expiryMonth : '',
            'cc_exp_year' => !empty($meta_data['card']->expiryYear) ? $meta_data['card']->expiryYear : '',
            'customer_object' => '',
            'auth_token' => '',
            //'cc_cvc_num' =>'',
            'due_date' => $due_date,
            'system_usage' => Card::encrypt($meta_data['card']),
            'status' => $status == null ? config('db_const.credit_card_infos.status.In-Retry') : $status,
            'is_vc' => ($is_vc == 1 ? 1 : 0), //VC => 1 , BT|CC => 0
            'attempts' => ($is_vc == 0 ? 1 : 0), // IF CC Customer object failed attempt 1 else BT | VC => 0,
            'type'=> $type_to_insert,
            'error_message' => $errorMessage
        ]);
    }

    public function getVcDueDate(array $meta_data) {

        $dueDate = $meta_data['booking']->getDueDateFromGuestComments();
        $dueDate = Carbon::parse($dueDate)->toDateTimeString();

        $bookingDate = Carbon::parse($meta_data['booking_info']->booking_time)->toDateString();
        $checkInDate = Carbon::parse($meta_data['booking_info']->check_in_date)->toDateString();

        if ($bookingDate >= $checkInDate) {

            $dueDate = Carbon::parse($dueDate)->addMinute(10)->toDateTimeString();

        } else {

            $repoBookings = new Bookings($meta_data['user_account']->id);
            $dueDateAddedHours = $repoBookings->addCheckInHours($dueDate); /* add Hours To Check-in Datetime */
            $dueDate = $repoBookings->setVcDueDateWithTimeZone(
                $meta_data['user_account'],
                $meta_data['property_info'],
                $dueDateAddedHours);
        }
        return $dueDate;

    }
    
    public static function insert_CC_Info_Log($cc_info_id, $user_account_id, $message, $status, $response = null, $user_id = null) {
        
        try {
            
            $ccid = new CreditCardInfoDetail();
            $ccid->cc_info_id = $cc_info_id;
            $ccid->user_id = $user_id;
            $ccid->user_account_id = $user_account_id;
            $ccid->message = $message;
            $ccid->response = $response;
            $ccid->status = $status;
            $ccid->save();
            
        } catch (\Exception $ex) {
            
            Log::error($ex->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'cc_info_id' => $cc_info_id, 
                'user_account_id' => $user_account_id, 
                'message' => $message, 
                'status' => $status, 
                'response' => $response, 
                'user_id' => $user_id,
                'Stack' => $ex->getTraceAsString()
            ]);

        }
        
    }
}