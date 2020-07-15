<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsells', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_account_id')->index()->comment('Foreign Key -> user_accounts');
            $table->unsignedBigInteger('user_id')->comment('Foreign Key -> users');
            $table->unsignedBigInteger('upsell_type_id')->index()->comment('Foreign Key -> upsell_type');
            $table->string('internal_name',100)->nullable();
            $table->text('meta')->comment('Title,Description,Notes(Icon,Title,Text)');
            $table->unsignedTinyInteger('value_type')->default(null)->comment('Value Type ($)/1 for Flat OR (%)/2 for Percentage ');
            $table->float('value');
            $table->unsignedTinyInteger('per')->comment(' Per Booking/1 OR Guest/2 ');
            $table->unsignedTinyInteger('period')->comment('daily/2, One time/1');
            $table->unsignedTinyInteger('notify_guest')->default(0)->comment('Marketing Emails days before checkin in integer 0 => do not Notify');
            $table->unsignedTinyInteger('status')->comment('0=Inactive, 1=Active');
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
        Schema::dropIfExists('upsells');
    }
}
