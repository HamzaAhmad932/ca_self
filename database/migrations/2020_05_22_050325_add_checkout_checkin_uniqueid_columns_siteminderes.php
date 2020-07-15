<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckoutCheckinUniqueidColumnsSiteminderes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('siteminders', function (Blueprint $table) {
            $table->string('unique_booking_id', 50)->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
