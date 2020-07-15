<?php

use Illuminate\Database\Seeder;
use App\Upsell;

class TestUpsellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Upsell::class, 110)->create();
    }
}
