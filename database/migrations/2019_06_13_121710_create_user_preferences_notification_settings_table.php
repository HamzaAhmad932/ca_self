<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPreferencesNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_preferences_notification_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id')->unsigned();
            $table->integer('activity_id')->unsigned();
            $table->text('notify_settings')->nullable()->comment('All Notify Status on /off in json format');
            $table->string('to_email')->nullable();
            $table->string('cc_email')->nullable();
            $table->string('bcc_email')->nullable();
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
        Schema::dropIfExists('user_preferences_notification_settings');
    }
}
