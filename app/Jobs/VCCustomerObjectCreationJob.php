<?php

namespace App\Jobs;

use App\BookingInfo;
use App\CreditCardInfo;
use App\Events\Emails\EmailEvent;
use App\Mail\GenericEmail;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_Expedia;
use App\TransactionInit;
use App\UserAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VCCustomerObjectCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('ba_charge');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccInfos = CreditCardInfo::whereIn('status',
            [config('db_const.credit_card_infos.status.Scheduled'), config('db_const.credit_card_infos.status.In-Retry')])
            ->where('is_vc', 1)
            ->where('due_date', '<=', Carbon::now()->toDateTimeString())
            ->where('attempts', '<=', CreditCardInfo::TOTAL_ATTEMPTS)
            ->get();

        /**
         * @var $ccInfo CreditCardInfo
         */
        foreach ($ccInfos as $ccInfo) {

            try {

                if(empty($ccInfo->system_usage))
                    continue;

                $status = 0;
                if($ccInfo->attempts == CreditCardInfo::TOTAL_ATTEMPTS) {
                    $status = config('db_const.credit_card_infos.status.Failed');
                    $this->informDevelopers($ccInfo);
                }
                else
                    $status = config('db_const.credit_card_infos.status.In-Retry');

                $card = Card::decrypt($ccInfo->system_usage);

                if (!($card instanceof Card)) {
                    $ccInfo->update([
                        'attempts' => $ccInfo->attempts + 1,
                        'status' => $status,
                        'error_message' => $ccInfo->error_message . "\n" . $card,
                        'type'=> config('db_const.credit_card_infos.type.empty')
                        ]);
                    continue;
                }

                if(empty($card->cardNumber) || empty($card->expiryMonth) || $card->expiryMonth == '00' || empty($card->expiryYear) || $card->expiryYear == '0000') {
                    $ccInfo->update([
                        'attempts' => $ccInfo->attempts + 1,
                        'status' => config('db_const.credit_card_infos.status.Failed'),
                        'error_message' => $ccInfo->error_message . "\nMissing card number of expiry month year.\n",
                        'type'=> config('db_const.credit_card_infos.type.missing_card_number')
                    ]);
                    continue;
                }

                $card->adjust_first_last_name_if_empty_any();

                /**
                 * @var $bookingInfo BookingInfo
                 */
                $bookingInfo = BookingInfo::where('id', $ccInfo->booking_info_id)->first();
                $userAccount = UserAccount::where('id', $ccInfo->user_account_id)->first();
                $propertyInfo = $bookingInfo->property_info;

                $usingPaymentGateway = ($propertyInfo->use_pg_settings == 1 ? $propertyInfo->id : 0);
                $userPaymentGateway = $userAccount->user_payment_gateways->where('property_info_id', $usingPaymentGateway)->first();

                /**
                 * @var $customer Customer
                 */
                $customer = null;

                try {

                    if($userPaymentGateway->payment_gateway_form->name == 'Stripe Keys' || $userPaymentGateway->payment_gateway_form->name == 'Stripe')
                        switch ($ccInfo->attempts) {
                            case 0:
                                if($bookingInfo->channel_code == BS_Expedia::BA_CHANNEL_CODE) {
                                    $card->cvvCode = '';
                                } else {
                                    $card->cvvCode = 0;
                                }
                                break;
                            case 1:
                                $card->cvvCode = 0;
                                break;
                            case 2:
                                if($bookingInfo->channel_code == BS_Expedia::BA_CHANNEL_CODE) {
                                    $card->cvvCode = 469;
                                }
                                break;
                            case 3:
                                if($bookingInfo->channel_code == BS_Expedia::BA_CHANNEL_CODE) {
                                    $card->cvvCode = '';
                                }

                        }

                    PaymentGateways::addMetadataInformation($bookingInfo, $card, VCCustomerObjectCreationJob::class);

                    $paymentGateway = new PaymentGateway();
                    $customer = $paymentGateway->addAsCustomer($card, $userPaymentGateway);

                    if($customer != null) {
                        if ($customer->succeeded) {

                            $ccInfo->update([
                                'customer_object' => json_encode($customer),
                                'auth_token' => $customer->token,
                                'status' => config('db_const.credit_card_infos.status.Created'),
                                'system_usage' => '',
                                'attempts' => $ccInfo->attempts + 1,
                                'error_message' => "Successfully Created Customer Object.\nBy " . VCCustomerObjectCreationJob::class,
                                'type'=> config('db_const.credit_card_infos.type.customer_obj_created')
                            ]);

                            $transactions = TransactionInit::where('final_tick', 0)
                                ->where('lets_process', 0)
                                ->where('type', 'C')
                                ->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
                                ->where('booking_info_id', $ccInfo->booking_info_id)
                                ->get();

                            foreach ($transactions as $transaction) {
                                $transaction->lets_process = 1;
                                $transaction->save();
                            }
                        } else {

                            $ccInfo->update([
                                'attempts' => $ccInfo->attempts + 1,
                                'status' => $status,
                                'error_message' => $ccInfo->error_message . "\n" . $customer->message,
                                'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
                                ]);

                            $this->send_decline_email($ccInfo, $userAccount, $bookingInfo, 'Creating customer object (Saving Customer on Gateway) failed.');
                        }

                    } else {

                        $minutes = 5;
                        $minutes = $minutes * ($ccInfo->attempts == 0 ? 1 : ($ccInfo->attempts + 1));
                        
                        $ccInfo->update([
                            'attempts' => $ccInfo->attempts + 1,
                            'status' => $status,
                            'error_message' => $ccInfo->error_message . "\nPayment Gateway gave null object",
                            'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created'),
                            'due_date' => Carbon::now()->addMinutes($minutes)
                            ]);

                    }

                } catch (GatewayException $e) {

                    $ccInfo->update([
                        'attempts' => $ccInfo->attempts + 1,
                        'status' => $status,
                        'error_message' => $ccInfo->error_message . "\n" . $e->getDescription(),
                        'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
                        ]);

                    Log::error($e->getDescription(), ['File' => VCCustomerObjectCreationJob::class]);

                    $this->send_decline_email($ccInfo, $userAccount, $bookingInfo, $e->getDescription());
                }



            } catch (\Exception $e) {

                $ccInfo->update([
                    'attempts' => $ccInfo->attempts + 1,
                    'status' => $status,
                    'error_message' => $ccInfo->error_message . "\n" . $e->getMessage(),
                    'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
                    ]);

                Log::error($e->getMessage(), ['File' => VCCustomerObjectCreationJob::class, 'Stack' => $e->getTraceAsString()]);

            }

        }
    }

    private function send_decline_email(CreditCardInfo $creditCardInfo, UserAccount $userAccount, BookingInfo $bookingInfo, $reason) {
        try {

            $creditCardInfo->refresh();

            if($creditCardInfo->decline_email_sent == 0 && $creditCardInfo->attempts >= CreditCardInfo::TOTAL_ATTEMPTS) {

                $tran = $bookingInfo->transaction_init->first();

                if($tran != null)
                event(new EmailEvent(config('db_const.emails.heads.payment_failed.type'),$tran->id, [ 'reason' => $reason ] ));

                $creditCardInfo->update(['decline_email_sent' => 1]);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'UserAccountId' => $userAccount->id,
                'PmsBookingId' => $bookingInfo->pms_booking_id
            ]);
        }
    }

    private function informDevelopers(CreditCardInfo $creditCardInfo){
        try {

            Mail::to(config('db_const.app_developers.emails.to'))
                ->cc(config('db_const.app_developers.emails.cc'))
                ->send(
                    new GenericEmail(array(
                        'subject' => 'VC Customer Object not Created after Specific Attempts.',
                        'markdown' => 'emails.network_error_email_markdown',
                        'noReply' => true,
                        'message'=> 'VC Customer Object not Created after Specific Attempts, File => '. VCCustomerObjectCreationJob::class,
                        'any_json_object' => json_encode($creditCardInfo, JSON_PRETTY_PRINT)
                    ))
                );

        } catch(\Exception $e) {
            Log::error("Message: " . $e->getMessage(), [
                'File' => VCCustomerObjectCreationJob::class,
                'stack' => $e->getTraceAsString()
            ]);
        }
    }
}
