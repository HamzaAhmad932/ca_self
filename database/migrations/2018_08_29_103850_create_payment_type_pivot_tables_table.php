<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTypePivotTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_type_pivot_tables', function (Blueprint $table) {
            $table->integer('id');
            
            $table->integer('ptmh_id')->unsigned();
            
            $table->integer('ptch_id')->unsigned();
            
            $table->integer('ptah_id')->unsigned();

            $table->integer('ptih_id')->unsigned();

            $table->integer('ptph_id')->unsigned();            

            $table->string('name');
            $table->string('sys_name');
            $table->string('status');
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
        Schema::dropIfExists('payment_type_pivot_tables');
    }
}
