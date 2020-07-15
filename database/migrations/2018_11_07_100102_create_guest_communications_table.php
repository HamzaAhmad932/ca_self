<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestCommunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_communications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('user_account_id')->nullable();
            $table->integer('booking_info_id')->nullable();
            $table->integer('is_guest');
            $table->text('message')->nullable();
            $table->integer('message_read_by_guest')->nullable()->comment('this field use for message read or not read read status should be 1 and not read 0/null');
            $table->integer('message_read_by_user')->nullable()->comment('this field use for message read or not read read status should be 1 and not read 0/null');
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
        Schema::dropIfExists('guest_communications');
    }
}
