<?php

use Illuminate\Database\Seeder;
use App\PropertyInfo;

class TestPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(PropertyInfo::class, 274)->create();
    }
}
