<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePmsFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pms_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('logo')->nullable();
            $table->string('name')->nullable();
            //$table->integer('form_id');
            $table->text('backend_name');
            $table->string('status')->nullable();
            $table->timestamps();
        });

        // TODO: make these changes and also in their appropriate file(s)
        // $table->string('form_id'); // its a config file stored in config/db_const
        // $table->text('backend_name');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pms_forms');
    }
}
