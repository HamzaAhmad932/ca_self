<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plan_type')->comment('1 => trail, 2=>volume, 3=>subscrib, 4=>transaction');
            $table->text('settings')->comment('settings in json format');
            $table->boolean('type')->comment('type is 0 default and custom setting is 1  ');
            $table->boolean('status')->comment('setting status active or not active');
            $table->string('model_type')->comment('Model type or Model class name');
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
        Schema::dropIfExists('plan_settings');
    }
}
