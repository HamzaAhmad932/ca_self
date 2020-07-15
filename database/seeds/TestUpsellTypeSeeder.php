<?php

use Illuminate\Database\Seeder;
use App\UpsellType;

class TestUpsellTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(UpsellType::class, 110)->create();
    }
}
