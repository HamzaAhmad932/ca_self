<?php

namespace App\Jobs;

use App\BookingInfo;
use App\CreditCardInfo;
use App\Mail\GenericEmail;
use App\PropertyInfo;
use App\Repositories\Bookings\Bookings;
use App\Repositories\PaymentGateways\PaymentGateways;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\Models\Booking;
use App\TransactionInit;
use App\UserPaymentGateway;
use http\Encoding\Stream\Inflate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReTryForCustomerObject implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ReTryForCustomerObject constructor.
     */
    public function __construct() {
        self::onQueue('reattempt');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        $ccInfos = CreditCardInfo::where('status', config('db_const.credit_card_infos.status.In-Retry'))
            ->where('is_vc', 0)
            ->where('due_date', '<=', Carbon::now()->toDateTimeString())
            ->where('attempts', '<=', CreditCardInfo::TOTAL_ATTEMPTS)
            ->get();

        $ccForException = null;
        foreach ($ccInfos as $creditCardInfo) {

            $ccForException = $creditCardInfo;

            try {

                /**
                 * If card is null then there is no point in reTrying!!
                 */
                if ($creditCardInfo->system_usage == null) {
                    $creditCardInfo->update(['attempts' => CreditCardInfo::TOTAL_ATTEMPTS + 1]);
                    $ccForException = null;
                    continue;
                }

                $bookingInfo = BookingInfo::where('id', $creditCardInfo->booking_info_id)->first();

                if ($bookingInfo != null) {

                    $data = json_decode($bookingInfo->full_response, true);
                    $booking = new Booking();

                    foreach ($data as $key => $value)
                        $booking->{$key} = $value;

                    $card = Card::decrypt($creditCardInfo->system_usage);

                    if (!($card instanceof Card)) {
                        $creditCardInfo->update([
                            'attempts' => CreditCardInfo::TOTAL_ATTEMPTS + 1,
                            'status' => config('db_const.credit_card_infos.status.Failed'),
                            'error_message' => $creditCardInfo->error_message . "\n" . $card,
                            'type'=> config('db_const.credit_card_infos.type.empty')
                            ]);
                        $ccForException = null;
                        continue;
                    }
                    
                    if(empty($card->cardNumber)) {
                        $creditCardInfo->update([
                            'attempts' => CreditCardInfo::TOTAL_ATTEMPTS + 1,
                            'status' => config('db_const.credit_card_infos.status.Failed'),
                            'error_message' => $creditCardInfo->error_message . "\nMissing Card Number."]);
                        $ccForException = null;
                        continue;
                    }

                    $card->adjust_first_last_name_if_empty_any();


                    $propertyInfo = PropertyInfo::where('pms_property_id', $bookingInfo->property_id)
                        ->where('pms_id', $bookingInfo->pms_id)
                        ->first();

                    if($propertyInfo == null) {
                        $creditCardInfo->update([
                            'attempts' => CreditCardInfo::TOTAL_ATTEMPTS + 1,
                            'error_message' => $creditCardInfo->error_message . "\nCould not find propertyInfo for currency code",
                            'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
                        ]);
                        $ccForException = null;
                        continue;
                    }

                    $card->firstName = $booking->getCardFirstName();
                    $card->lastName = $booking->getCardLastName();
                    $card->currency = $propertyInfo->currency_code;
                    $card->eMail = $booking->guestEmail;

                    PaymentGateways::addMetadataInformation($bookingInfo, $card, ReTryForCustomerObject::class);

                    $usingPaymentGateway = ($propertyInfo->use_pg_settings == 1 ? $propertyInfo->id : 0);

                    $userPaymentGateway = UserPaymentGateway::where('property_info_id', $usingPaymentGateway)
                        ->where('user_account_id', $bookingInfo->user_account_id)
                        ->first();

                    if($userPaymentGateway == null) {
                        $creditCardInfo->update([
                            'attempts' => CreditCardInfo::TOTAL_ATTEMPTS + 1,
                            'error_message' => $creditCardInfo->error_message . "\nCould not find User Payment Gateway",
                            'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
                            ]);
                        $ccForException = null;
                        continue;
                    }

                    if($userPaymentGateway->payment_gateway_form->name == 'Stripe')
                        switch ($creditCardInfo->attempts) {
                            case 1:
                            case 2:
                                $card->cvvCode = 0;
                                break;
                        }

                    try {

                        $pg = new PaymentGateway();
                        $customer = $pg->addAsCustomer($card, $userPaymentGateway);

                        if ($customer != null) {

                            if ($customer->succeeded) {

                                $creditCardInfo->update([
                                    'card_name' => $card->firstName . ' ' . $card->lastName,
                                    'f_name' => $card->firstName,
                                    'l_name' => $card->lastName,
                                    'cc_last_4_digit' => $customer->last_four_digits,
                                    'cc_exp_month' => $customer->month,
                                    'cc_exp_year' => $customer->year,
                                    'customer_object' => json_encode($customer),
                                    'auth_token' => $customer->token,
                                    'status' => config('db_const.credit_card_infos.status.Created'),
                                    'system_usage' => '',
                                    'attempts' => $creditCardInfo->attempts + 1,
                                    'error_message' => "Successfully Created Customer Object.\nBy " . ReTryForCustomerObject::class,
                                    'type'=> config('db_const.credit_card_infos.type.customer_obj_created')
                                ]);

                                $transactions = TransactionInit::where('final_tick', 0)
                                    ->where('lets_process', 0)
                                    ->where('type', 'C')
                                    ->where('payment_status', TransactionInit::PAYMENT_STATUS_PENDING)
                                    ->where('booking_info_id', $bookingInfo->id)
                                    ->get();

                                foreach ($transactions as $transaction) {
                                    $transaction->lets_process = 1;
                                    $transaction->save();
                                }

                            } else {
                                $this->failedCase('Customer Object not created: ' . $customer->message, '', $creditCardInfo);
                                $ccForException = null;
                                Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);
                            }

                        } else {
                            $this->failedCase('Customer Object came NULL from PaymentGateway Class', '', $creditCardInfo);
                            $ccForException = null;
                            Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);
                        }

                    } catch (GatewayException $e) {
                        if ($e->getCode() != PaymentGateway::CODE_NETWORK_ERROR_RE_TRY_ABLE) {
                            Bookings::BA_reportInvalidCardForBDCChannel($bookingInfo);
                        }
                        $this->failedCase($e->getDescription(), $e->getTraceAsString(), $creditCardInfo);
                        $ccForException = null;
                    }

                } else {
                    $ccForException = null;
                }

            } catch (\Exception $e) {
                $this->failedCase($e->getMessage(), $e->getTraceAsString(), $ccForException);
                $ccForException = null;
            }

        }


    }

    private function failedCase(string  $errorMessage, string $stackTrace, CreditCardInfo $creditCardInfo) {

        Log::error("Customer Object Exception: " . $errorMessage, [
            'File' => ReTryForCustomerObject::class,
            'stack' => $stackTrace
        ]);

        if($creditCardInfo != null)
            $creditCardInfo->update([
                'attempts'=> ($creditCardInfo->attempts + 1),
                'error_message' => $creditCardInfo->error_message . "\n" . $errorMessage,
                'due_date' => Carbon::now()->addMinute(2)->toDateTimeString(),
                'type'=> config('db_const.credit_card_infos.type.customer_obj_not_created')
            ]);

        if($creditCardInfo->attempts >= CreditCardInfo::TOTAL_ATTEMPTS)
            $this->informDevelopers($creditCardInfo);

    }

    /**
     * @param CreditCardInfo $creditCardInfo
     * @return void
     */

    private function informDevelopers(CreditCardInfo $creditCardInfo){
        try {

            Mail::to(config('db_const.app_developers.emails.to'))
                ->cc(config('db_const.app_developers.emails.cc'))
                ->send(
                    new GenericEmail(array(
                        'subject' => 'CC Customer Object not Created after Specific Attempts.',
                        'markdown' => 'emails.network_error_email_markdown',
                        'noReply' => true,
                        'message'=> 'CC Customer Object not Created after Specific Attempts, File => '. ReTryForCustomerObject::class,
                        'any_json_object' => json_encode($creditCardInfo, JSON_PRETTY_PRINT)
                    ))
                );

        } catch(\Exception $e) {
            Log::error("Message: " . $e->getMessage(), [
                'File' => ReTryForCustomerObject::class,
                'stack' => $e->getTraceAsString()
            ]);
        }
    }
}
