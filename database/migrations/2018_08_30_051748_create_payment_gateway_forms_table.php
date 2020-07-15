<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentGatewayFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('logo')->nullable();
            $table->string('name');
            $table->string('backend_name', 50);
            $table->text('gateway_form');
            $table->integer('payment_gateway_parent_id');
            $table->string('status');
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
        Schema::dropIfExists('payment_gateway_forms');
    }
}
