<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('error_code_id')->nullable();
            $table->string('errorable_type')->nullable(); // table name of which belong's to error
            $table->integer('errorable_id')->nullable(); // table primary id which error blog's
            $table->string('response')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('user_account_id')->nullable();
            $table->string('message')->nullable();
            $table->string('application')->nullable();
            $table->string('functionality')->nullable();
            $table->integer('refer_id')->nullable();
            $table->string('third_party_response')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('error_logs');
    }
}
