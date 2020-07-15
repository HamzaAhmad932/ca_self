<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_init_id');
            $table->integer('booking_info_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('user_account_id');
            $table->string('name')->nullable();
            $table->text('payment_processor_response');
            $table->integer('user_payment_gateway_id');
            $table->tinyInteger('payment_status');
            $table->text('charge_ref_no');
            $table->text('against_charge_ref_no')->nullable();
            $table->double('amount')->nullable();
            $table->text('client_remarks')->nullable();
            $table->double('order_id')->nullable();
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
        Schema::dropIfExists('refund_details');
    }
}
