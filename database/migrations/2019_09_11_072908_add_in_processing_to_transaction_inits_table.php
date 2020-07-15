<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInProcessingToTransactionInitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_inits', function (Blueprint $table) {
            $table->integer('in_processing')->default(0)->comment('Entry available to process = 0 | entry in process by job = 1 | entry in process Manually = 2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_inits', function (Blueprint $table) {
            //
        });
    }
}
