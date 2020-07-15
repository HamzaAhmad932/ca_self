<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPaymentGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_payment_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_gateway_form_id');
            $table->integer('property_info_id');
            $table->string('payment_hold_day')->nullable();
            $table->integer('user_id');
            $table->integer('user_account_id');
            $table->text('gateway');
            $table->integer('is_verified')->default(0);
            $table->integer('status')->default(1);

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
        Schema::dropIfExists('user_payment_gateways');
    }
}
