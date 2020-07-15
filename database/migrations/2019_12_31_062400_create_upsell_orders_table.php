<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_info_id')->index()->comment('Foreign Key -> booking_infos');
            $table->unsignedBigInteger('cc_info_id')->comment('Foreign Key -> cc_infos');
            $table->unsignedBigInteger('user_account_id')->index()->comment('Foreign Key -> user_accounts');
            $table->unsignedBigInteger('user_id')->comment('Foreign Key -> users');
            $table->float('final_amount')->comment('Sum Of Full Order');
            $table->unsignedTinyInteger('status');
            $table->float('commission_fee')->comment('Charge Automation Fee');
            $table->string('charge_ref_no',100)->nullable();
            $table->text('last_success_trans_obj')->nullable();
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
        Schema::dropIfExists('upsell_orders');
    }
}
