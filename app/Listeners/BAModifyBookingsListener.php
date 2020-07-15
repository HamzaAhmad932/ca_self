<?php

namespace App\Listeners;

use App\CreditCardInfo;
use App\Events\BAModifyBookingsEvent;
use App\Repositories\Settings\PaymentTypeMeta;
use App\Repositories\TransactionInit\TransactionInitRepository;
use App\Services\UpdateCard;
use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\PaymentGateway;
use App\System\PMS\BookingSources\BS_BookingCom;
use App\System\PMS\BookingSources\BS_Generic;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use App\Repositories\CreditCardAuthorization\CreditCardAuthorizationRepository;


class BAModifyBookingsListener implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    /**
     * @var $event BAModifyBookingsEvent
     */
    private $event;
    private $pivotTable;
    /**
     * @var PaymentGateway
     */
    private $paymentGateway;
    private $isNewCardReceived = false;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {


    }

    /**
     * Handle the event.
     *
     * @param BAModifyBookingsEvent $event
     * @return void
     */
    public function handle(BAModifyBookingsEvent $event)
    {
        /**
         * @var $customerObject ['cc_info'] CreditCardInfo
         */


        try {

            $this->init_job($event);

            switch ($this->event->typeOfPaymentSource) {
                case BS_Generic::PS_CREDIT_CARD:
                    $this->caseCreditCard();
                    break;

                case BS_Generic::PS_VIRTUAL_CARD:
                    $this->caseVirtualCard();
                    break;

                case BS_Generic::PS_BANK_TRANSFER:
                    // No need to update Records for BT Case Bookings.
                    break;
            }

        } catch (FatalThrowableError $e) {
            log_exception_by_exception_object($e);

        } catch (\Exception $e) {
            log_exception_by_exception_object($e, ['booking_info_id' => $event->bookingInfoNewObject->id]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param BAModifyBookingsEvent $event
     * @param \Exception $exception
     * @return void
     */
    public function failed(BAModifyBookingsEvent $event, $exception)
    {

        // TODO: handle failed case
    }

    /**
     * Setter for variables.
     * @param $event
     */
    private function init_job($event)
    {
        $this->event = $event;
        $this->pivotTable = new PaymentTypeMeta();
        $this->paymentGateway = new PaymentGateWay();
    }

    /**
     * Business Logic for CC Booking's Card Update
     */
    private function caseCreditCard()
    {
        $customerObject = $this->createCustomerObject(0);

        if ($customerObject['status']) {

            CreditCardAuthorizationRepository::updateCreditCardAuthOnCustomerSuccess(
                $this->event->bookingInfoNewObject,
                $customerObject['cc_info']->id
            );

            CreditCardAuthorizationRepository::updateSecurityDamageDepositAuthOnCustomerSuccess(
                $this->event->bookingInfoNewObject,
                $customerObject['cc_info']->id
            );

            TransactionInitRepository::updateTransactionInitsOnCustomerSuccess(
                $this->event->bookingInfoNewObject,
                false
            );

        } else if (empty($customerObject['cc_info']->auth_token)) {

            $this->dispatchCustomerObjectRetry(0, $customerObject['cc_info'], $customerObject['error_message']);

            if ($this->isNewCardReceived) {
                TransactionInitRepository::updateTransactionInitsOnCustomerFail(
                    $this->event->bookingInfoNewObject,
                    false
                );
            }
        }
    }

    /**
     * Business Logic for VC Booking's Card Update
     */
    private function caseVirtualCard()
    {
        $customerObject = $this->createCustomerObject(1, $this->event->dueDate);

        if (!empty($customerObject['status']) && $this->isNewCardReceived) {

            TransactionInitRepository::updateTransactionInitsOnCustomerSuccess(
                $this->event->bookingInfoNewObject,
                false
            );

        } elseif ($this->isNewCardReceived) {

            TransactionInitRepository::updateTransactionInitsOnCustomerFail(
                $this->event->bookingInfoNewObject,
                false
            );
        }
    }


    /**
     * @param int $is_vc
     * @param null $dueDate
     * @return null
     */
    private function createCustomerObject(int $is_vc, $dueDate = null)
    {

        $cc_infoObject = null;

        try {
            // First Creating new Record
            $cc_infoObject = new CreditCardInfo();

            $cc_infoObject->booking_info_id = $this->event->bookingInfoNewObject->id;
            $cc_infoObject->user_account_id = $this->event->userAccount->id;
            $cc_infoObject->is_vc = $is_vc;
            $cc_infoObject->card_name = $this->event->card->getName();
            $cc_infoObject->f_name = $this->event->card->firstName;
            $cc_infoObject->l_name = $this->event->card->lastName;
            $cc_infoObject->cc_last_4_digit = $this->event->card->getLastFourDigits();
            $cc_infoObject->cc_exp_month = $this->event->card->expiryMonth;
            $cc_infoObject->cc_exp_year = $this->event->card->expiryYear;
            $cc_infoObject->system_usage = Card::encrypt($this->event->card);
            $cc_infoObject->customer_object = '';
            $cc_infoObject->auth_token = '';
            $cc_infoObject->due_date = Carbon::now()->toDateTimeString();
            $cc_infoObject->status = config('db_const.credit_card_infos.status.Void');
            $cc_infoObject->error_message = 'Creating empty, before customer object, Booking Modify';
            $cc_infoObject->type = config('db_const.credit_card_infos.type.customer_obj_not_created');

            if ($is_vc == 1 && !empty($dueDate)) {

                $cc_infoObject->due_date = $dueDate;
                $cc_infoObject->status = config('db_const.credit_card_infos.status.Scheduled');
                $cc_infoObject->save();

                /**
                 * Booking is now being set as VC because we have received dueDate which indicates that this booking
                 * has been changed on PMS to VC.
                 */
                $this->event->bookingInfoNewObject->is_vc = BS_Generic::PS_VIRTUAL_CARD;
                $this->event->bookingInfoNewObject->save();

                $this->isNewCardReceived = true;

                return array('status' => false, 'error_message' => 'CC Info Scheduled Record Created', 'cc_info' => $cc_infoObject);
            }

            $cc_infoObject->save();

            // Making Previous Cards Void if any exists, only in case of VC
            CreditCardInfo::whereNotIn('id', [$cc_infoObject->id])
                ->where('is_vc', 1)
                ->where('booking_info_id', $this->event->bookingInfoNewObject->id)
                ->where('user_account_id', $this->event->userAccount->id)
                ->update([
                    'status' => config('db_const.credit_card_infos.status.Void')
                ]);

        } catch (\Exception $e) {
            Log::critical($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'BookingInfoId' => $this->event->bookingInfoNewObject->id,
                'UserAccountId' => $this->event->userAccount->id,
                'Stack' => $e->getTraceAsString()]);
            return array('status' => false, 'error_message' => 'CC Info Record creation failed', 'cc_info' => $cc_infoObject);
        }

        /**
         * @var $customer Customer
         */
        $customer = null;

        $successMessage = 'Successfully Created Customer Object on Booking Modify Notification.';

        try {


            if (!empty($this->event->card->cardNumber))
                $customer = $this->paymentGateway->addAsCustomer($this->event->card, $this->event->userPaymentGateway);
            else
                return array('status' => false, 'error_message' => 'Missing Card Number. Modify Booking.', 'cc_info' => $cc_infoObject);


            if ($customer != null) {
                $this->isNewCardReceived = true;
                $isPMSReportAbleCardReceived = $this->isPMSReportAbleCardReceived($cc_infoObject, $customer);

                if ($customer->succeeded) {

                    $update = $cc_infoObject->update([
                        'cc_last_4_digit' => $customer->last_four_digits,
                        'cc_exp_month' => $customer->month,
                        'cc_exp_year' => $customer->year,
                        'customer_object' => json_encode($customer),
                        'auth_token' => $customer->token,
                        'system_usage' => '',
                        'attempts' => 1,
                        'status' => config('db_const.credit_card_infos.status.Created'),
                        'error_message' => $cc_infoObject->error_message . "\n" . $successMessage,
                        'type' => config('db_const.credit_card_infos.type.customer_obj_created')]);

                    if ($update) {
                        $this->event->booking->cardNumber = null;
                        $this->event->booking->cardExpire = null;
                        $this->event->booking->cardCvv = null;
                        $this->event->bookingInfoNewObject->full_response = json_encode($this->event->booking);
                        $this->event->bookingInfoNewObject->is_pms_reported_for_invalid_card = ($isPMSReportAbleCardReceived ? 0 : $this->event->bookingInfoNewObject->is_pms_reported_for_invalid_card);
                        $this->event->bookingInfoNewObject->save();

                        return array('cc_info' => $cc_infoObject, 'customer' => $customer, 'status' => true, 'error_message' => $successMessage);

                    } else {
                        return array('status' => false, 'cc_info' => $cc_infoObject,
                            'error_message' => 'Credit Card Record not created automatically. ' . " :: cc_info :: " . json_encode($update) . " :: Customer :: " . json_encode($customer));
                    }

                } else {
                    return array('cc_info' => $cc_infoObject, 'status' => false, 'error_message' => 'Customer Object not created: ' . $customer->message);
                }

            } else {
                return array('cc_info' => $cc_infoObject, 'status' => false, 'error_message' => 'Customer Object came NULL from PaymentGateway Class');
            }

        } catch (GatewayException $e) {

            $cc_infoObject->update(['status' => config('db_const.credit_card_infos.status.Scheduled')]);
            return array('cc_info' => $cc_infoObject, 'status' => false, 'error_message' => $e->getDescription());

        } catch (\Exception $e) {

            $timeKey = "key_" . time();

            Log::error($e->getMessage(), array(
                'File' => 'TransactionInitListener',
                'Stack' => $e->getTraceAsString(),
                'Key' => $timeKey,
                'Customer' => json_encode($customer)
            ));

            $cc_infoObject->update(['status' => config('db_const.credit_card_infos.status.Scheduled')]);
            return array('cc_info' => $cc_infoObject, 'status' => false, 'error_message' => $e->getMessage() . " Key to find Stack on Slack: " . $timeKey);
        }
    }


    /**
     * @param $is_vc
     * @param $cc_infoObject
     * @param $errorMessage
     */
    private function dispatchCustomerObjectRetry($is_vc, $cc_infoObject, $errorMessage)
    {

        if ($is_vc == 1)
            return;

        try {

            if (!empty($this->event->card->cardNumber))
                $cc_infoObject->update([
                    'system_usage' => Card::encrypt($this->event->card),
                    'attempts' => 1,
                    'error_message' => $cc_infoObject->error_message . "\n" . $errorMessage . ". Reset attempt counter also.",
                    'status' => config('db_const.credit_card_infos.status.In-Retry')
                ]);

        } catch (\Exception $e) {
            Log::critical("Exception Creating Customer Object and then Dispatching it.", [
                'Message' => $e->getMessage(),
                "File" => BAModifyBookingsListener::class,
                'Stack' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @param $cc_infoObject
     * @param $customerObject
     * @return bool
     */

    private function isPMSReportAbleCardReceived($cc_infoObject, $customerObject)
    {

        if (($this->event->bookingInfoNewObject->channel_code == BS_BookingCom::BA_CHANNEL_CODE)
            && ($this->event->bookingInfoNewObject->is_vc == BS_Generic::PS_CREDIT_CARD)) {
            return $this->isNewCardReceived($cc_infoObject, $customerObject);
        }
        return false;
    }

    /**
     * @param $cc_infoObject
     * @param $customerObject
     * @return bool
     */

    private function isNewCardReceived($cc_infoObject, $customerObject)
    {
        if (($cc_infoObject->cc_last_4_digit != $customerObject->last_four_digits)
            || ($cc_infoObject->cc_exp_month != $customerObject->month)
            || ($cc_infoObject->cc_exp_year != $customerObject->year)) {
            return true; //NewCard Received
        }
        return false;
    }
}
