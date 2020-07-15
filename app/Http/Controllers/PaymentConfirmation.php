<?php

namespace App\Http\Controllers;


use App\Jobs\PaymentConfirmationJob;
use App\System\PaymentGateway\Models\Transaction;
use App\TransactionInit;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Error\SignatureVerification;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentConfirmation extends Controller
{

    const INTENT_FOR_CHARGE = 1;
    const INTENT_FOR_AUTH = 2;
    private $isHook = false;

    /**
     * Guest must land here via link from email. one of the place this email is send is from FirstAttemptTransactionChargeJob file.
     *
     * @param Request $request
     * @param $userAccountId
     * @param $bookingInfoId
     * @param $transactionId
     */
    public function render3dsVerificationFormForSpreedly(Request $request, $userAccountId, $bookingInfoId, $transactionId) {

        if(!empty($userAccountId) && !empty($bookingInfoId) && !empty($transactionId)) {

            $transactionInit = TransactionInit::where('id', $transactionId)
                ->where('user_account_id', $userAccountId)->where('booking_info_id', $bookingInfoId)->first();

            if($transactionInit != null) {

                /**
                 * @var $transaction Transaction
                 */

                $transaction = $transactionInit->last_success_trans_obj;

                if(!empty($transaction->checkout_form))
                    exit($transaction->checkout_form);
                else
                    abort(403, 'Invalid Authentication Checkout Form, Please contact Support.');


            } else {
                abort(403, 'Invalid Request Parameters');
            }

        } else {
            abort(403, 'Invalid Request Parameters');
        }
    }

    /**
     * For 3D secure when guest Authenticates or denies request.
     * Route defined in web.php
     * @param Request $request
     * @param $userAccountID
     */
    public function afterAuthentication(Request $request, $userAccountID) {

        PaymentConfirmationJob::dispatch(
            PaymentConfirmationJob::SOURCE_STRIPE_REDIRECT_AFTER_3DS,
            $request,
            $userAccountID,
            null,
            null,
            null)->delay(now()->addSeconds(5));

        abort(403, 'Request in Process, Visit Guest Portal');

    }

    public function afterAuthenticationCA(Request $request, $userAccountID) {

    }

    public function spreedlyWebHook(Request $request) {

    }

    /**
     * When Payment Intent is Success or Failed, Stripe WebHook is received here.
     * Route defined in api.php
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function stripeWebHook(Request $request) {

        $this->isHook = true;

        Stripe::setApiKey(config('db_const.auth_keys.stripe.secret_key'));
        $endpoint_secret = config('db_const.auth_keys.stripe.stripe-endpoint-secret');

        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
        $event = null;

        try {

            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

        } catch(\UnexpectedValueException $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Reason'=>'Invalid Payload']);
            return response(['Invalid payload'], 403);

        } catch(SignatureVerification $e) {
            Log::error($e->getMessage(), ['File'=>__FILE__, 'Function'=>__FUNCTION__, 'Reason'=>'Invalid Signature']);
            return response(['Invalid signature'], 403);
        }

        PaymentConfirmationJob::dispatch(
            PaymentConfirmationJob::SOURCE_STRIPE_WEB_HOOK,
            null,
            null,
            $event,
            null,
            null)->delay(now()->addSeconds(5));

        return response(['OK'], 200);

    }

}
