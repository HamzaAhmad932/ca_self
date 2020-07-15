<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('upsell_order_id')->comment('Foreign Key -> upsell_orders');
            $table->unsignedBigInteger('upsell_id')->comment('Foreign Key -> upsell_listing');
            $table->text('upsell_price_settings_copy')->default(null)->comment('The Of Upsell Settings At the time of upsell purchase');
            $table->float('amount')->comment('Single Upsell Item Amount');
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
        Schema::dropIfExists('upsell_order_details');
    }
}
