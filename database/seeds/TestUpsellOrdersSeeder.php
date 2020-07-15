<?php

use Illuminate\Database\Seeder;
use App\UpsellOrder;

class TestUpsellOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(UpsellOrder::class, 110)->create();
    }
}
