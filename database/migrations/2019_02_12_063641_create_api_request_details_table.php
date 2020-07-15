<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_request_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id')->nullable();
            $table->string('channel_code')->nullable()->comment('channel code as save in booking_source_forms');
            $table->integer('pms_property_id')->nullable()->unsigned()->comment('pms_property_id => id getting from pms');
            $table->string('pms_booking_id',70)->nullable()->comment('it received as string , boking id getting from pms');
            $table->text('full_request_url')->comment('Full Api url request');
            $table->string('status')->nullable()->comment('new => for getting new booking request, modify => for getting modify booking request , delete=>for getting delete booking request');
            $table->text('client_ip')->nullable()->comment('IP address from where api request called');
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
        Schema::dropIfExists('api_request_details');
    }
}
