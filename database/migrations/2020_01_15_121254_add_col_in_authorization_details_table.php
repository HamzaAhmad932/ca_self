<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColInAuthorizationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authorization_details', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->after('user_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authorization_details', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
