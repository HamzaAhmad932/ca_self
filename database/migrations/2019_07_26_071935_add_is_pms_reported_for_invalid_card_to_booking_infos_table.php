<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPmsReportedForInvalidCardToBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->double('is_pms_reported_for_invalid_card')->default(0)->after('document_status_updated_on_pms')->comment('In case of BDC Channel if customer object creation failed or any auth or transaction failed on reporting PMS set colmmn val to 1');

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
            $table->dropColumn(['is_pms_reported_for_invalid_card']);
        });
    }
}
