<?php

use App\UserBookingSource;
use Illuminate\Database\Seeder;

class UserBookingSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(UserBookingSource::class,10)->create();

    }
}
