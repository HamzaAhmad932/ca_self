<?php

use App\BookingInfo;
use Illuminate\Database\Seeder;

class ChangeAirbnbBookingsChannelCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BookingInfo::whereIn('channel_code', [10, 46])
    					->update(['channel_code' => 101]);
    }
}
