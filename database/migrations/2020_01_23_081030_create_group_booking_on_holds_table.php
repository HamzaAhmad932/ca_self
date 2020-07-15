<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupBookingOnHoldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_booking_on_holds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id');
            $table->integer('pms_booking_id');
            $table->integer('master_id');
            $table->string('booking_status');
            $table->integer('channel_code');
            $table->integer('pms_property_id');
            $table->string('token')->nullable();
            $table->integer('cvv')->nullable();
            $table->string('caller')->default(null)->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('group_booking_on_holds');
    }
}
