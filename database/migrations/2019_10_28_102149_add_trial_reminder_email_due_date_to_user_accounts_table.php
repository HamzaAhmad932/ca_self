<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class
AddTrialReminderEmailDueDateToUserAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            $table->timestamp('billing_card_required_reminder_due_date')->after('stripe_customer_id')->nullable()
                ->comment('due date to send reminder email to user for Billing Commission Trial Period Ends');
            $table->integer('billing_reminder_attempts')->after('billing_card_required_reminder_due_date')
                ->default(0)
                ->comment('Number of emails sent to remind user for Billing Commission Trial Period Ends');
            $table->timestamp('suspend_account_on')->after('billing_reminder_attempts')->nullable()
                ->comment('IF Subscription Invoice Failed then due date for account to suspend will 
                be entered to suspend');
            $table->timestamp('sd_activated_on')->after('suspend_account_on')->nullable()
                ->comment('Date time when first time SD Auth Settings Activated');
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
