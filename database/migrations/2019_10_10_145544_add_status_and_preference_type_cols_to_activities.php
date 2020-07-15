<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAndPreferenceTypeColsToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            //add preference_type column
            $table
                ->integer('preference_type')
                ->default(1)
                ->after('id')
                ->comment('1:Account Notifications, 2:Guest Notification, 3:Payment Activity Notifications');

            //add status column
            $table
                ->integer('status')
                ->default(1)
                ->after('email')
                ->comment('0:Disable, 1:Active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            //
        });
    }
}
