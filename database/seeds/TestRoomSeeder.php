<?php

use Illuminate\Database\Seeder;
use App\RoomInfo;

class TestRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '1024M');//allocate memory
        factory(RoomInfo::class, 8334)->create();
    }
}
