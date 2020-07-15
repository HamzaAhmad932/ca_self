<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnToPreferencesForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preferences_forms', function (Blueprint $table) {
            $table
                ->integer('status')
                ->default(1)
                ->after('form_data')
                ->comment('0 = Disable, 1 = Active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preferences_form', function (Blueprint $table) {
            //
        });
    }
}
