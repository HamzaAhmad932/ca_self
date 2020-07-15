<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriorityColumnToBookingSourcesForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_source_forms', function (Blueprint $table) {
            //add priority column which will be used to sort sequence of settings representation
            $table
                ->integer('priority')
                ->after('pms_form_id')
                ->default(NULL)
                ->nullable()
                ->comment('Priority is used to show settings order on frontend');

            //custom settings column for boking sources to check whether tpo use others or custom settings
            $table
                ->integer('use_custom_settings')
                ->after('pms_form_id')
                ->default(0)
                ->comment('0:Not custom, 1:Custom');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_source_forms', function (Blueprint $table) {
            //
        });
    }
}
