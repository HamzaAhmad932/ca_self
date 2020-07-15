<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingCancellationInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_cancellation_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id');
            $table->string('is_cancelled');
            $table->text('pms_booking_status');
            $table->text('cancellation_encryption');
            $table->text('cancellation_secret');
            $table->text('cancellation_result');
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
        Schema::dropIfExists('booking_cancellation_infos');
    }
}
