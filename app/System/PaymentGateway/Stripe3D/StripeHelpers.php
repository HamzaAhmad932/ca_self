<?php


namespace App\System\PaymentGateway\Stripe3D;


use App\System\PaymentGateway\Exceptions\GatewayException;
use App\System\PaymentGateway\Models\Card;
use App\System\PaymentGateway\Models\Transaction;
use App\System\PaymentGateway\PaymentGateway;
use Exception;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

trait StripeHelpers {

    private function is3D_secureException(string $message) {
        if($message != null) {
            $strPosition = strpos($message, 'This transaction requires authentication');
            if($strPosition !== false)
                return true;
        }
        return false;
    }

    /**
     * @param PaymentIntent $intent
     * @param string $type
     * @param Card $card
     * @return Transaction
     */
    private function getTransactionObject(PaymentIntent $intent, string $type, Card $card) {

        $transaction = new Transaction();
        $transaction->paymentIntentId = $intent->id;

        if ($intent->status == 'requires_source_action' && in_array($intent->next_action->type, ['redirect_to_url', 'use_stripe_sdk'])) {
            # Tell the client to handle the action
            $transaction->state = 'requires_action';
            $transaction->status = false;

        } elseif (in_array($intent->status, ['succeeded', 'requires_capture'])) {
            # The payment didn't need any additional actions and completed!
            $transaction->state = $intent->status;
            $transaction->status = true;

        } elseif($intent->status == 'requires_source' && $intent->last_payment_error != null) {
            $transaction->state = Transaction::STATE_AUTHENTICATION_FAILED;
            $transaction->status = false;
            $transaction->message = $intent->last_payment_error->message;
            $transaction->exceptionMessage = $intent->last_payment_error->code . ' Guest might had not authenticated this transaction!';

        } else {
            # Invalid status
            $transaction->state = 'Invalid status';
            $transaction->status = false;
            $transaction->message = 'Invalid PaymentIntent status';
            $transaction->exceptionMessage = 'Invalid PaymentIntent status';
        }

        $transaction->fullResponse = json_encode($intent);

        $obj = json_decode($transaction->fullResponse, true);

        if(key_exists('charges', $obj)) {
            if($obj['charges']['object'] == 'list' && $obj['charges']['total_count'] >= 1) {

                $data = $obj['charges']['data'][0];

                $transaction->token = $data['id'];
                $transaction->message = $data['outcome']['seller_message'];
                // $transaction->status = $data['paid'] && $data['captured'] ? true : false;

            }
        }

        /**
         * @var $intent->next_action StripeObject
         * @var $intent->next_action->redirect_to_url StripeObject
         */
        if($intent->next_action) {
            if($intent->next_action->redirect_to_url) {
                if(!empty($intent->next_action->redirect_to_url->url)) {
                    $transaction->authenticationUrl = $intent->next_action->redirect_to_url->url;
                }
            }
        }

        $transaction->payment_intent_client_secret = $intent->client_secret;
        $transaction->created_at = $obj['created'];

        $transaction->amount = $card->amount;

        if(!empty($card->order_id))
            $transaction->order_id = $card->order_id;

        $transaction->currency_code = $card->currency;

        if(!empty($card->general_description)) {
            $transaction->description = $card->general_description;

        } elseif(!empty($intent->description)) {
            $transaction->description = $intent->description;
        }

        $transaction->type = $type;

        return $transaction;

    }

    /**
     * @param Card $card
     * @return array
     */
    private function makeParametersFromCard(Card $card) {

        $_c = array(
            "type" => "card",
            "card" => array(
                "number" => $card->cardNumber,
                "exp_month" => $card->expiryMonth,
                "exp_year" => $card->expiryYear,
            )
        );

        $name = "";

        if(!empty($card->firstName))
            $name = $card->firstName;

        if(!empty($card->lastName))
            $name .= " " . $card->lastName;

        if(!empty($name))
            $_c['billing_details']['name'] = $name;

        if(!empty($card->eMail))
            $_c['billing_details']['email'] = $card->eMail;

        if(!empty($card->phone))
            $_c['billing_details']['phone'] = $card->phone;

        if(!empty($card->city))
            $_c['billing_details']['address']['city'] = $card->city;

        if(!empty($card->country))
            $_c['billing_details']['address']['country'] = $card->country;

        if(!empty($card->address1))
            $_c['billing_details']['address']['line1'] = $card->address1;

        if(!empty($card->address2))
            $_c['billing_details']['address']['line2'] = $card->address2;

        if(!empty($card->postalCode))
            $_c['billing_details']['address']['postal_code'] = $card->postalCode;

        if(!empty($card->state))
            $_c['billing_details']['address']['state'] = $card->state;

        /*
         * NOTE: Order Id must be sent inside metadata with key of 'order_id'
         */

        if(!empty($card->metadata)) {
            $_c['metadata'] = $card->metadata;
//            $_c['metadata']['user_account_id'] = $this->userAccountId;

        } //else { $_c['metadata'] = ['user_account_id' => $this->userAccountId]; }

        if (isset($card->cvvCode) && $card->cvvCode > 0)
            $_c['card']['cvc'] = $card->cvvCode;

        return $_c;
    }

    /**
     * @param Card $card
     * @param string $paymentMethodId |null
     * @return array
     * @throws GatewayException
     */
    private function makeParametersForPaymentIntent(Card $card, string $paymentMethodId = null) {

        $paymentIntentParams = [
            'amount' => $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->amount),
            'currency' => $card->currency,
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'off_session' => true,
//            'setup_future_usage' => 'off_session',
//            'return_url' => route('payment-confirmation', $this->userAccountId)
        ];

        if($card->applicationFee > 0)
            $paymentIntentParams['application_fee_amount'] = $this->getAmountInLowestPositiveUnitOfCurrency($card->currency, $card->applicationFee);

        if($paymentMethodId != null)
            $paymentIntentParams['payment_method'] = $paymentMethodId;

        if(!empty($card->general_description))
            $paymentIntentParams['description'] = $card->general_description;

        if(!empty($card->statement_descriptor))
            $paymentIntentParams['statement_descriptor'] = $card->statement_descriptor;

        if(!empty($card->metadata)) {
            $_c['metadata'] = $card->metadata;
//            $_c['metadata']['user_account_id'] = $this->userAccountId;

        } // else { $_c['metadata'] = ['user_account_id' => $this->userAccountId]; }

        return $paymentIntentParams;

    }

    /**
     * @param Exception $e
     * @return GatewayException
     */
    private function handleStripeExceptions(Exception $e) {

        $errorMessage = '';
        $errorCode = 0;
        $declineCode = '';
        $description = '';
        $nextStep = '';
        $type = '';
        $httpStatus = 0;
        $generalCode = '';
        $retryAble = true;
        $reportToGuest = true;

        if($e instanceof CardException ) {
            $errorMessage .= $e->getMessage();
            $errorCode = $this->is3D_secureException($e->getMessage()) ? PaymentGateway::ERROR_CODE_3D_SECURE : PaymentGateway::ERROR_CODE_CARD;

            $httpStatus = $e->getHttpStatus();
            $type = $e->getError()->type;
            $generalCode = $e->getError()->code;
            $declineCode = $e->getDeclineCode();

            /* *
             * https://stripe.com/docs/declines/codes
             * */
            $details = $this->getDeclineCodeDetails($declineCode);
            $description = $details['description'];
            $nextStep = $details['nextStep'];
            $retryAble = $details['retryAble'];
            $reportToGuest = $details['reportToGuest'];


        } elseif($e instanceof InvalidRequestException) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_INVALID_REQUEST;
            $type = 'InvalidRequestException';

        } elseif($e instanceof RateLimitException) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_RATE_LIMIT;
            $type = 'RateLimitException';

        } elseif($e instanceof AuthenticationException) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_AUTHENTICATION;
            $type = 'AuthenticationException';

        } elseif($e instanceof ApiConnectionException) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_CONNECTION;
            $type = 'ApiConnectionException';

        } elseif($e instanceof ApiErrorException) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_API_ERROR;
            $type = 'ApiErrorException';

        } elseif($e instanceof Exception) {
            $errorMessage .= $e->getMessage();
            $errorCode = PaymentGateway::ERROR_CODE_GENERAL;
            $type = 'Exception';
        }

        $pgException = new GatewayException($errorMessage, $errorCode);

        $pgException->setDeclineCode($declineCode);
        $pgException->setDescription($description);
        $pgException->setNextStep($nextStep);
        $pgException->setExceptionType($type);
        $pgException->setHttpStatus($httpStatus);
        $pgException->setGeneralCode($generalCode);
        $pgException->setRetryAble($retryAble);
        $pgException->setReportToGuest($reportToGuest);

        return $pgException;
    }

    private function makeParametersForCustomer(Card $card, PaymentMethod $paymentMethod) {

        $customerParams = [
            'payment_method' => $paymentMethod->id,
//            'invoice_settings' => ['default_payment_method' => $paymentMethod->id]
        ];

        if($card->isNameSet())
            $customerParams['name'] = $card->getName();

        if(!empty($card->general_description))
            $customerParams['description'] = $card->general_description;

        if(!empty($card->eMail))
            $customerParams['email'] = $card->eMail;

        if(!empty($card->metadata))
            $customerParams['metadata'] = $card->metadata;

        if(!empty($card->phone))
            $customerParams['phone'] = $card->phone;

        if(!empty($card->address1)) { // required

            $address = [];

            $address['line1'] = $card->address1;

            if(!empty($card->address2))
                $address['line2'] = $card->address2; // optional

            if(!empty($card->city))
                $address['city'] = $card->city; // optional

            if(!empty($card->country))
                $address['country'] = $card->country; // optional

            if(!empty($card->postalCode))
                $address['postal_code'] = $card->postalCode; // optional

            if(!empty($card->state))
                $address['state'] = $card->state; // optional

            $customerParams['address'] = $address;
        }


        return $customerParams;
    }

    private function getDeclineCodeDetails($decline_code) : array {
        $op = ['description' => 'The card has been declined for an unknown reason.', 'nextStep' => '', 'retryAble' => false, 'reportToGuest' => false];
        $details = [
            'authentication_required' => [
                'description' => 'The card was declined as the transaction requires authentication.',
                'nextStep' => 'The customer should try again and authenticate their card when prompted during the transaction.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'approve_with_id' => [
                'description' => 'The payment cannot be authorized.',
                'nextStep' => 'The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.',
                'retryAble' => true, 'reportToGuest' => true
            ],
            'call_issuer' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'card_not_supported' => [
                'description' => 'The card does not support this type of purchase.',
                'nextStep' => 'The customer needs to contact their card issuer to make sure their card can be used to make this type of purchase.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'card_velocity_exceeded' => [
                'description' => 'The customer has exceeded the balance or credit limit available on their card.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'currency_not_supported' => [
                'description' => 'The card does not support the specified currency.',
                'nextStep' => 'The customer needs to check with the issuer whether the card can be used for the type of currency specified.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'do_not_honor' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'do_not_try_again' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'duplicate_transaction' => [
                'description' => 'A transaction with identical amount and credit card information was submitted very recently.',
                'nextStep' => 'Check to see if a recent payment already exists.',
                'retryAble' => false, 'reportToGuest' => false
            ],
            'expired_card' => [
                'description' => 'The card has expired.',
                'nextStep' => 'The customer should use another card.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'fraudulent' => [
                'description' => 'The payment has been declined as Stripe suspects it is fraudulent.',
                'nextStep' => 'Do not report more detailed information to your customer. Instead, present as you would the generic_decline described below.',
                'retryAble' => false, 'reportToGuest' => false
            ],
            'generic_decline' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'incorrect_number' => [
                'description' => 'The card number is incorrect.',
                'nextStep' => 'The customer should try again using the correct card number.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'incorrect_cvc' => [
                'description' => 'The CVC number is incorrect.',
                'nextStep' => 'The customer should try again using the correct CVC.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'incorrect_pin' => [
                'description' => 'The PIN entered is incorrect. This decline code only applies to payments made with a card reader. ',
                'nextStep' => 'The customer should try again using the correct PIN.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'incorrect_zip' => [
                'description' => 'The ZIP/postal code is incorrect.',
                'nextStep' => 'The customer should try again using the correct billing ZIP/postal code.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'insufficient_funds' => [
                'description' => 'The card has insufficient funds to complete the purchase.',
                'nextStep' => 'The customer should use an alternative payment method.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_account' => [
                'description' => 'The card, or account the card is connected to, is invalid.',
                'nextStep' => 'The customer needs to contact their card issuer to check that the card is working correctly.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_amount' => [
                'description' => 'The payment amount is invalid, or exceeds the amount that is allowed.',
                'nextStep' => 'If the amount appears to be correct, the customer needs to check with their card issuer that they can make purchases of that amount.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_cvc' => [
                'description' => 'The CVC number is incorrect.',
                'nextStep' => 'The customer should try again using the correct CVC.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_expiry_year' => [
                'description' => 'The expiration year invalid.',
                'nextStep' => 'The customer should try again using the correct expiration date.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_number' => [
                'description' => 'The card number is incorrect.',
                'nextStep' => 'The customer should try again using the correct card number.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'invalid_pin' => [
                'description' => 'The PIN entered is incorrect. This decline code only applies to payments made with a card reader.',
                'nextStep' => 'The customer should try again using the correct PIN.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'issuer_not_available' => [
                'description' => 'The card issuer could not be reached, so the payment could not be authorized.',
                'nextStep' => 'The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.',
                'retryAble' => true, 'reportToGuest' => true
            ],
            'lost_card' => [
                'description' => 'The payment has been declined because the card is reported lost.',
                'nextStep' => 'The specific reason for the decline should not be reported to the customer. Instead, it needs to be presented as a generic decline.',
                'retryAble' => false, 'reportToGuest' => false
            ],
            'merchant_blacklist' => [
                'description' => 'The payment has been declined because it matches a value on the Stripe user\'s block list.',
                'nextStep' => 'Do not report more detailed information to your customer. Instead, present as you would the generic_decline described above.',
                'retryAble' => false, 'reportToGuest' => false
            ],
            'new_account_information_available' => [
                'description' => 'The card, or account the card is connected to, is invalid.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'no_action_taken' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'not_permitted' => [
                'description' => 'The payment is not permitted.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'offline_pin_required' => [
                'description' => 'The card has been declined as it requires a PIN.',
                'nextStep' => 'The customer should try again by inserting their card and entering a PIN.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'online_or_offline_pin_required' => [
                'description' => 'The card has been declined as it requires a PIN.',
                'nextStep' => 'If the card reader supports Online PIN, the customer should be prompted for a PIN without a new transaction being created. If the card reader does not support Online PIN, the customer should try again by inserting their card and entering a PIN.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'pickup_card' => [
                'description' => 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'pin_try_exceeded' => [
                'description' => 'The allowable number of PIN tries has been exceeded.',
                'nextStep' => 'The customer must use another card or method of payment.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'processing_error' => [
                'description' => 'An error occurred while processing the card.',
                'nextStep' => 'The payment should be attempted again. If it still cannot be processed, try again later.',
                'retryAble' => true, 'reportToGuest' => true
            ],
            'reenter_transaction' => [
                'description' => 'The payment could not be processed by the issuer for an unknown reason.',
                'nextStep' => 'The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.',
                'retryAble' => true, 'reportToGuest' => true
            ],
            'restricted_card' => [
                'description' => 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'revocation_of_all_authorizations' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'revocation_of_authorization' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'security_violation' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'service_not_allowed' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'stolen_card' => [
                'description' => 'The payment has been declined because the card is reported stolen.',
                'nextStep' => 'The specific reason for the decline should not be reported to the customer. Instead, it needs to be presented as a generic decline.',
                'retryAble' => false, 'reportToGuest' => false
            ],
            'stop_payment_order' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer should contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'testmode_decline' => [
                'description' => 'A Stripe test card number was used.',
                'nextStep' => 'A genuine card must be used to make a payment.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'transaction_not_allowed' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'The customer needs to contact their card issuer for more information.',
                'retryAble' => false, 'reportToGuest' => true
            ],
            'try_again_later' => [
                'description' => 'The card has been declined for an unknown reason.',
                'nextStep' => 'Ask the customer to attempt the payment again. If subsequent payments are declined, the customer should contact their card issuer for more information.',
                'retryAble' => true, 'reportToGuest' => true
            ],
            'withdrawal_count_limit_exceeded' => [
                'description' => 'The customer has exceeded the balance or credit limit available on their card. ',
                'nextStep' => 'The customer should use an alternative payment method.',
                'retryAble' => false, 'reportToGuest' => true
            ]
        ];

        if(!empty($decline_code))
            if(key_exists($decline_code, $details))
                return $details[$decline_code];

        return $op;
    }

}