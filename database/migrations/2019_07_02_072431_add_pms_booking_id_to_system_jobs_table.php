<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPmsBookingIdToSystemJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_jobs', function (Blueprint $table) {
            $table->integer('pms_booking_id')->nullable()->after('booking_info_id');
            $table->integer('pms_property_id')->nullable()->after('pms_booking_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_jobs', function (Blueprint $table) {
            $table->dropColumn('pms_booking_id');
            $table->dropColumn('pms_property_id');

        });
    }
}
