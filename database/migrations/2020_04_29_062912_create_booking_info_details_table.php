<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingInfoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_info_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('booking_info_id')->unique()->index();
            $table->text('cc_auth_settings')->nullable()->comment("Booking Info's Credit Card Validation Settings");
            $table->text('security_deposit_settings')->nullable()->comment("Booking Info's Security Damage Deposit Settings");
            $table->text('payment_schedule_settings')->nullable()->comment("Booking Info's Payment Schedule Settings");
            $table->text('cancellation_settings')->nullable()->comment("Booking Info's Cancellation Policies Settings");
            $table->text('payment_gateway_settings')->nullable()->comment("Payment Gateway Settings");
            $table->text('full_response')->comment('Original Full Response of Booking Info got from PMS');
            $table->boolean('use_bs_settings')->default(0)->comment(" Global or Custom (0 => Global | 1 => Custom) Booking Source Settings");
            $table->boolean('use_pg_settings')->default(0)->comment("Attached Payment Gateway Global or Custom (0 => Global | 1 => Custom)");
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
        Schema::dropIfExists('booking_info_details');
    }
}
