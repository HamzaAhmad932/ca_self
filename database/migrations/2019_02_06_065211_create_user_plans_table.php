<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id')->comment('foreign key referenced from user_accounts');
            $table->integer('trail_id')->nullable();
            $table->integer('flat_fee_id')->nullable();
            $table->integer('volume_plan_id')->nullable();
            $table->integer('subscription_id')->nullable();
            $table->timestamps();

//            $table->foreign('user_account_id')->references('id')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_plans');
    }
}
