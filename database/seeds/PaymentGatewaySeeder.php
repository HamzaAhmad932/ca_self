<?php

use App\PaymentGatewayForm;
use App\PaymentGatewayParent;
use App\System\PaymentGateway\Models\CredentialFormField;
use App\System\PaymentGateway\Models\GateWay;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/17/19
 * Time: 3:22 PM
 */

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $this->spreedly(1);
        $this->stripeConnect(1);

//         $this->stripeWithKeys(0);
//        $this->payfirma(0);
//        $this->moneris(0);
//        $this->helcim(0);

    }

    function spreedly(int $status) {
        /**
         * NOTE: Do not remove it, Just empty env_key & api_secret values not keys !!
         */
        $spreedlyAsParent = PaymentGatewayParent::create(['name' => 'CV Gateway',
            'backend_name' => 'pg_form_spreedly',
            'credentials' => json_encode(array(
                'env_key' => config('db_const.auth_keys.spreedly.env_key'),
                'api_secret' => config('db_const.auth_keys.spreedly.api_secret'))),
            'status' => $status]);

        // ================= Test Gateway for Spreedly ===============
        if (config('app.env') === 'local' || config('app.debug') == true) {

            PaymentGatewayForm::create([
                'logo' => 'no_image.png',
                'name' => 'CV Test Gateway',
                'backend_name' => 'pg_form_spreedly',
                'gateway_form' => '{"gateway_type":"test","name":"CV Test Gateway","auth_modes":[{"auth_mode_type":null,"name":null,"credentials":[]}],"characteristics":["purchase","authorize","capture","credit","general_credit","void","verify","reference_purchase","purchase_via_preauthorization","offsite_purchase","offsite_authorize","3dsecure_purchase","3dsecure_authorize","store","remove","reference_authorization"],"payment_methods":["credit_card","sprel","third_party_token","bank_account","apple_pay","android_pay","google_pay"],"gateway_specific_fields":[],"supported_countries":[],"supported_cardtypes":[],"regions":[],"homepage":null,"company_name":null,"token":"","description":null,"state":"retained","created_at":"","updated_at":"","redacted":false}',
                'payment_gateway_parent_id' => $spreedlyAsParent->id,
                'status' => $status]);
        }
    }

    function stripeConnect(int $status) {
        $stripeConnect = new GateWay();
        $stripeConnect->name = "Stripe";

        $accountId = new CredentialFormField();
        $accountId->name = config('db_const.pg_form_stripe_connect.credentials.0.name');
        $accountId->safe = true;
        $accountId->label = config('db_const.pg_form_stripe_connect.credentials.0.label');
        $accountId->url = config('db_const.pg_form_stripe_connect.credentials.0.url');
        $accountId->type = CredentialFormField::TYPE_BUTTON;
        $accountId->state = CredentialFormField::STATE_SHOW;
        $accountId->value = config('db_const.pg_form_stripe_connect.credentials.0.value');

        $scPub = new CredentialFormField();
        $scPub->name = config('db_const.pg_form_stripe_connect.credentials.1.name');
        $scPub->safe = true;
        $scPub->label = config('db_const.pg_form_stripe_connect.credentials.1.label');
        $scPub->value = '';
        $scPub->type = CredentialFormField::TYPE_TEXT;
        $scPub->state = CredentialFormField::STATE_HIDDEN;

        $stripeConnect->credentials = array($accountId, $scPub);

        PaymentGatewayForm::create(['name' => $stripeConnect->name,
            'logo' => 'stripe.png',
            'backend_name' => 'pg_form_stripe_connect',
            'gateway_form' => json_encode($stripeConnect),
            'payment_gateway_parent_id' => 0,
            'status' => $status]);
    }

    function stripeWithKeys(int $status) {
        $stripe = new GateWay();
        $stripe->name = "Stripe Keys";

        $public = new CredentialFormField();
        $public->name = 'publishable_key';
        $public->safe = true;
        $public->label = 'Publishable key';

        $key = new CredentialFormField();
        $key->name = 'secret_key';
        $key->safe = false;
        $key->label = 'Secret key';
        $key->type = CredentialFormField::TYPE_TEXT;

        $stripe->credentials = array($key, $public);

        PaymentGatewayForm::create(['name' => $stripe->name,
            'logo' => 'stripe.png',
            'backend_name' => 'pg_form_stripe',
            'gateway_form' => json_encode($stripe),
            'payment_gateway_parent_id'=>0,
            'status' => $status]);
    }

    function payfirma(int $status) {
        $stripe = new GateWay();
        $stripe->name = "Payfirma";

        $public = new CredentialFormField();
        $public->name = 'publishable_key';
        $public->safe = true;
        $public->label = 'Publishable key';

        $key = new CredentialFormField();
        $key->name = 'secret_key';
        $key->safe = false;
        $key->label = 'Secret key';

        $stripe->credentials = array($key, $public);

        PaymentGatewayForm::create(['name' => 'Payfirma',
            'logo' => 'payfirma.png',
            'backend_name' => 'pg_form_payfirma',
            'gateway_form' => json_encode($stripe),
            'payment_gateway_parent_id'=>0,
            'status' => $status]);
    }

    function moneris(int $status) {
        $stripe = new GateWay();
        $stripe->name = "Moneris";

        $public = new CredentialFormField();
        $public->name = 'publishable_key';
        $public->safe = true;
        $public->label = 'Publishable key';

        $key = new CredentialFormField();
        $key->name = 'secret_key';
        $key->safe = false;
        $key->label = 'Secret key';

        $stripe->credentials = array($key, $public);

        PaymentGatewayForm::create(['name' => 'Moneris',
            'logo' => 'moneris.png',
            'backend_name' => 'pg_form_moneris',
            'gateway_form' => json_encode($stripe),
            'payment_gateway_parent_id'=>0,
            'status' => $status]);
    }

    function helcim(int $status) {
        $stripe = new GateWay();
        $stripe->name = "Helcim";

        $public = new CredentialFormField();
        $public->name = 'publishable_key';
        $public->safe = true;
        $public->label = 'Publishable key';
        $public->type = \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT;

        $key = new CredentialFormField();
        $key->name = 'secret_key';
        $key->safe = false;
        $key->label = 'Secret key';
        $key->type = \App\System\PaymentGateway\Models\CredentialFormField::TYPE_TEXT;

        $stripe->credentials = array($key, $public);

        PaymentGatewayForm::create(['name' => 'Helcim',
            'logo' => 'helcim.png',
            'backend_name' => 'pg_form_helcim',
            'gateway_form' => json_encode($stripe),
            'payment_gateway_parent_id'=>0,
            'status' => $status]);
    }
}