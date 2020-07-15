<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingSourceCapabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_source_capabilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_source_form_id')->comment('ID of booking_source_forms table');
            $table->integer('ca_capability_id')->comment('ID of booking_source_forms table');
            $table->integer('status')->default(1)->comment(
                ' Capability Supported for this booking Source or not ? (0 => notSupportedYet, 1 => supported,)');
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
        Schema::dropIfExists('booking_source_capabilities');
    }
}
