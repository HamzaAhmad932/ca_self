<?php

use Illuminate\Database\Seeder;
use App\GuestCommunication;

class TestGuestCommunicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(GuestCommunication::class, 165)->create();
    }
}
