<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuideBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_books', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('guide_book_type_id')->index()->comment('foreign key');
            $table->string('internal_name', 100)->nullable()->comment('optional for client ease to differentiate');
            $table->string('icon', 20)->nullable()->comment('font awsome Icon class name');
            $table->text('text_content')->comment('description or HTML');
            $table->unsignedBigInteger('user_account_id')->default(0)->index()->comment('when system defined => 0');;
            $table->unsignedBigInteger('user_id')->default(0)->comment('when system defined => 0');;
            $table->unsignedTinyInteger('status')->default(1)->comment('1 => active, 0 => inactive');
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
        Schema::dropIfExists('guide_books');
    }
}
