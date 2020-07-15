<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSecurityDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_security_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('security_deposit_id');
            $table->integer('user_id');
            $table->integer('user_account_id');
            $table->integer('pms_property_id');
            $table->text('form_data');
            $table->string('type');
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
        Schema::dropIfExists('user_security_deposits');
    }
}
