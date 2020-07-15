<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemJobDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_job_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_job_id')->comment('system job id');
            $table->text('exception_object')->nullable();
            $table->text('response_msg')->nullable('Exception msg or Success msg');
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
        Schema::dropIfExists('system_job_details');
    }
}
