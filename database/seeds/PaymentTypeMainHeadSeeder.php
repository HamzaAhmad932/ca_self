<?php

use Illuminate\Database\Seeder;
use App\PaymentTypeMainHead;
class PaymentTypeMainHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mainheads = ['BOOKING PAYMENT', 'SECURITY DEPOSIT', 'ADDITIONAL CHARGE', 'CANCELLATION ADJUSTMENT', 'CREDIT CARD VALIDATION'];
        $id = 1;
        foreach ($mainheads as $head) {
            PaymentTypeMainHead::create(['id'=> $id, 'name' => $head, 'sys_name' => $head, 'status' => 1, ] );
            $id++;
    }
}
}
