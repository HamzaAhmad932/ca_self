<?php

use Illuminate\Database\Seeder;
use App\PaymentTypePartialHead;

class PaymentTypePartialHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
           $heads = ['1/2', '2/2'];
           $id = 1;
        foreach ($heads as $head) {
            PaymentTypePartialHead::create(['id'=> $id, 'name' => $head, 'sys_name' => $head, 'status' => 1, ] );
    }
}
}
