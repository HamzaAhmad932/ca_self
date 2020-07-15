<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardAuthorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_authorizations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id')->unsigned();
            $table->integer('cc_info_id')->unsigned();
            $table->integer('user_account_id')->default(0);
            $table->tinyInteger('attempts')->default(0);
            $table->tinyInteger('attempts_for_500')->default(0);
            $table->double('hold_amount',8,2)->nullable()->default(0.00)->comment('Authorized amount based on client settings');
            $table->string('token', 100)->comment('Extracted token from transaction object');
            $table->text('transaction_obj')->comment('Json of transaction object');
            $table->tinyInteger('is_auto_re_auth')->default(0)->comment('0=do not, 1=yes do auto reAuth');
            $table->integer('type')->comment(' 3=CCValidation Auth  Verification, 4=Security Damage Deposit,  (added from heads )');
            $table->timestamp('due_date')->nullable()->comment('First Due Date of Auth and it always constant as first time loged');         
            $table->string('next_due_date')->nullable()->comment('Next due date on which to re auth based on settings IF null dnt re-auth');
            $table->tinyInteger('status');
            $table->tinyInteger('captured')->default(0);
            $table->tinyInteger('decline_email_sent')->default(0)->nullable(true);
            $table->text('remarks')->nullable()->comment('Remarks on voided e.g if payment Processor Changed');      
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
        Schema::dropIfExists('credit_card_authorizations');
    }
}
