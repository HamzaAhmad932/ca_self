<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailDefaultContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_default_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('email_type_head_id');
            $table->unsignedTinyInteger('email_receiver_id')->comment('content for Whom Admin, Guest, Client.');
            $table->longText('content')->comment('Content in Json format.');
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
        Schema::dropIfExists('email_default_contents');
    }
}
