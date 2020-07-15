<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermsAndConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_and_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_account_id')->comment('User account ID for unique Terms and condition against each account.');
            $table->unsignedBigInteger('user_id')->comment('Foreign Key -> users');
            $table->string('internal_name', 100)->comment('Internal usage name.');
            $table->text('text_content')->comment('Content is the actual paragraph of terms and conditions.');
            $table->string('checkbox_text', 100)->comment('This is the text that will show with checkbox on Guest Precheckin summary.');
            $table->unsignedTinyInteger('required')->comment('Required indicates whether this term is strictly required to accept by the guest or not(1=Required and 0=NotRequired ).');
            $table->unsignedTinyInteger('status')->comment('Status indicates whether this terms are active or not (1=Active and 0=Inactive)');
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
        Schema::dropIfExists('terms_and_conditions');
    }
}
