<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();            
            $table->string('company_logo')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('post_code')->nullable();
            $table->string('area_code')->nullable();
            $table->integer('user_limit')->default(5);
            $table->timestamp('account_verified_at')->nullable();
            $table->timestamp('integration_completed_on')->nullable();
            $table->timestamp('last_booking_sync')->nullable();
            $table->string('time_zone')->default('0')->comment('User Account Master or global time zone for propertise');
            $table->integer('status')->default(1)->comment('1 => active , 2 => de-activated by client, admin or client can re-activate , 3 => de-activated by admin, and only admin can re-activate ,');
            $table->tinyInteger('account_type')->default(1)->comment('1 => Live, 2 => Admin, 3 => Test');
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
        Schema::dropIfExists('user_accounts');
    }
}
