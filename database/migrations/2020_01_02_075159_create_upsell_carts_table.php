<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_info_id')->index()->comment('Foreign Key -> booking_infos');
            $table->unsignedBigInteger('upsell_id')->index()->comment('Foreign Key -> upsell_listings');
            $table->unsignedInteger('persons')->default(1);
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
        Schema::dropIfExists('upsell_carts');
    }
}
