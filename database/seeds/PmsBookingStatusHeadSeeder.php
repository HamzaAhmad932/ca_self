<?php

use Illuminate\Database\Seeder;
use App\PmsBookingStatusHead;

class PmsBookingStatusHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $mainheads = ['Cancelled','Confirmed','New','Request','Black'];
        foreach ($mainheads as $key => $head) {

            PmsBookingStatusHead::create(['name' => $head, 'code' => $key, 'pms_id' => 1, 'short_code' => $head, 'remarks' => 'System Generated Entries', 'status' => 1]);
        }
    }

}
