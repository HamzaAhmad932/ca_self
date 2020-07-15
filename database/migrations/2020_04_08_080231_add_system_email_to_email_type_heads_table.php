<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemEmailToEmailTypeHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_type_heads', function (Blueprint $table) {
            $table->boolean('system_email')->default(false)->after('customizable')
                ->comment('System emails content created at runtime while sending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_type_heads', function (Blueprint $table) {
            //
        });
    }
}
