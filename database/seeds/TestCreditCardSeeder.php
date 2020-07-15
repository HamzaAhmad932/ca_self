<?php

use Illuminate\Database\Seeder;
use App\CreditCardInfo;

class TestCreditCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(CreditCardInfo::class, 1644)->create();
    }
}
