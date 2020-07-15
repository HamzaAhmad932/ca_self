<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardInfoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_info_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cc_info_id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('user_account_id');
            $table->text('message');
            $table->text('response')->nullable();
            $table->tinyInteger('status');
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
        Schema::dropIfExists('credit_card_info_details');
    }
}
