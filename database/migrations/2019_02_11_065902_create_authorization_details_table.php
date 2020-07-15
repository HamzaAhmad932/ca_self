<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorizationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorization_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cc_auth_id');
            $table->integer('user_account_id');
            $table->string('name')->nullable();
            $table->text('payment_processor_response');
            $table->integer('payment_gateway_form_id');
            $table->string('payment_gateway_name')->nullable();
            $table->string('amount')->nullable();
            $table->tinyInteger('payment_status');
            $table->text('charge_ref_no');
            $table->text('client_remarks')->nullable();
            $table->double('order_id')->nullable();
            $table->text('error_msg')->nullable();
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
        Schema::dropIfExists('authorization_details');
    }
}
