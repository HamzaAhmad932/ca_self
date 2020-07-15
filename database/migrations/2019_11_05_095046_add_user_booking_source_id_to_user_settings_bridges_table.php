<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserBookingSourceIdToUserSettingsBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings_bridges', function (Blueprint $table) {
            $table->bigInteger('user_booking_source_id')->after('id')->default(0)
                ->comment('user_booking_sources table foreign key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_settings_bridges', function (Blueprint $table) {
            //
        });
    }
}
