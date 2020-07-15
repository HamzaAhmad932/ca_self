<?php

use Illuminate\Database\Seeder;
use App\TransactionInit;

class TestTransactionInitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TransactionInit::class, 1206)->create();
    }
}
