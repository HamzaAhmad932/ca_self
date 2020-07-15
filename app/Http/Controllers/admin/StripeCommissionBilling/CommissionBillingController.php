<?php

namespace App\Http\Controllers\admin\StripeCommissionBilling;


use App\System\StripeCommissionBilling\StripeCommissionBillingTrait;
use App\UserAccount;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use \Exception;
use Illuminate\Http\JsonResponse;
use Stripe\Subscription;


class CommissionBillingController extends Controller
{
    use  StripeCommissionBillingTrait;

    /**
     * @return Factory|View
     */
    public function stripeCommissionPlansList()
    {
        try {
            $plans = $this->getAllBillingPlans()->data;
            return view('admin.stripe_commission_billing.stripe_commission_plans_list', compact('plans'));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return Factory|View
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCustomer($id)
    {
        $userAccount = UserAccount::with('users')->findOrFail($id);

        if (empty($userAccount->integration_completed_on))
            abort(422, 'Integration not completed yet by this user account.');

        $intent = $this->createPaymentIntent();
        return view('admin.stripe_commission_billing.create_customer', compact('intent','userAccount'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
   public function createStripeBillingCustomerWithOutCard(Request $request)
   {
       try {

           $userAccount = UserAccount::findOrFail($request->userAccountId);

           if (empty($userAccount->integration_completed_on))
               return $this->apiErrorResponse('Integration not completed yet by this user account.',422);

           $this->createStripeCustomerWithOutPaymentMethod($userAccount);

           return $this->apiSuccessResponse(200, [], 'User added as Billing Customer Successfully');
       } catch (Exception $exception) {
           return $this->apiErrorResponse($exception->getMessage(),402);
       }
   }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stripeAllBillingPlansWithUserSubscribedPlans(Request $request)
    {
        try {
            $user_account = UserAccount::findOrFail($request->userAccountId);
            $plans =  $this->getAllBillingPlansWithSubscriptionStatus($this->getAllBillingPlans()->data,
                $this->getUserSubscribedPlans($user_account), $request->subscriptionId);
           return $this->apiSuccessResponse(200, $plans, 'success');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllUserAccountsForCommissionPlan(Request $request)
    {
        try{
            $validator = \Validator::make($request->all(), ['filters' => 'required|array']);
            if ($validator->passes()) {
                $filters = $request->filters;

                if (isset($request->account_constraint) && ($request->account_constraint != 'all')) {
                    if ($request->account_constraint == 'active')
                        $filters['constraints'][] = ['status', '=', config('db_const.user_account.status.active.value')];
                    elseif ($request->account_constraint == 'inactive')
                        $filters['constraints'][] = ['status', '=', config('db_const.user_account.status.deactive.value')];
                    elseif ($request->account_constraint == 'suspended')
                        $filters['constraints'][] = ['status', '=', config('db_const.user_account.status.suspendedbyadmin.value')];
                }

                if (isset($request->integration_constraint) && ($request->integration_constraint != 'all')){
                    if ($request->integration_constraint == 'completed')
                        $filters['constraints'][] = ['integration_completed_on', '!=', null];
                    elseif ($request->integration_constraint == 'incomplete')
                        $filters['constraints'][] = ['integration_completed_on', '=', null];
                }

                $filters['page'] = isset($request->page) ? $request->page : 1;
                return $this->apiSuccessResponse(200,
                    get_collection_by_applying_filters($filters, UserAccount::class), 'success');
            } else {
                return $this->apiErrorResponse(ucwords($validator->errors()->first()), 422, $validator->errors());
            }
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function  listUserSubscriptions(Request $request)
    {
        try {
            $subscriptions  = $this->getUserSubscribedPlans(UserAccount::findOrFail($request->userAccountId));
            /**
             * @var $subscription Subscription
             */
            $trial_days_to_avail = [];
            foreach ($subscriptions as $subscription) {
                if ($subscription->status == 'trialing') {
                    if (!empty($subscription->trial_end)) {
                        $trial_days_to_avail[] = Carbon::createFromTimestamp($subscription->trial_end)->diff(now())->days;
                    }
                }
            }

            $trial_days_to_avail = empty($trial_days_to_avail) ? 0 : min($trial_days_to_avail);

            return $this->apiSuccessResponse(200,
                ['subscriptions' => $subscriptions, 'trial_days_to_avail' => $trial_days_to_avail], //Min Trial Days
                'success'
            );

        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public  function getSubscriptionDetails(Request $request)
    {
        try {
            return $this->apiSuccessResponse(200,
                $this->retrieveSubscription($request->subscriptionId)->items->data, 'success');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public  function deAttachUserSubscriptionPlan(Request $request)
    {
        try {
            $this->cancelSubscriptionItem($request->subscriptionItemId);
            $this->planAttachedStatusUpdate(UserAccount::find($request->userAccountId));
            return $this->apiSuccessResponse(200,
                $this->retrieveSubscription($request->subscriptionId)->items->data, 'Plan De-attached Successfully');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @return JsonResponse
     */
    public  function getAllBillingPlan()
    {
        try {
            return $this->apiSuccessResponse(200, $this->getAllBillingPlans()->data, 'success');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function attachPlansToSubscription(Request $request)
    {
        try {
            foreach ($request->plans as $plan) {
                if ($plan['checked']) {
                    $this->AddNewPlansToSubscription($request->subscriptionId, $plan['id'],
                        $request->userAccountId, ($plan['usage_type'] == 'metered'), $plan['quantity']);
                }
            }
            $subscription = $this->retrieveSubscription($request->subscriptionId)->items->data;
            $this->planAttachedStatusUpdate(UserAccount::find($request->userAccountId));
            return $this->apiSuccessResponse(200, $subscription, 'Successfully Updated!');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public  function saveSubscription(Request $request)
    {
        try {
            foreach ($request->subscriptionItems as $subscriptionItem) {
                if ($subscriptionItem['plan']['usage_type'] === 'licensed') {
                    $this->updateSubscriptionItem($subscriptionItem['id'], ['quantity' => $subscriptionItem['quantity']]);
                }
            }
            return $this->apiSuccessResponse(200,
                $this->retrieveSubscription($request->subscriptionId)->items->data, 'Subscription Updated!');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addSubscription(Request $request)
    {
        try {
            /**
             * @var $userAccount UserAccount
             */
            $userAccount = UserAccount::findOrFail($request->userAccountId);
            if ($userAccount->stripe_customer_id != null) {
                $itemsArr = array();
                foreach ($request->plans as $plan) {
                    if ($plan['checked']) {
                        if ($plan['usage_type'] == 'licensed')
                            $itemsArr[] = ['plan' => $plan['id'], 'quantity' => $plan['quantity']];
                        else
                            $itemsArr[] = ['plan' => $plan['id']];
                    }
                }

                if (count($itemsArr) > 0) {
                    $this->createSubscription($userAccount->stripe_customer_id, $itemsArr, $request->trial_days);
                    $plans = $this->getUserSubscribedPlans($userAccount);
                    return $this->apiSuccessResponse(200, $plans, 'Subscription Added!');
                } else {
                    return $this->apiErrorResponse('No Plan to assign.',402);
                }
            } else {
                return $this->apiErrorResponse("Stripe Customer ID for User Account #  $userAccount->id Not  Created!",402);
            }
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeSubscription(Request $request)
    {
        try {
            $userAccount = UserAccount::findOrFail($request->userAccountId);
            $this->cancelSubscription($request->subscriptionId);
            $plans = $this->getUserSubscribedPlans($userAccount);
            return $this->apiSuccessResponse(200,$plans, 'Subscription Cancelled Successfully, 
            Any pending Invoices will be added to Draft kindly Verify and send manually.');
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createStripeBillingCustomer(Request $request)
    {
        try {
            $userAccount = UserAccount::findOrFail($request->userAccountId);

            if (empty($userAccount->integration_completed_on))
                return $this->apiErrorResponse('Integration not completed yet by this user account.',422);

            if($this->createStripeCustomerWithPaymentMethod($userAccount, $request->paymentMethod)){
                return $this->apiSuccessResponse(200, [],'Customer Card Saved Successfully!');
            } else {
                return $this->apiErrorResponse('Fail to Save Card Details',501);
            }
        } catch (Exception $e) {
            return $this->apiErrorResponse($e->getMessage(),402);
        }
    }

    private function planAttachedStatusUpdate(UserAccount $user_account)
    {
        try{
            $subscription = $this->getUserSubscribedPlans($user_account);
            if (!is_null($subscription) && is_array($subscription) && count($subscription))
                $plan_attached_status = 1;
            else
                $plan_attached_status = 0;
            $user_account->update(['plan_attached_status' => $plan_attached_status]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage(). ' --  ' . json_encode($exception->getTraceAsString()));

        }
    }

    /**
     * @return string
     */
    public function createCoupon() {
        return $this->generateCoupon(100, 'Account Suspended', 'forever');
    }
}
