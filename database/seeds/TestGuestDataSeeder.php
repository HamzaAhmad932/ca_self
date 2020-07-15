<?php

use Illuminate\Database\Seeder;
use App\GuestData;

class TestGuestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(GuestData::class, 164)->create();
    }
}
