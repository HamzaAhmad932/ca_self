<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuideBookTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_book_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100);
            $table->string('icon', 20)->comment('font awsome Icon class name');
            $table->unsignedInteger('user_account_id')->default(0)->index()->comment('when system defined => 0');
            $table->unsignedInteger('user_id')->default(0)->comment('when system defined => 0');
            $table->boolean('is_user_defined')->default(0)->comment('1=> user defined, 0 => System Defined');
            $table->unsignedTinyInteger('priority')->default(0)->comment('Priority to Show List, etc...');
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
        Schema::dropIfExists('guide_book_types');
    }
}
