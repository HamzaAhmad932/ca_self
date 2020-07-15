<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableGuestData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guest_datas', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->integer('adults')->default(0);
            $table->integer('childern')->default(0);
            $table->string('arriving_by')->nullable();
            $table->string('plane_number')->nullable();
            $table->string('verification_document')->nullable();
            $table->integer('step_completed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guest_datas', function (Blueprint $table) {
            //
        });
    }
}
