<?php


namespace App\System\StripeCommissionBilling;

use App\UserAccount;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Plan;
use Stripe\Subscription;
use \Exception;

use \UnexpectedValueException;
use App\Events\StripeCommissionBilling\StripeCommissionWebHookEvent;


class StripeCommissionBilling extends StripeCommissionBillingBase implements StripeCommissionBillingInterface
{


    use StripeCommissionBillingTrait;
    /**
     * StripeCommissionBilling constructor.
     */
    public function __construct()
    {
        $this->setBillingApiKey(config(get_billing_config_file_name().'.secret_key'));
        $this->setEndPointSecretKey(config(get_billing_config_file_name().'.commission-billing-endpoint-secret'));
    }

    /**
     * @param Request $request
     * @return ResponseFactory | Response
     */
    public function ListenStripeEvent(Request $request)
    {
        try {
            $signatureHeader = $request->server('HTTP_STRIPE_SIGNATURE');
            $payload         = $request->getContent();
            $event           = $this->validateStripeRequestSignature($payload, $signatureHeader);
            return $this->handleEvent($event);
        } catch(UnexpectedValueException $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Reason'=>'Invalid Payload']);
            return response(['Invalid payload'], 402);
        } catch(SignatureVerificationException $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Reason'=>'Invalid Signature']);
            return response(['Invalid signature'], 402);
        }
    }


    /**
     * @param Event $event
     * @return ResponseFactory | Response
     */
    public function handleEvent(Event $event)
    {
        switch ($event->type) {
            case 'customer.subscription.trial_will_end':
            case 'invoice.upcoming':
            case 'customer.created':
            case 'customer.updated':
            case 'invoice.payment_failed':
            case 'customer.subscription.updated' :
            case 'invoice.payment_succeeded':
                StripeCommissionWebHookEvent::dispatch($event);
                return response(['ok'], 200);
                break;
            default:
                return response(["Unexpected event type, 
                $event->type event not implemented on Commission WebHook end point Yet."], 200);
                break;
        }
    }

    /**
     * @param UserAccount $userAccount
     * @return Subscription
     * @throws Exception
     */
     public  function createBillingCustomerWithNoCardAndAddDefaultBillingPlan(UserAccount $userAccount) {
         try {
             $customer = $this->createStripeCustomerWithOutPaymentMethod($userAccount);
             $subscription = $this->addDefaultCommissionBillingSubscriptionPlan($customer->id);
             $userAccount->update(['plan_attached_status' => 1,
                 'plan_attached_status_last_sync' => now()->toDateTimeString()]);
             return $subscription;
         } catch (Exception $exception) {
             Log::error($exception->getMessage(), ['StackTrace' => $exception->getTraceAsString()]);
             return null;
         }
     }

    /**
     * @param $userAccount
     * @return bool
     */
    public function isDefaultPlanAttachedToCustomer($userAccount)
    {
        try {
            /**
             * @var $plan Plan
             */
            $plans = $this->getUserSubscribedPlans($userAccount);
            foreach ($plans as $plan) {
                if ($plan->id == config(get_billing_config_file_name().'.plans.plan_default'))
                    return true;
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), ['StackTrace' => $exception->getTraceAsString()]);
        }
        return false;
    }

}