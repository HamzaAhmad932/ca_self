<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('agree')->nullable();
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->integer('user_account_id');
            $table->integer('parent_user_id')->default(0);
            $table->integer('status')->default(1);

            $table->timestamp('email_verified_at')->nullable();
            $table->integer('phone_verified')->default(0);
            $table->string('test_user')->default(0);
            $table->integer('is_activated')->default(false);
            $table->tinyInteger('attempt_tour')->default(0);

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['email', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
