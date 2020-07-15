<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTablesForSmx extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_infos', function (Blueprint $table) {
            $table->string('pms_property_id', 50)->change();
        });

//        Schema::table('audit_property_infos', function (Blueprint $table) {
//            $table->string('pms_property_id', 50)->change();
//        });

        Schema::table('booking_infos', function (Blueprint $table) {
            $table->string('property_id', 50)->change();
            $table->string('pms_booking_id', 50)->change();
        });

//        Schema::table('audit_booking_infos', function (Blueprint $table) {
//            $table->string('old_property_id', 50)->change();
//            $table->string('new_property_id', 50)->change();
//        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
