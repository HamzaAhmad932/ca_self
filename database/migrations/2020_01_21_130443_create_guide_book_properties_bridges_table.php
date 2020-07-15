<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuideBookPropertiesBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_book_properties_bridges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('guide_book_id')->index()->comment('foreign key');
            $table->unsignedBigInteger('property_info_id')->index()->comment('foreign key');
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
        Schema::dropIfExists('guide_book_properties_bridges');
    }
}
