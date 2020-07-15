<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id')->nullable();
            $table->string('email_subject',255)->nullable();
            $table->string('email_type',255)->nullable();
            $table->integer('sent_to')->comment('1=Admin, 2=Client And 3=Guest')->nullable();
            $table->longText('encoded_data')->nullable();
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
        Schema::dropIfExists('email_infos');
    }
}
