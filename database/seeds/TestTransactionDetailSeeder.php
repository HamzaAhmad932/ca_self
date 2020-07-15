<?php

use Illuminate\Database\Seeder;
use App\TransactionDetail;

class TestTransactionDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TransactionDetail::class, 1480)->create();
    }
}
