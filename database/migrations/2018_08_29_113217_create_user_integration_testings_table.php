<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIntegrationTestingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_integration_testings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('user_id')->unsigned();
            $table->integer('user_account_id');
            
            $table->timestamp('pms_last_test_date')->nullable();   
            $table->text('pms_result');
            
            $table->timestamp('payment_gateway_last_test_date')->nullable();
            $table->text('payment_gateway_result');

            $table->timestamp('new_booking_last_test_date')->nullable();
            $table->text('new_booking_result');  



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
        Schema::dropIfExists('user_integration_testings');
    }
}
