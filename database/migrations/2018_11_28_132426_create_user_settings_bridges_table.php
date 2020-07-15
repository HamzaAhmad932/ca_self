<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings_bridges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id')->unsigned();
            $table->integer('booking_source_form_id')->unsigned();
            $table->integer('property_info_id')->unsigned();
            $table->string('model_name');
            $table->integer('model_id')->unsigned();
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
        Schema::dropIfExists('user_settings_bridges');
    }
}
