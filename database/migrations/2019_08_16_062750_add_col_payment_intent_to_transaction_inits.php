<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColPaymentIntentToTransactionInits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_inits', function (Blueprint $table) {
            $table->string('payment_intent_id')->nullable()->comment('Only for Stripe usage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_inits', function (Blueprint $table) {
            //
        });
    }
}
