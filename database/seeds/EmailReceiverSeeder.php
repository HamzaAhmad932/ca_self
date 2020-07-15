<?php

use Illuminate\Database\Seeder;

class EmailReceiverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\EmailReceiver::truncate();
        \App\EmailReceiver::insert([
            ['name'=>"Admin",'receiver_id'=>\App\EmailReceiver::$ADMIN],
            ['name'=>"Client",'receiver_id'=>\App\EmailReceiver::$CLIENT],
            ['name'=>"Guest",'receiver_id'=>\App\EmailReceiver::$GUEST],
        ]);
    }
}
