<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id');
            $table->string('channel_code')->comment('channel code as save in booking_source_forms');
            $table->integer('pms_property_id')->unsigned()->comment('pms_property_id => id getting from pms');
            $table->string('pms_booking_id',70)->comment('it received as string , boking id getting from pms');
            $table->string('status')->nullable();
            $table->text('exception')->nullable()->comment('exception msg ');
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
        Schema::dropIfExists('failed_bookings');
    }
}
