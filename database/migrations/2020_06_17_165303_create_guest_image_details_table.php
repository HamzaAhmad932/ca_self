<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestImageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_image_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('booking_info_id');
            $table->unsignedBigInteger('guest_image_id');
            $table->string('image');
            $table->string('type');
            $table->string('user_account_id');
            $table->string('user_id');
            $table->string('description');
            $table->string('status');
            $table->boolean('is_deleted')->default(true);
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
        Schema::dropIfExists('guest_image_details');
    }
}
