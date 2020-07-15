<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLongitudeColumnToPropertyInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_infos', function (Blueprint $table) {
            $table->dropColumn('payment_schedule_id');
            $table->double('longitude')->default(0)->after('time_zone');
            $table->double('latitude')->default(0)->after('longitude');
            $table->string('address')->nullable()->after('latitude');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_infos', function (Blueprint $table) {
            $table->dropColumn(['longitude', 'latitude', 'address', 'city', 'country']);
        });
    }
}
