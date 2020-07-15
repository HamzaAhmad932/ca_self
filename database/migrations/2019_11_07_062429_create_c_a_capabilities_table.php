<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaCapabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ca_capabilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('CA capability name');
            $table->text('description')->nullable()->comment('Description of CA capability name');
            $table->integer('priority')->default(0)->comment('Sort Order key to Display');
            $table->integer('status')->default(1)->comment(
                'Capability Supported or not ? (0 => notSupportedYet, 1 => supported,)');
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
        Schema::dropIfExists('ca_capabilities');
    }
}
