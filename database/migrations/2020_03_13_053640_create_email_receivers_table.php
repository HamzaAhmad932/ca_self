<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailReceiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_receivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique()->comment('"Admin", "Client", "Guest"');
            $table->string('receiver_id')->unique()->comment('"1" => "Admin", "2" => "Client", "3" => "Guest"');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *t
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_receivers');
    }
}
