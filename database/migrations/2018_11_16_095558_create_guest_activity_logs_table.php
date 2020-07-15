<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id')->nullable()->unsigned();
            $table->integer('transaction_init_id')->nullable()->unsigned();
            $table->integer('transaction_detail_id')->nullable()->unsigned();
            $table->string('guest_email')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->macAddress('mac_address')->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('activity_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guest_activity_logs');
    }
}
