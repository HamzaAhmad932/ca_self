<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id')->unsigned();
            $table->integer('user_account_id')->default(0);
            $table->tinyInteger('is_vc')->default(0)->comment('1=VC, 0=CC');
            $table->string('card_name');
            $table->string('f_name');
            $table->string('l_name');
            $table->string('cc_last_4_digit');
            $table->string('cc_exp_month');
            $table->string('cc_exp_year');
            //$table->string('cc_cvc_num');
            $table->text('system_usage')->nullable()->comment('card realted encrypted information for system use');
            $table->text('customer_object');
            $table->string('auth_token');
            $table->integer('status');
            $table->tinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('due_date')->nullable();
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
        Schema::dropIfExists('credit_card_infos');
    }
}
