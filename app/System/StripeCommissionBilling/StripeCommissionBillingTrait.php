<?php


namespace App\System\StripeCommissionBilling;

use App\BookingInfo;
use App\PropertyInfo;
use App\StripeUsageDetail;
use App\TransactionInit;
use App\UserAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;
use Stripe\PaymentMethod;
use Stripe\Plan;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\SubscriptionItem;
use Stripe\UsageRecord;

trait StripeCommissionBillingTrait
{


    /**
     * @param TransactionInit $transactionInit
     */
    protected function updateMeteredUsageSubscriptionInvoiceOnTransactionSuccess(TransactionInit $transactionInit)
    {
        try {
            if ($transactionInit->user_account->stripe_customer_id != null) {
                /**
                 * @var $stripeCustomerObject Customer
                 * @var $subscription Subscription
                 * @var $subscriptionItem SubscriptionItem
                 * @var $usageRecord UsageRecord
                 */
                $this->setApiKey();
                $stripeCustomerObject = $this->retrieveCustomerFromStripe($transactionInit->user_account->stripe_customer_id);
                $subscriptions = $stripeCustomerObject->subscriptions->data;


                foreach ($subscriptions as $subscription) {
                    foreach ($subscription->items->data as $subscriptionItem) {
                        if ((strtolower($subscriptionItem->plan->usage_type) === 'metered')
                            && ($this->isPlanValidToUpdateTransactionRecords($subscriptionItem->plan->id))) {
                            if ($this->isPlanToUpdateTransactionAmount($subscriptionItem->plan->id))
                            $quantity = $this->getVolumeInCentsByCurrencyConversion($transactionInit->price,
                                $transactionInit->booking_info->property_info->currency_code);
                            elseif ($this->isPlanToUpdateTransactionCount($subscriptionItem->plan->id))
                                $quantity = 1;
                            else
                                return;

                            if (strtolower($subscriptionItem->plan->aggregate_usage) === 'max')
                                $volume = ($subscriptionItem->quantity + $quantity);
                            elseif (strtolower($subscriptionItem->plan->aggregate_usage) === 'sum')
                                $volume = $quantity;

                            $usageRecord = $this->createUsageRecordsOnStripe($subscriptionItem->id, $volume);
                            if ($usageRecord->id)
                                self::stripeUsageDetailsEntry($transactionInit->user_account->id,
                                    TransactionInit::class, $transactionInit->id, json_encode($usageRecord),
                                    1, 'Successfully Updated.');
                        }
                    }
                }
            } else {
                $message = 'Stripe Customer Id not Created yet for User Account, Update Invoice Volume';
                Log::notice($message, ['User Account Id' => $transactionInit->user_account->id,
                    'Transaction Init Id' => $transactionInit->id]);
                self::stripeUsageDetailsEntry($transactionInit->user_account->id, TransactionInit::class,
                    $transactionInit->id, null, 0, $message);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['TransactionInit Id' => $transactionInit->id, 'StackTrace' => $e->getTraceAsString()]);
            self::stripeUsageDetailsEntry($transactionInit->user_account->id, TransactionInit::class,
                $transactionInit->id, json_encode($e->getTraceAsString()), 0, $e->getMessage());
        }
    }

    /**
     * Set Stripe Api Key for API Call
     */
    protected function setApiKey()
    {
        Stripe::setApiKey(config(get_billing_config_file_name() . '.secret_key'));
    }

    /**
     * Retrieve Customer From  Stripe against Stripe Customer Id
     * @param $customerStripeId
     * @return Customer|null
     */
    protected function retrieveCustomerFromStripe($customerStripeId)
    {
        try {
            $this->setApiKey();
            return Customer::retrieve($customerStripeId);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTraceAsString()]);
        }
        return null;
    }

    /**
     * @param $userAccountId
     * @param string $modelName
     * @param int $modelId
     * @param $jsonResponse
     * @param bool $status
     * @param string|null $description
     */
    static function stripeUsageDetailsEntry($userAccountId, string $modelName, int $modelId, $jsonResponse, bool $status, string $description = null)
    {
        StripeUsageDetail::create(['user_account_id' => $userAccountId, 'model_name' => $modelName,
            'model_id' => $modelId, 'description' => $description, 'response' => $jsonResponse, 'status' => $status]);
    }

    /**
     * @param Event $event
     */
    protected function handleCustomerCreatedHook(Event $event)
    {
        try {
            /**
             * @var $stripeCustomerObject Customer
             */
            $stripeCustomerObject = $this->event->data->object;
            if (isset($stripeCustomerObject->metadata->userAccountId) && ($stripeCustomerObject->metadata->userAccountId != null)) {
                $userAccount = UserAccount::where('id', $stripeCustomerObject->metadata->userAccountId)->first();
                if (!is_null($userAccount))
                    $userAccount->update(['stripe_customer_id' => $stripeCustomerObject->id]);
                else
                    self::InformDevelopersCustomerMetaDataUserAccountIdNotValid($event);
            } else {
                self::InformDevelopersCustomerMetaDataUserAccountIdNotValid($event);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'stackTrace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param $jsonObject
     */
    static function InformDevelopersCustomerMetaDataUserAccountIdNotValid($jsonObject)
    {
        /*
         *
        Log::notice('User Account Id Missing or not Valid in Meta Data');
        sendMailToAppDevelopers('User Account Id Missing or not Valid in Meta Data,
         Customer Created on Stripe Account', 'User Account Id Missing or not Valid in Meta Data,
         Customer Created on Stripe Account', json_encode($jsonObject));
        */
    }

    /**
     * * Handle Upcoming Invoices
     * @param Invoice $invoice
     */
    protected function handleUpComingInvoiceHook(Invoice $invoice)
    {
        foreach ($invoice->lines->data as $lineItem) {
            /**
             * @var $invoiceLineItem InvoiceLineItem
             */
            $invoiceLineItem = $lineItem;
            if (($invoiceLineItem->type === 'subscription') && ($invoiceLineItem->plan->usage_type === 'metered')) {
                $stripeCustomerObject = $this->retrieveCustomerFromStripe($invoice->customer);
                if ((!is_null($stripeCustomerObject)) && (!$stripeCustomerObject->isDeleted())
                    && ($stripeCustomerObject->metadata->count() > 0)) {
                    if (isset($stripeCustomerObject->metadata->userAccountId)
                        && ($stripeCustomerObject->metadata->userAccountId != null)) {
                        $this->updateMonthlyMeteredUsageSubscriptionUpComingInvoiceHook($stripeCustomerObject, $invoiceLineItem);
                    } else {
                        self::InformDevelopersCustomerMetaDataUserAccountIdNotValid(json_encode($invoice));
                    }
                } elseif ($stripeCustomerObject->isDeleted()) {
                    Log::error('Customer Deleted on Stripe', ['Request Content Invoice' => json_encode($invoice)]);
                }
            }
        }
    }

    /**
     * @param Customer $stripeCustomerObject
     * @param InvoiceLineItem $invoiceLineItem
     */
    protected function updateMonthlyMeteredUsageSubscriptionUpComingInvoiceHook(Customer $stripeCustomerObject, InvoiceLineItem $invoiceLineItem)
    {
        if (strtolower($invoiceLineItem->plan->usage_type) == 'metered') {
            $this->setApiKey();
            Log::info(json_encode($invoiceLineItem->period));
            if (strtolower($invoiceLineItem->plan->aggregate_usage == 'max')) {
                //TODO Implement by Requirement Docs
            } else if (strtolower($invoiceLineItem->plan->aggregate_usage == 'sum')) {
                //TODO Implement by Requirement Docs
            }
        }
    }

    /**
     * @return Collection
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function getAllBillingPlans()
    {
        $this->setApiKey();
        return Plan::all();
    }

    /**
     * Getting User Subscribed Plans From Stripe
     * @param UserAccount $userAccount
     * @return array|mixed
     * @throws Exception
     */
    public function getUserSubscribedPlans(UserAccount $userAccount)
    {
        if ($userAccount->stripe_customer_id != null) {
            $stripeCustomerObject = $this->retrieveCustomerFromStripe($userAccount->stripe_customer_id);
            $subscriptions =  $stripeCustomerObject != null ? $stripeCustomerObject->subscriptions->data : [];
            self::planAttachedStatusUpdateForBilling($userAccount, $subscriptions);
            return $subscriptions;
        } else
            throw new Exception("Stripe Customer ID Missing or not Valid for User Account # $userAccount->id");
    }

    /**
     * @param UserAccount $userAccount
     * @param array $subscriptions
     * @return bool
     */
    static function planAttachedStatusUpdateForBilling(UserAccount $userAccount, array $subscriptions)
    {
        /**
         * @var Subscription $subscription
         */

        $plan_attached_status = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status != 'canceled' && !$subscription->isDeleted()
                && !empty($subscription->items->data)) {
                $plan_attached_status = true;
                break;
            }
        }

        if ($userAccount->plan_attached_status != $plan_attached_status) {
            $userAccount->update(['plan_attached_status' => $plan_attached_status,
                'plan_attached_status_last_sync' => now()->toDateTimeString()]);
        }


        return $plan_attached_status;
    }

    /**
     * @param $allPlansObject
     * @param $subscriptionsObjects
     * @param string $subscriptionId
     * @return mixed
     */
    protected function getAllBillingPlansWithSubscriptionStatus($allPlansObject, $subscriptionsObjects, string $subscriptionId = null)
    {
        /*  @var $plan Plan
         * @var $subscription Subscription
         * @var $subscriptionItem SubscriptionItem
         */

        $plans = Arr::flatten(config(get_billing_config_file_name() . '.plans')); // All functional plans

        foreach ($allPlansObject as $key => $plan) {

            if (!in_array($plan->id, $plans)) {
                unset($allPlansObject[$key]);
                continue;
            }

            $plan->alreadySubscribed = false;
            $plan->quantity = 1;
            $plan->checked = false;

            foreach ($subscriptionsObjects as $subscription) {
                $subscriptionItems = $subscription->items->data;

                foreach ($subscriptionItems as $subscriptionItem) {
                    if (($subscriptionItem->plan->product == $plan->product) && ($subscriptionItem->plan->id == $plan->id)
                        && ($subscriptionItem->subscription == $subscriptionId)) {

                        if (strtolower($subscriptionItem->plan->usage_type) === 'licensed')
                            $plan->quantity += $subscriptionItem->quantity;

                        $plan->alreadySubscribed = true;
                        unset($subscriptionItem);
                    }
                }
            }
        }

        return $allPlansObject;
    }

    /**
     * @param string $subscriptionItemId
     * @return SubscriptionItem
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function cancelSubscriptionItem(string $subscriptionItemId)
    {
        $item = $this->retrieveSubscriptionItem($subscriptionItemId);

        if ($item->plan->usage_type != 'licensed')
            throw new \Exception('Kindly use Stripe Panel to Remove Metered Usage Plan');

        return $this->retrieveSubscriptionItem($subscriptionItemId)->delete();
    }

    /**
     * @param string $subscriptionItemId
     * @return SubscriptionItem
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function retrieveSubscriptionItem(string $subscriptionItemId)
    {
        $this->setApiKey();
        return SubscriptionItem::retrieve($subscriptionItemId);
    }

    /**
     * @param string $subscriptionId
     * @param string $planID
     * @param $userAccountId
     * @param bool $metered
     * @param int $quantity
     * @return SubscriptionItem
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function AddNewPlansToSubscription(string $subscriptionId, string $planID,  $userAccountId, bool $metered = false, int $quantity = 1)
    {
        $this->setApiKey();
        if ($metered) {
            return SubscriptionItem::create(["subscription" => $subscriptionId, "plan" => $planID]);
        } else {
            if(in_array(strtolower($planID), config(get_billing_config_file_name() . '.plans.plan_per_rental_charge')))
                $quantity = PropertyInfo::where('user_account_id', $userAccountId)->where('status', 1)->count();

            return SubscriptionItem::create([
                "subscription" => $subscriptionId,
                "plan" => $planID,
                "quantity" => $quantity,
            ]);
        }
    }

    /**
     * @param string $subscriptionItemId
     * @param array $keyValuePairsToUpdate
     * @return SubscriptionItem
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function updateSubscriptionItem(string $subscriptionItemId, array $keyValuePairsToUpdate)
    {
        $this->setApiKey();
        return SubscriptionItem::update($subscriptionItemId, $keyValuePairsToUpdate);
    }

    /**
     * @param string $stripeCustomerId
     * @param array $itemsArr
     * @param int $trial_days
     * @return Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function createSubscription(string $stripeCustomerId, array $itemsArr, $trial_days)
    {
        $trial_days = is_null($trial_days) ? 14 : (int) abs($trial_days);
        $trial_end = Carbon::now()->addDays($trial_days)->timestamp;
        $this->setApiKey();

        if ((config('app.env') === 'local' || config('app.debug') == true)) {  // TODO Remove For Releases
            $items = ['customer' => $stripeCustomerId, 'collection_method' => 'send_invoice', 'items' => $itemsArr ,
                'trial_end' => $trial_end, 'days_until_due' => 15,
                "billing_thresholds" => ["amount_gte" => 50000, "reset_billing_cycle_anchor" => true]
            ];
        } else {
            $items = [
                'customer' => $stripeCustomerId,
                'days_until_due' => 15,
                'collection_method' => 'send_invoice',
                'trial_end' => $trial_end,
                'items' => $itemsArr
            ];
        }
        return Subscription::create($items);
    }


    /**
     * @param string $subscriptionId
     * @param string $subscriptionItemId
     * @param int $quantity
     * @return Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function updateFlatSubscriptionQty(string $subscriptionId, string $subscriptionItemId, int $quantity)
    {
        return Subscription::update($subscriptionId, [
            'items' => [
                [
                    'id' => $subscriptionItemId,
                    'quantity' => $quantity,
                    ],
                ],
            'proration_date' => strtotime(now()),
        ]);
    }

    /**
     * @param string $subscriptionID
     * @return Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function cancelSubscription(string $subscriptionID)
    {
        $subscription = $this->retrieveSubscription($subscriptionID);
        if (empty($subscription->trial_end) || $subscription->trial_end < now()->timestamp) {
            $this->setApiKey();
            $subscription = Subscription::update($subscriptionID, [
                'billing_cycle_anchor' => 'now',
                'prorate' => true,
            ]);
        }
        return $subscription->cancel();
    }

    /**
     * @param string $subscriptionId
     * @return Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function retrieveSubscription(string $subscriptionId)
    {
        $this->setApiKey();
        return Subscription::retrieve($subscriptionId);
    }

    /**
     * @return SetupIntent
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function createPaymentIntent()
    {
        $this->setApiKey();
        return SetupIntent::create();
    }

    /**
     * @param $userAccount
     * @param $paymentMethodId
     * @return Customer|null
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function createStripeCustomerWithPaymentMethod($userAccount, $paymentMethodId)
    {
        $email = ($userAccount->email != null ? $userAccount->email : $userAccount->users->first()->email);
        $this->setApiKey();
        if (!empty($userAccount->stripe_customer_id) && ($userAccount->stripe_customer_id != null)) {
            /**
             * @var $subscription Subscription
             */
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $userAccount->stripe_customer_id]);
            $customer = $this->retrieveCustomerFromStripe($userAccount->stripe_customer_id);
            foreach ($customer->subscriptions->data as $subscription) {
                Subscription::update($subscription->id, ['default_payment_method' => $paymentMethodId,]);
            }
        } else {
            $customer = Customer::create(['payment_method' => $paymentMethodId,
                [
                    'email' => $email,
                    'name' => $userAccount->name,
                    'description' => "CA Billing Customer for User Account # $userAccount->id",
                    'metadata' => [
                        'userAccountId' => $userAccount->id,
                        'email' => $email,
                    ]
                ]
            ]);
            $userAccount->billing_card_required_reminder_due_date = null;
            $userAccount->billing_reminder_attempts = 0;
            $userAccount->stripe_customer_id = $customer->id;
            $userAccount->save();
        }
        return $customer;
    }


    /**
     * @param UserAccount $userAccount
     * @return Customer
     * @throws Exception
     */
    protected function createStripeCustomerWithOutPaymentMethod(UserAccount $userAccount)
    {

        if (!empty($userAccount->stripe_customer_id)) {
            throw new Exception("Stripe Customer Already Created for User Account # $userAccount->id");
        } else {
            $email = ($userAccount->email != null ? $userAccount->email : $userAccount->users->first()->email);
            $this->setApiKey();

            $customer = Customer::create(
                [
                    'email' => $email,
                    'name' => $userAccount->name,
                    'description' => "CA Billing Customer for User Account # $userAccount->id",
                    'metadata' => [
                            'userAccountId' => $userAccount->id,
                            'email' => $email,
                    ]
                ]
            );

            $userAccount->stripe_customer_id = $customer->id;
            $userAccount->save();
            return $customer;
        }
    }

    /**
     * @param string $planId
     * @return bool
     */
    private function isPlanValidToUpdateTransactionRecords(string $planId)
    {
        return ($this->isPlanToUpdateTransactionCount($planId) || $this->isPlanToUpdateTransactionAmount($planId));
    }

    /**
     * @param string $planId
     * @return bool
     */
    private function isPlanToUpdateTransactionCount(string $planId)
    {
        return in_array($planId,
            config(get_billing_config_file_name() . '.plans.transaction_count'));
    }

    /**
     * @param string $planId
     * @return bool
     */
    private function isPlanToUpdateTransactionAmount(string $planId)
    {
        return in_array($planId,
            config(get_billing_config_file_name() . '.plans.transaction_volume'));
    }

    /**
     * @param string $planId
     * @return bool
     */
    private function isPlanToUpdateBookingCount(string $planId){
        return in_array(strtolower($planId) , config(get_billing_config_file_name() . '.plans.plan_per_booking_charge'));
    }

    /**
     * @param string $planId
     * @return bool
     */
    private function isPlanToUpdateActivePropertiesCount(string $planId){
        return in_array(strtolower($planId), config(get_billing_config_file_name() . '.plans.plan_per_rental_charge'));
    }


    /**
     * @param BookingInfo $bookingInfo
     */
    protected function updateMeteredUsageSubscriptionInvoiceOnNewBooking(BookingInfo $bookingInfo)
    {
        try {

            if ($bookingInfo->user_account->stripe_customer_id != null) {
                /**
                 * @var $stripeCustomerObject Customer
                 * @var $subscription Subscription
                 * @var $subscriptionItem SubscriptionItem
                 * @var $usageRecord UsageRecord
                 */
                $this->setApiKey();
                $stripeCustomerObject = $this->retrieveCustomerFromStripe($bookingInfo->user_account->stripe_customer_id);
                $subscriptions = $stripeCustomerObject->subscriptions->data;

                foreach ($subscriptions as $subscription) {
                    foreach ($subscription->items->data as $subscriptionItem) {
                        if ((strtolower($subscriptionItem->plan->usage_type) === 'metered')
                            && ($this->isPlanToUpdateBookingCount($subscriptionItem->plan->id))){

                            $volume = 1;

                            if (strtolower($subscriptionItem->plan->aggregate_usage) === 'max')
                                $volume = ($subscriptionItem->quantity + 1);

                            $usageRecord = $this->createUsageRecordsOnStripe($subscriptionItem->id, $volume);
                            if ($usageRecord->id)
                                self::stripeUsageDetailsEntry($bookingInfo->user_account->id,
                                    BookingInfo::class, $bookingInfo->id, json_encode($usageRecord),
                                    1, 'Successfully Updated.');
                        }
                    }
                }
            } else {
                $message = 'Stripe Customer not Created yet for User Account, Update Booking Count Invoice Volume';
                Log::notice($message, ['User Account Id' => $bookingInfo->user_account->id,
                    'Booking Info  Id' => $bookingInfo->id]);
                self::stripeUsageDetailsEntry($bookingInfo->user_account->id, BookingInfo::class,
                    $bookingInfo->id, null, 0, $message);
            }
        } catch (Exception $e) {
            self::stripeUsageDetailsEntry($bookingInfo->user_account->id, BookingInfo::class,
                $bookingInfo->id, json_encode($e->getTraceAsString()), 0, $e->getMessage());
        }
    }

    /**
     * Update User's all Active Properties on Stripe Commission Billing Subscription
     * @param PropertyInfo $propertyInfo
     */
    protected  function updateMeteredUsageSubscriptionInvoiceOnPropertyConnectDisconnect(PropertyInfo $propertyInfo)
    {
        try {

            $userAccount = $propertyInfo->user_account;
            $userAccount->load('properties_info');

            if ($userAccount->stripe_customer_id != null) {
                /**
                 * @var $stripeCustomerObject Customer
                 * @var $subscription Subscription
                 * @var $subscriptionItem SubscriptionItem
                 * @var $usageRecord UsageRecord
                 */
                $this->setApiKey();
                $stripeCustomerObject = $this->retrieveCustomerFromStripe($userAccount->stripe_customer_id);
                $subscriptions = $stripeCustomerObject->subscriptions->data;

                foreach ($subscriptions as $subscription) {
                    foreach ($subscription->items->data as $subscriptionItem) {
                        if ((strtolower($subscriptionItem->plan->usage_type) === 'licensed')
                            && ($this->isPlanToUpdateActivePropertiesCount($subscriptionItem->plan->id))){
                            $quantity = $userAccount->properties_info->where('status', 1)->count();
                            $usageRecord = $this->updateFlatSubscriptionQty($subscription->id, $subscriptionItem->id, $quantity);
                            if ($usageRecord)
                                self::stripeUsageDetailsEntry($userAccount->id,
                                    UserAccount::class, $userAccount->id, json_encode($usageRecord),
                                    1, ("Successfully Updated Active Properties. \n" .
                                        json_encode($userAccount->properties_info->where('status', 1)
                                            ->pluck('pms_property_id', 'id')->toArray())));
                        }
                    }
                }
            } else {
                $message = 'Stripe Customer not Created yet for User Account, Update Properties 
                Count Invoice Volume';
                Log::notice($message, ['User Account Id' => $userAccount->id,
                    'User Account  Id' => $userAccount->id]);
                self::stripeUsageDetailsEntry($userAccount->id, UserAccount::class,
                    $userAccount->id, null, 0, $message);
            }
        } catch (Exception $e) {
            self::stripeUsageDetailsEntry($userAccount->id, UserAccount::class,
                $userAccount->id, json_encode($e->getTraceAsString()), 0,
                ("Fail to Updated Active Properties. \n". $e->getMessage()). "\n" .
                json_encode($userAccount->properties_info->where('status', 1)->pluck('pms_property_id','id')->toArray()));
        }
    }

    /**
     * @param string $subscriptionItemId
     * @param $volume
     * @return \Stripe\ApiResource
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function createUsageRecordsOnStripe(string $subscriptionItemId, $volume)
    {
        $this->setApiKey();
        return SubscriptionItem::createUsageRecord($subscriptionItemId,
            ['quantity' => $volume, 'timestamp' => strtotime(now()), 'action' => "increment"]);
    }

    /**
     * @param $stripeCustomerId
     * @return Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function addDefaultCommissionBillingSubscriptionPlan($stripeCustomerId)
    {
        $this->setApiKey();
        $plan = [['plan' => config(get_billing_config_file_name() . '.plans.plan_default')]];
        $trial_days = 15;
        return $this->createSubscription($stripeCustomerId, $plan, $trial_days);
    }

    /**
     * @param Event $event
     */
    protected function handleCustomerTrialWillEndHook(Event $event)
    {
        try {
            /**
             * @var $subscription Subscription
             */
            $subscription = $this->event->data->object;
            $userAccount = UserAccount::where('stripe_customer_id', $this->getCustomerIdFromSubscription($subscription))->first();
            if (!is_null($userAccount))
                $userAccount->update(['billing_reminder_attempts' => 0,
                'billing_card_required_reminder_due_date' => $this->getSubscriptionTrialEndingDate($subscription)]);
            else
                Log::emergency("No Such User Stripe Customer ID found in Database for event $event->type",
                    ['__function__' => __FUNCTION__,  'HookContent' => json_encode($event)]);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'stackTrace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param Event $event
     */
    protected function handleCustomerInvoicePaymentFailedHook(Event $event)
    {
        try {
            /**
             * @var $invoice Invoice
             */
            $invoice = $this->event->data->object;
            $this->updateUserAccountByCheckingInvoiceHook($invoice, $event->type, false);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'stackTrace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param Event $event
     */
    protected function handleCustomerInvoicePaymentSuccessHook(Event $event)
    {
        try {
            /**
             * @var $invoice Invoice
             */
            $invoice = $this->event->data->object;
            $this->updateUserAccountByCheckingInvoiceHook($invoice, $event->type, true);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'stackTrace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param Invoice $invoice
     * @param string $eventType
     * @param bool $paid
     */
    protected function updateUserAccountByCheckingInvoiceHook(Invoice $invoice, string $eventType,  bool $paid)
    {
        if (in_array(strtolower($invoice->billing_reason), ['subscription_cycle', 'subscription_create'])) {
            $user_account = UserAccount::where('stripe_customer_id', $invoice->customer)->first();
            if (!empty($user_account)) {
                $suspended = config('db_const.user_account.status.suspendedbyadmin.value');
                $active = config('db_const.user_account.status.active.value');
                $update_columns = ($paid
                    ? ['suspend_account_on' => null,
                        'billing_card_required_reminder_due_date' => null,
                        'status' => $user_account->status == $suspended ? $active : $user_account->status]
                    : ['suspend_account_on' => Carbon::createFromTimestamp($invoice->period_end)->toDateTimeString()]
                );
                $user_account->update($update_columns);
                Log::notice('Customer Invoice Hook for User Account' . $user_account->id ,
                    ['webHook-content' => json_encode($invoice)]
                );
            } else {
                Log::notice('Billing Customer not Found for this invoice',
                    ['webHook-content' => json_encode($invoice)]
                );
            }
        } else{
           Log::notice('Stripe WebHook Received, not any action implemented, Invoice => ',
               ['webHook-content' => json_encode($invoice)]
           );
        }
    }

    /**
     * @param Subscription $subscription
     * @return string
     */
    private function getSubscriptionTrialEndingDate(Subscription $subscription)
    {
       return $subscription->trial_end != null
           ? Carbon::createFromTimestamp($subscription->trial_end)->toDateTimeString() : now()->toDateTimeString();
    }

    /**
     * @param Subscription $subscription
     * @return string
     */
    private function getCustomerIdFromSubscription(Subscription $subscription)
    {
       return $subscription->customer;
    }

    /**
     * @param string $planId
     * @return Collection
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function getAllSubscriptionWithPlanId(string  $planId)
    {
        $this->setApiKey();
        return Subscription::all(['plan' => $planId,]);
    }

    /**
     * @param $price
     * @param string $property_info_currency_code
     * @return int
     */
    protected function getVolumeInCentsByCurrencyConversion($price, string $property_info_currency_code)
    {
        $currencies = json_decode(file_get_contents(base_path('app/Json/currencies.json')), true);
        $price_in_cents = ((1 / $currencies['rates'][strtoupper($property_info_currency_code)]) * $price) * 100;
        Log::notice("Total Transaction Price : $property_info_currency_code $price 
         converted Price in USD Cents $price_in_cents", ['currencies' => json_encode($currencies), 'file' => __FILE__]);
        return (int) $price_in_cents;
    }

    /**
     * If Subscription status changed activate or suspend account
     *
     * @param Event $event
     * @throws Exception
     */
    protected function handleCustomerSubscriptionUpdated(Event $event)
    {
        /**
         * @var $subscription Subscription
         * @var $user_account UserAccount
         */
       $subscription = $event->data->object;
       $user_account = UserAccount::where('stripe_customer_id', $subscription->customer)->first();

       if (!empty($user_account)) {

           $update_columns = $this->columnToUpdateBySubscription($user_account);

           $user_account->update($update_columns); // Activate or suspend account

           Log::notice('Customer Subscription Updated for User Account' . $user_account->id,
               ['webHook-content' => json_encode($subscription)]
           );
       }
    }

    /**
     * Activate or Suspend account by checking subscriptions
     * @param UserAccount $user_account
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     * @throws  Exception
     */
    private function columnToUpdateBySubscription(UserAccount $user_account)
    {
        $suspended = config('db_const.user_account.status.suspendedbyadmin.value');
        $active = config('db_const.user_account.status.active.value');

        // Default not-Suspended.
        $account_values =  [
            'suspend_account_on' => null,
            'status' => $user_account->status == $suspended ? $active : $user_account->status
        ];


        $subscriptions = $this->getUserSubscribedPlans($user_account);

        foreach ($subscriptions as $subscription) {
            if (in_array($subscription->status, ['past_due', 'unpaid'])) {
                if (empty($subscription->discount)) {

                    // Generate final invoice for already used units.
                    Subscription::update($subscription->id, ['billing_cycle_anchor' => 'now', 'prorate' => true]);

                    try {
                        // Add Account Suspended Coupon to avoid further charge.
                        Subscription::update($subscription->id,
                            ['coupon' => config(get_billing_config_file_name() . '.account_suspended_coupon')]);
                    } catch (\Exception $exception) {
                        log_exception_by_exception_object($exception);
                    }


                    Log::notice('Suspended Account Coupon added to subscription',
                        ['user_account_id' => $user_account, 'subscription_id' => $subscription->id]
                    );
                }
                $account_values = ['suspend_account_on' => now()->toDateTimeString(), 'status' => $suspended];
            }
        }

        if ($account_values['status'] != $suspended)
            self::removeCoupon($subscriptions);

        // TODO Remove for account Suspend Stripe Billing
        //if ( config('app.env') !== 'local' && config('app.debug') != true) //TODO REMOVE FOR Releases
            $account_values['status'] = $user_account->status;

        return  $account_values;
    }

    /**
     * @param array $subscriptions
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function removeCoupon(array $subscriptions)
    {
        /**
         * @var $subscription Subscription
         */
        foreach ($subscriptions as $subscription) {
            if (in_array($subscription->status, ['active'])) {
                if (!empty($subscription->discount)) {
                    // Generate invoice for already used units in suspended coupon to avoid charge for un used period.
                    Subscription::update($subscription->id, ['billing_cycle_anchor' => 'now', 'prorate' => true]);
                    // Delete Coupon
                    $subscription->deleteDiscount();
                    Log::notice('Account Suspended Coupon Removed for subscription',
                        [ 'subscription_id' => $subscription->id]
                    );
                }
            }
        }
    }

    public function generateCoupon(int $percent_off, $name, $duration) {
        $this->setApiKey();
        $coupon = \Stripe\Coupon::create(
            [
                'percent_off' => $percent_off,
                'name' => $name,
                'duration' => $duration,
            ]
        );

        return 'Generated Successfully. Kindly Add this coupon ID to account_suspended_coupon in  
            config/db_const/stripe_commission_billing.php file  "'. $coupon->id.'"';
    }
}

