<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreCheckinEmailStatusToBookingInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->integer('pre_checkin_email_status')->default(0)->comment('0 = Email has not sended, 1 = 10 days email has sended, 2 = 5 days email has sended, 3 = 1 day email has sended');
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
            $table->dropColumn('pre_checkin_email_status');
        });
    }
}
