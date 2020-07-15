<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertyInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('logo');     //->default('no_image.png');
            $table->integer('user_id');
            $table->integer('pms_id');
            $table->integer('user_account_id');
            $table->integer('pms_property_id');
            $table->text('property_key')->nullable();
            $table->string('currency_code', 5)->nullable();
            $table->string('time_zone')->nullable();
            $table->integer('payment_schedule_id');
            $table->integer('user_payment_gateway_id');
            $table->integer('use_bs_settings')->default(0)->comment('0 for use gloabal BookingSource ,1 for use local BookingSource ');
            $table->integer('use_pg_settings')->default(0)->comment('0 for use gloabal PaymentGateway ,1 for use local PaymentGateway ');
            $table->string('status');
            $table->string('property_email')->nullable()->comment('Email Assosiated to that property to send emails to guest');
            $table->text('notify_url')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->integer('available_on_pms')->default(1)->comment('1 => Available or Valid Property , 0=>not valid or deleted on PMS');
            $table->timestamps();
            $table->unique(['user_account_id', 'pms_property_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_infos');
    }
}
