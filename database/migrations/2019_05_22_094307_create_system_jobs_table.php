<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id');
            $table->integer('booking_info_id');
            $table->string('model_name')->comment('Add related model name to identify job type');
            $table->string('model_id')->nullable()->comment('Add related model id to identify job type or parameters');
            $table->string('dispatch_description')->nullable()->comment('Any related information to store. Some of the related descriptions are CONST in SystemJob Model (Optional)');
            $table->timestamp('due_date')->comment('Attempt Datetime');
            $table->text('json_data')->nullable();
            $table->integer('attempts')->default(0)->comment('Number of times attempted, CONST in Model');
            $table->integer('status')->default(0)->comment('0 => pending , 1 => completed, 2 => void, CONST in Model');
            $table->integer('lets_process')->default(1);
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
        Schema::dropIfExists('system_jobs');
    }
}
