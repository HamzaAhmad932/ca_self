<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanAttachedStatusToUserAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            $table->integer('plan_attached_status')->after('sd_activated_on')->default(0)
                ->comment('if commission billing plan attached => 1 else not => 0');
            $table->timestamp('plan_attached_status_last_sync')->after('plan_attached_status')->nullable()
                ->comment('Last sync datetime of Commission Billing Plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            //
        });
    }
}
