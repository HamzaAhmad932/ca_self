<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellPropertiesBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_properties_bridges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('upsell_id')->index()->comment('Foreign Key -> upsell_listings');
            $table->unsignedBigInteger('property_info_id')->index()->comment('Foreign Key -> property_infos -> property_info_id');
            $table->text('room_info_ids')->nullable()->default(null)->comment('always get and return array of room_info_ids, Null = All Rooms');
            $table->unsignedTinyInteger('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upsell_properties_bridges');
    }
}
