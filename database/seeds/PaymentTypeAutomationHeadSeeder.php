<?php

use Illuminate\Database\Seeder;
use App\PaymentTypeAutomationHead;

class PaymentTypeAutomationHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $heads = ['AUTO', 'MANUAL'];
       $id = 1;
        foreach ($heads as $head) {
            PaymentTypeAutomationHead::create(['id'=> $id,'name' => $head, 'sys_name' => $head, 'status' => 1, ] );
            $id++;
    }
}
}
