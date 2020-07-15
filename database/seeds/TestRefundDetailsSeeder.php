<?php

use Illuminate\Database\Seeder;
use App\RefundDetail;

class TestRefundDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(RefundDetail::class, 55)->create();
    }
}
