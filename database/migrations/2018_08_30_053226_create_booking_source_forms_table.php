<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingSourceFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_source_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('logo')->nullable();
            $table->string('name');
            $table->integer('form_id')->nullable();
            $table->integer('channel_code');
            $table->integer('type')->comment('1=VC, 2=CC, 3=BankDraft, 4=VC CC, 5=VC CC BankDraft')->nullable();
            $table->text('form_data')->nullable();
            $table->integer('pms_form_id')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('booking_source_forms');
    }
}
