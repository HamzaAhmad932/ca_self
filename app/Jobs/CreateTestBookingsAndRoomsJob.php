<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TestBookingInfoSeeder;
use TestRoomSeeder;

class CreateTestBookingsAndRoomsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::onQueue('clean_database_tables');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $test_room_seeder = new TestRoomSeeder();
        $test_room_seeder->run();
        $test_booking_seeder = new TestBookingInfoSeeder();
        $test_booking_seeder->run();
    }
}
