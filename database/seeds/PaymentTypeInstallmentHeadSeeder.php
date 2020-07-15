<?php

use Illuminate\Database\Seeder;
use App\PaymentTypeInstallmentHead;

class PaymentTypeInstallmentHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $heads = ['FULL', 'PARTIAL'];
        $id = 1;
        foreach ($heads as $head) {
            PaymentTypeInstallmentHead::create(['id'=> $id, 'name' => $head, 'sys_name' => $head, 'status' => 1, ] );
            $id++;
    }
}
}
