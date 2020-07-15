<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellNotifyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_notify_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('booking_info_id')->comment('Foreign key for booking_infos.');
            $table->unsignedBigInteger('upsell_id')->comment('Foreign key for upsells')->index();
            $table->boolean('status')->comment('1 => sent, 0 => fail');
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
        Schema::dropIfExists('upsell_notify_details');
    }
}
