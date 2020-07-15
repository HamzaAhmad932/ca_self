<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInProcessingToCreditCardAuthorizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_card_authorizations', function (Blueprint $table) {
            $table->integer('in_processing')->default(0)->comment('Entry process available = 0 | entry in process by job = 1 | entry in process Manually = 2 | entry in process by hook = 3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processing_to_credit_card_authorizations', function (Blueprint $table) {
            //
        });
    }
}
