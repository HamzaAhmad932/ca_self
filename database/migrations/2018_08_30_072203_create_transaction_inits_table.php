<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionInitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_inits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_info_id');
            $table->integer('pms_id')->nullable()->comment('Id of pms_form table, to differ between PMS');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('next_attempt_time')->nullable();
            $table->timestamp('update_attempt_time')->nullable();
            $table->double('price', 8, 2)->default(0.00)->nullable();
            $table->string('is_modified')->nullable();
            $table->string('payment_status');
            $table->integer('user_id')->nullable();
            $table->integer('user_account_id');
            $table->string('charge_ref_no')->nullable();
            $table->text('last_success_trans_obj')->nullable();
            $table->tinyInteger('lets_process')->default(0);
            $table->tinyInteger('final_tick')->default(0);
            $table->text('system_remarks')->nullable();
            $table->text('split')->nullable();
            $table->text('against_charge_ref_no')->nullable();
            $table->string('type',3);
            $table->tinyInteger('status');
            $table->string('transaction_type');
            $table->text('client_remarks')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('error_code_id')->nullable();
            $table->integer('attempt')->default(0);
            $table->integer('attempts_for_500')->default(0);
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
        Schema::dropIfExists('transaction_inits');
    }
}
