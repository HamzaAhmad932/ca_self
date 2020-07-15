<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommissionInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_account_id');
            // $table->integer('user_id');
            $table->string('invoice_number');
            $table->integer('status')->comment('1=>paid, 2=>fail, 3=>pending, 4=>retry, 5=>void');
            // $table->string('currency', 3);
            $table->dateTime('due_date');
            $table->integer('attempts');
            $table->text('items');
            // $table->dateTime('last_charge_attempt');
            $table->dateTime('paid_on');
            $table->float('total_amount');
            $table->timestamps();

//            $table->foreign('user_account_id')->references('id')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_invoices');
    }
}
