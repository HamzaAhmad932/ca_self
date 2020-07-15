<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_init_id');
            $table->integer('cc_info_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('user_account_id');
            $table->string('name')->nullable();
            $table->text('payment_processor_response');
            $table->integer('payment_gateway_form_id');
            $table->tinyInteger('payment_status');
            $table->text('charge_ref_no');
            $table->text('client_remarks')->nullable();
            $table->text('error_msg')->nullable();
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
        Schema::dropIfExists('transaction_details');
    }
}
