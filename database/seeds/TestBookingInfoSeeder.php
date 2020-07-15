<?php

use Illuminate\Database\Seeder;
use App\BookingInfo;

class TestBookingInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '1024M');//allocate memory

        factory(BookingInfo::class, 2284)->create();
    }

}
