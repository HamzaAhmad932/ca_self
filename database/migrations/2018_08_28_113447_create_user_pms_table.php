<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('pms_form_id')->unsigned();
            $table->integer('user_id');
            $table->integer('user_account_id');
            $table->text('form_data');
            $table->string('unique_key');
            $table->integer('is_verified')->default(0);
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
        Schema::dropIfExists('user_pms');
    }
}
