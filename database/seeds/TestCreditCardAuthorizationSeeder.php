<?php

use Illuminate\Database\Seeder;
use App\CreditCardAuthorization;

class TestCreditCardAuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(CreditCardAuthorization::class, 1041)->create();
    }
}
