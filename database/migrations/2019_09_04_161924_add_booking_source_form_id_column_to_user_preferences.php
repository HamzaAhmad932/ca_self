<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBookingSourceFormIdColumnToUserPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_general_preferences', function (Blueprint $table) {
            $table->string('booking_source_form_id')->comment('ID of the booking_source_forms table. Save 0 for all channel except 4 supported 19, 53, 17, 14')->nullable()->after('form_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            //
        });
    }
}
