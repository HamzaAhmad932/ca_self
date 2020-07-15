<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommissionPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_source');
            $table->string('stripe_customer_id', 255);
            $table->string('backend_name');
            $table->text('customer_object');
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
        Schema::dropIfExists('commission_payment_methods');
    }
}
