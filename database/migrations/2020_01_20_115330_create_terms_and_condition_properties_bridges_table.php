<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermsAndConditionPropertiesBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_and_condition_properties_bridges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('terms_and_condition_id')->comment('Foreign key for Terms and condition table\'s(terms_and_conditions) Primary key id column.');
            $table->unsignedBigInteger('property_info_id')->comment('Foreign key for Property Infos table\'s Primary key id column')->index();
            $table->text('room_info_ids')->nullable()->comment(
                'always get and return array of room info ids but mutator will auto convert to "" and , 
                separated ids before saving & null Rooms_info_ids means all rooms can availing this guidebook.');
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
        Schema::dropIfExists('terms_and_condition_properties_bridges');
    }
}
