<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        
        //factory(App\User::class,10)->create();
        //factory(App\User_account::class,10)->create();

        factory(App\PaymentTypePivotTable::class,10)->create();
        factory(App\PropertyInfo::class,10)->create();
        factory(App\BookingInfo::class,10)->create();
        factory(App\BookingCancellationInfo::class,10)->create();
        factory(App\TransactionInit::class,10)->create();
        factory(App\TransactionDetail::class,10)->create();
        factory(App\BookingModificationInfo::class,10)->create();
        factory(App\BookingSourceForm::class,10)->create();

// ########################## Payment Gateway parent seed for Spreedly ###########################

        \App\PaymentGatewayParent::create(
            ['name' => 'Spreedly',
            'backend_name' => 'pg_form_spreedly',
            'credentials' => json_encode(array(
                'env_key' => 'VxCy5I5uVWSaIfsaQGWglNgMv6w',
                'api_secret' => 'UzF762NjnWH8MbJUVHnm00kkWUPRiyECWJXhvnqD2eQnleA5h4pWTrP4suMdh7ZT')),
            'status' => 1]
        );


// ######################## Payment Gateway form for Stripe ####################################

        $stripe = new \App\System\PaymentGateway\Models\GateWay();
        $stripe->name = "Stripe";

        $public = new \App\System\PaymentGateway\Models\CredentialFormField();
        $public->name = 'publishable_key';
        $public->safe = true;
        $public->label = 'Publishable key';

        $key = new \App\System\PaymentGateway\Models\CredentialFormField();
        $key->name = 'secret_key';
        $key->safe = false;
        $key->label = 'Secret key';

        $stripe->credentials = array($key, $public);

        \App\PaymentGatewayForm::create(
            ['name' => 'Stripe',
            'backend_name' => 'pg_form_stripe',
            'gateway_form' => json_encode($stripe),
            'payment_gateway_parent_id'=>0,
            'status' => 1]);

        

        
    }
}
