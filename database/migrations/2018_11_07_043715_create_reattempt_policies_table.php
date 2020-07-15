<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReattemptPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reattempt_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('user_account_id');
            $table->integer('attempts'); //in this field user tell us how many attempts he want in x hours
            $table->integer('hours'); //in this field user tell hours for attempts for example how many attempts in one hours
            $table->integer('stop_after'); // in this field user tell us stop after this hours
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
        Schema::dropIfExists('reattempt_policies');
    }
}
