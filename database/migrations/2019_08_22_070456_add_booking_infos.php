<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBookingInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->string('guestMobile', 50)->nullable();
            $table->string('guestFax', 255)->nullable();
            $table->string('guestCity', 50)->nullable();
            $table->longText('notes')->nullable();
            $table->string('flagColor',10)->nullable();
            $table->longText('flagText')->nullable();
            $table->integer('bookingStatusCode')->nullable();
            $table->string('price',50)->nullable();
            $table->string('bookingReferer',50)->nullable();
            $table->longText('guestComments')->nullable();
            $table->dateTime('guestArrivalTime')->nullable();
            $table->string('invoiceNumber',50)->nullable();
            $table->dateTime('invoiceDate')->nullable();
            $table->longText('apiMessage')->nullable();
            $table->longText('message')->nullable();
            $table->string('bookingIp',50)->nullable();
            $table->longText('host_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->dropColumn(['guestMobile', 'guestFax', 'guestCity', 'notes', 'flagColor', 'flagText', 'bookingStatusCode', 'price', 'bookingReferer', 'guestComments', 'guestArrivalTime', 'invoiceNumber', 'invoiceDate', 'apiMessage', 'message', 'bookingIp', 'host_comments']);
        });
    }
}
