<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColsToGuestCommunication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guest_communications', function (Blueprint $table) {
            $table->integer('pms_booking_id')->nullable()->after('booking_info_id');
            $table->string('alert_type')->after('is_guest')->nullable()->default('chat')->comment('Which type of notification:- payment_failed | chat | payment_past_due | id_uploaded');
            $table->string('action_required')->nullable()->comment('0 : No | 1 : Required')->after('message');
            $table->string('action_performed')->nullable()->comment('0 : No | 1 : Required')->after('message');
            $table->string('action_performed_by')->nullable()->comment('This is the user_id from users table')->after('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guest_communication', function (Blueprint $table) {
            //
        });
    }
}
