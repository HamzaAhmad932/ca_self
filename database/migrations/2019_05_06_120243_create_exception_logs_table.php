<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExceptionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exception_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message')->nullable(true);
            $table->text('stack_trace')->nullable(true);
            $table->text('file')->nullable(true);
            $table->integer('line')->default(0);
            $table->integer('user_id')->nullable(true);
            $table->integer('user_account_id')->nullable(true);
            $table->integer('booking_info_id')->nullable(true);
            $table->integer('booking_pms_id')->nullable(true);
            $table->text('meta_data')->nullable(true);
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
        Schema::dropIfExists('exception_logs');
    }
}
