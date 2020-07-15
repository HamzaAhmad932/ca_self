<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpsellTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upsell_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_account_id')->default(0)->comment('Foreign Key -> user_account');
            $table->unsignedBigInteger('user_id')->default(0)->comment('Foreign Key -> user_id');
            $table->string('title',100);
            $table->unsignedTinyInteger('is_user_defined')->comment('0 = System Defined, 1 = User Defined');
            $table->unsignedTinyInteger('priority')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('0=Inactive, 1=Active');
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
        Schema::dropIfExists('upsell_types');
    }
}
