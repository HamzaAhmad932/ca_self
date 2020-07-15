<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pms_booking_id', 50)->comment('its received as string');
            $table->string('bs_booking_id');
            $table->integer('user_id')->nullable()->comment('By default it will be Null, But when some action is performed by user, that users id will be saved here');
            $table->integer('user_account_id');
            $table->integer('channel_code');
            $table->integer('property_id')->comment('PMS property ID');
            $table->integer('pms_id')->comment('Id of pms_form table, to differ between PMS');
            $table->integer('room_id')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_title')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_last_name')->nullable();
            $table->string('guest_zip_code')->nullable();
            $table->string('guest_post_code')->nullable();
            $table->string('guest_country')->nullable();
            $table->string('guest_address')->nullable();
            $table->string('guest_currency_code')->nullable();
            $table->timestamp('booking_time')->nullable()->comment('Booking Time GMT');
            $table->timestamp('pms_booking_modified_time')->nullable()->comment('Booking Modified GMT');   
            $table->timestamp('check_in_date')->nullable()->comment('GMT');
            $table->timestamp('check_out_date')->nullable()->comment('GMT');
            $table->tinyInteger('pms_booking_status');
            $table->float('total_amount', 8, 2)->nullable();
            $table->tinyInteger('booking_older_than_24_hours')->default(0)->comment('0=Not older, 1=Older');
            $table->string('is_vc', 4)->nullable()->comment('VC, CC, BT');
            $table->tinyInteger('is_manual')->nullable()->default(0)->comment('0=notification, 1=manual');
            $table->tinyInteger('record_source')->default(0)->comment('1=Upon Notification, 2=Via Scheduled Job');
            $table->text('full_response')->nullable()->comment('Full response from PMS server');
            $table->integer('is_process_able')->default(1)->comment('booking processable or valid => 1 , booking not processable or invalid => 0 ');
            $table->text('cancellation_settings')->comment('all cancellation_settings preserved at the time of booking received (current time settings) and will not be affected if policies are changed after some time');
            $table->dateTime('cancellationTime')->nullable();
            $table->tinyInteger('payment_gateway_effected')->default(0)->comment('0 = Valid Booking, Payment Gateway Settings are not changed after Booking Received, 1= Not Valid Booking, Payment Gateway Settings are changed after Booking Received its transactions effected or void due to change');
            $table->text('payment_gateway_object')->nullable()->comment('Payment Gateway Changed object');
            $table->string('property_time_zone')->nullable()->comment('Property TimeZone from property_info or from User Account (if time zone not set in property)');
            $table->tinyInteger('manual_canceled')->nullable()->default(0)->comment('only booking.com booking cancellation manually');
            $table->tinyInteger('cancel_email_sent')->nullable()->default(0)->comment('flag to check is email is already sent or not.');
            $table->timestamps();
            $table->unique(['pms_booking_id', 'pms_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_infos');
    }
}
