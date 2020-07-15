<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCreditCardInfoTableAddTypeAndIsdefaultCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_card_infos', function (Blueprint $table) {
            $table->integer('type')->nullable();
            $table->tinyInteger('is_default')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_card_infos', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_default');
        });
    }
}
