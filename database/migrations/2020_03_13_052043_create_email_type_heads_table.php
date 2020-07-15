<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTypeHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_type_heads', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique()->index();
            $table->string('title');
            $table->string('type')->unique()->comment('email type as defined in config');
            $table->string('icon')->nullable();
            $table->boolean('to_admin')->default(false)->comment('email_type available to send to admin');
            $table->boolean('to_client')->default(false)->comment('email_type available to send to client');
            $table->boolean('to_guest')->default(false)->comment('email_type available to send to guest');
            $table->boolean('customizable')->default(false)->comment('can client customise this email type content for himself and for guest');
            $table->boolean('status')->default(true)->comment('email type can be fully in-activate for all clients, guests, admins');
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
        Schema::dropIfExists('email_type_heads');
    }
}
