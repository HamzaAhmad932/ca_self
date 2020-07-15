<?php

use Illuminate\Database\Seeder;
use App\PaymentTypeCollectionHead;

class PaymentTypeCollectionHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $heads = ['COLLECTION', 'REFUND','AUTHORIZE'];
        $id = 1;
        foreach ($heads as $head) {
            PaymentTypeCollectionHead::create(['id'=> $id,'name' => $head, 'sys_name' => $head, 'status' => 1, ] );
            $id++;
    }
}
}
