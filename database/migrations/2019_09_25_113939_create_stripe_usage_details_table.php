<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripeUsageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_usage_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id');
            $table->string('model_name');
            $table->integer('model_id');
            $table->string('description')->nullable();
            $table->text('response')->nullable();
            $table->integer('status')->default(0)->comment('1 => Success, 0 => Failed');
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
        Schema::dropIfExists('stripe_usage_details');
    }
}
