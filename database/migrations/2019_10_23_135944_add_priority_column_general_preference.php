<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriorityColumnGeneralPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_preferences_forms', function (Blueprint $table) {
            //add priority column which will be used to sort sequence of preference representation
            $table
                ->integer('priority')
                ->after('description')
                ->default(NULL)
                ->nullable()
                ->comment('Priority is used to show settings on frontend');

            //add deleted_at column in the table
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
