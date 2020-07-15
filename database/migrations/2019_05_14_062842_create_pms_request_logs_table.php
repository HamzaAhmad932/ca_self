<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePmsRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pms_request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_account_id')->default(0);
            $table->string('user_name')->default('');
            $table->integer('pms_form_id')->default(0);
            $table->string('pms_function')->default('');
            $table->longText('meta_data')->nullable();
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
        Schema::dropIfExists('pms_request_logs');
    }
}
