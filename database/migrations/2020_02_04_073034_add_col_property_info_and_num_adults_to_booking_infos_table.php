<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColPropertyInfoAndNumAdultsToBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('property_info_id')->after('property_id');
            $table->integer('num_adults')->after('guest_country')->default(1);
            $table->string('channel_reference')->nullable()->default('');
            //$table->tinyInteger('purchase_upsell_notify')->default(0);
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
            $table->dropColumn('property_info_id');
            $table->dropColumn('num_adults');
            $table->dropColumn('channel_reference');
            //$table->dropColumn('purchase_upsell_notify');
        });
    }
}
