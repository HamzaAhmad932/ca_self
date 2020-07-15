<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/29/18
 * Time: 12:33 PM
 */

namespace App\System\PaymentGateway\Spreedly;


use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\Customer;
use App\System\PaymentGateway\Models\GateWay;
use App\System\PaymentGateway\Models\Transaction;

class ParseSpreedly {

    public function gateways_options(string $response) {

        $response = json_decode($response, true);

        if(key_exists('gateways', $response)) {
            $op = array();
            for($i = 0; $i < count($response['gateways']); $i++) {
                $pg = $response['gateways'][$i];
                $op[] = $this->parseSingleGateway($pg);
            }
            return $op;
        }

        return array();
    }

    /**
     * @param string $response
     * @return array
     */
    public function gateways(string $response) {

        $response = json_decode($response, true);

        if(key_exists('gateways', $response)) {
            $op = array();
            for($i = 0; $i < count($response['gateways']); $i++) {
                $pg = $response['gateways'][$i];
                $op[] = $this->parseSingleGateway($pg);
            }
            return $op;
        }

        return array();

    }

    public function parseSingleGateway(array $pg, GateWay &$gw = null) {

        if($gw === null)
            $gw = new GateWay();

        if(key_exists('statement_descriptor', $pg))
            $gw->statement_descriptor = $pg['statement_descriptor'];

        if(key_exists('gateway_type', $pg))
            $gw->gatewayType = $pg['gateway_type'];

        if(key_exists('name', $pg))
            $gw->name = $pg['name'];

        if(key_exists('homepage', $pg))
            $gw->homepage = $pg['homepage'];

        if(key_exists('company_name', $pg))
            $gw->companyName = $pg['company_name'];
        
        if(key_exists('display_name', $pg))
            $gw->displayName = $pg['display_name'];

        if(key_exists('auth_modes', $pg)) {
            for($a = 0; $a < count($pg['auth_modes']); $a++) {

                if(key_exists('name', $pg['auth_modes'][$a]))
                    $gw->authModeName = $pg['auth_modes'][$a]['name'];

                if(key_exists('auth_mode_type', $pg['auth_modes'][$a]))
                    $gw->authModeType = $pg['auth_modes'][$a]['auth_mode_type'];

                if(key_exists('credentials', $pg['auth_modes'][$a])) {
                    for($f = 0; $f < count($pg['auth_modes'][$a]['credentials']); $f++) {

                        $cre = new CredentialFormField();
                        $cre->name = $pg['auth_modes'][$a]['credentials'][$f]['name'];
                        $cre->label = $pg['auth_modes'][$a]['credentials'][$f]['label'];
                        $cre->safe = $pg['auth_modes'][$a]['credentials'][$f]['safe'];

                        if(key_exists('type', $pg['auth_modes'][$a]['credentials'][$f]))
                            $cre->type = $pg['auth_modes'][$a]['credentials'][$f]['type'];

                        if(key_exists('value', $pg['auth_modes'][$a]['credentials'][$f]))
                            $cre->value = $pg['auth_modes'][$a]['credentials'][$f]['value'];

                        if(key_exists('state', $pg['auth_modes'][$a]['credentials'][$f]))
                            $cre->state = $pg['auth_modes'][$a]['credentials'][$f]['state'];

                        if(key_exists('url', $pg['auth_modes'][$a]['credentials'][$f]))
                            $cre->url = $pg['auth_modes'][$a]['credentials'][$f]['url'];

                        if(key_exists('desc', $pg['auth_modes'][$a]['credentials'][$f]))
                            $cre->desc = $pg['auth_modes'][$a]['credentials'][$f]['desc'];

                        $gw->credentials[] = $cre;
                    }
                }
            }
        }

        if(key_exists('characteristics', $pg))
            for($c = 0; $c < count($pg['characteristics']); $c++)
                $gw->characteristics[] = $pg['characteristics'][$c];

        if(key_exists('payment_methods', $pg))
            for($p = 0; $p < count($pg['payment_methods']); $p++)
                $gw->paymentMethods[] = $pg['payment_methods'][$p];

        if(key_exists('gateway_specific_fields', $pg))
            for($g = 0; $g < count($pg['gateway_specific_fields']); $g++)
                $gw->gatewaySpecificFields[] = $pg['gateway_specific_fields'][$g];

        if(key_exists('supported_countries', $pg))
            for($s = 0; $s < count($pg['supported_countries']); $s++)
                $gw->supportedCountries[] = $pg['supported_countries'][$s];

        if(key_exists('supported_cardtypes', $pg))
            for($c = 0; $c < count($pg['supported_cardtypes']); $c++)
                $gw->supportedCardTypes[] = $pg['supported_cardtypes'][$c];

        if(key_exists('regions', $pg))
            for($r = 0; $r < count($pg['regions']); $r++)
                $gw->regions[] = $pg['regions'][$r];

        if(key_exists('token', $pg))
            $gw->token = $pg['token'];

        if(key_exists('description', $pg))
            $gw->description = $pg['description'];

        if(key_exists('state', $pg))
            $gw->state = $pg['state'];

        if(key_exists('created_at', $pg))
            $gw->created_at = $pg['created_at'];

        if(key_exists('updated_at', $pg))
            $gw->updated_at = $pg['updated_at'];

        if(key_exists('redacted', $pg))
            $gw->redacted = $pg['redacted'];

        if(key_exists('isStripeConnect', $pg))
            $gw->isStripeConnect = $pg['isStripeConnect'];

        if(key_exists('isStripeExpress', $pg))
            $gw->isStripeExpress = $pg['isStripeExpress'];

        return $gw;
    }

    public function parseAuthorizeTransaction(string $response) {

        $res = json_decode($response, true);
        if(key_exists('transaction', $res)) {

            $tran = new Transaction();

            if(key_exists('created_at', $res['transaction']))
                $tran->created_at = $res['transaction']['created_at'];

            if(key_exists('updated_at', $res['transaction']))
                $tran->updated_at = $res['transaction']['updated_at'];

            if(key_exists('succeeded', $res['transaction']))
                $tran->status = $res['transaction']['succeeded'];

            if(key_exists('state', $res['transaction']))
                $tran->state = $res['transaction']['state'];

            /**
             * Spreedly gives value 'pending' for 3ds transactions and stripe returns 'requires_action'
             * So here we are making this value common.
             */
            if($tran->state == 'pending')
                $tran->state = Transaction::STATE_REQUIRE_ACTION;

            if(key_exists('checkout_url', $res['transaction']))
                if($res['transaction']['checkout_url'] != null)
                    $tran->authenticationUrl = $res['transaction']['checkout_url'];

            if(key_exists('token', $res['transaction']))
                $tran->token = $res['transaction']['token'];

            if(key_exists('transaction_type', $res['transaction']))
                $tran->type = $res['transaction']['transaction_type'];

            if(key_exists('order_id', $res['transaction']))
                $tran->order_id = $res['transaction']['order_id'];

            if(key_exists('amount', $res['transaction'])) {
                if($res['transaction']['amount'] != 0)
                $tran->amount = ((double)$res['transaction']['amount']) / 100;
            }

            if(key_exists('currency_code', $res['transaction']))
                $tran->currency_code = $res['transaction']['currency_code'];

            if(key_exists('message', $res['transaction']))
                $tran->message = $res['transaction']['message'];

            if(key_exists('description', $res['transaction']))
                $tran->description = $res['transaction']['description'];


            return $tran;
        }

        return null;

    }

    public function parsePaymentMethod(string $response) {

        $res = json_decode($response, true);

        if(!key_exists('transaction', $res))
            return null;

        if(!key_exists('payment_method', $res['transaction']))
            return null;

        $cus = new Customer();

        if(key_exists('message', $res['transaction']))
            $cus->message = $res['transaction']['message'];

        if(key_exists('succeeded', $res['transaction']))
            $cus->succeeded = $res['transaction']['succeeded'];

        if(key_exists('state', $res['transaction']))
            $cus->state = $res['transaction']['state'];

        $pay = $res['transaction']['payment_method'];

        if(key_exists('token', $pay))
            $cus->token = $pay['token'];

        if(key_exists('created_at', $pay))
            $cus->created_at = $pay['created_at'];

        if(key_exists('updated_at', $pay))
            $cus->updated_at = $pay['updated_at'];

        if(key_exists('email', $pay))
            $cus->email = $pay['email'];

        if(key_exists('data', $pay))
            $cus->data = $pay['data'];

        if(key_exists('last_four_digits', $pay))
            $cus->last_four_digits = $pay['last_four_digits'];

        if(key_exists('first_six_digits', $pay))
            $cus->first_six_digits = $pay['first_six_digits'];

        if(key_exists('card_type', $pay))
            $cus->card_type = $pay['card_type'];

        if(key_exists('first_name', $pay))
            $cus->first_name = $pay['first_name'];

        if(key_exists('last_name', $pay))
            $cus->last_name = $pay['last_name'];

        if(key_exists('month', $pay))
            $cus->month = $pay['month'];

        if(key_exists('year', $pay))
            $cus->year = $pay['year'];

        return $cus;

    }
}